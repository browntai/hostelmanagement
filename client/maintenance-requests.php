<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    include('../includes/notification-helper.php');
    include('../includes/ai-helper.php');
    check_login();

    $uid   = $_SESSION['id'];
    $email = $_SESSION['login'];

    // Get client's tenant_id and active booking info
    $uStmt = $mysqli->prepare("SELECT u.tenant_id, u.first_name, u.last_name, b.hostel_id, b.roomno, h.name as hostel_name
        FROM users u
        LEFT JOIN bookings b ON b.emailid = u.email AND b.booking_status IN ('approved','confirmed')
        LEFT JOIN hostels h ON h.id = b.hostel_id
        WHERE u.id = ? ORDER BY b.postingDate DESC LIMIT 1");
    $uStmt->bind_param('i', $uid);
    $uStmt->execute();
    $uRes  = $uStmt->get_result();
    $uRow  = $uRes->fetch_object();
    $tenantId  = $uRow ? $uRow->tenant_id : 0;
    $hostelId  = $uRow ? $uRow->hostel_id : 0;
    $roomNo    = $uRow ? $uRow->roomno : 0;
    $hostelName = $uRow ? $uRow->hostel_name : '';
    $hasBooking = ($hostelId > 0);

    // ── Handle Form Submission ──
    $msg = ''; $msgType = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
        $category    = $mysqli->real_escape_string($_POST['category']);
        $priority    = $mysqli->real_escape_string($_POST['priority']);
        $subject     = $mysqli->real_escape_string($_POST['subject']);
        $description = $mysqli->real_escape_string($_POST['description']);

        // Photo upload (optional)
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/maintenance/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            $ext  = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed) && $_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                $fname = 'maint_' . time() . '_' . $uid . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fname)) {
                    $photoPath = 'uploads/maintenance/' . $fname;
                }
            }
        }

        $query = "INSERT INTO system_maintenance_requests (tenant_id, client_id, hostel_id, room_no, category, priority, subject, description, photo_path, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Open')";
        $ins = $mysqli->prepare($query);
        $ins->bind_param('iiiisssss', $tenantId, $uid, $hostelId, $roomNo, $category, $priority, $subject, $description, $photoPath);

        if ($ins->execute()) {
            $newRequestId = $mysqli->insert_id;

            // AI Auto-Route: assign best vendor
            $assignedVendor = autoAssignVendor($mysqli, $newRequestId);
            $vendorMsg = $assignedVendor ? " AI assigned vendor: {$assignedVendor->name}." : '';

            $msg = 'Maintenance request submitted successfully!' . $vendorMsg;
            $msgType = 'success';

            // Notify the landlord
            $lStmt = $mysqli->prepare("SELECT id FROM users WHERE tenant_id = ? AND role IN ('landlord','admin') LIMIT 1");
            $lStmt->bind_param('i', $tenantId);
            $lStmt->execute();
            $lRes = $lStmt->get_result();
            if ($landlord = $lRes->fetch_object()) {
                sendNotification($landlord->id, 'New Maintenance Request',
                    "A new $priority priority $category request has been submitted for Room $roomNo.", $uid);
            }
        } else {
            $msg = 'Failed to submit request. Please try again.';
            $msgType = 'danger';
        }
    }

    // ── Fetch existing requests for this client ──
    $rStmt = $mysqli->prepare("SELECT mr.*, h.name as hostel_name,
        v.name as vendor_name, v.phone as vendor_phone
        FROM system_maintenance_requests mr
        LEFT JOIN hostels h ON h.id = mr.hostel_id
        LEFT JOIN system_vendors v ON v.id = mr.assigned_vendor_id
        WHERE mr.client_id = ? ORDER BY mr.created_at DESC");
    $rStmt->bind_param('i', $uid);
    $rStmt->execute();
    $requests = $rStmt->get_result();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Maintenance Requests - ShimaHome</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <style>
        .priority-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .priority-Low        { background: #d4edda; color: #155724; }
        .priority-Medium     { background: #fff3cd; color: #856404; }
        .priority-High       { background: #f8d7da; color: #721c24; }
        .priority-Emergency  { background: #721c24; color: #fff; }
        .status-Open         { background: #cce5ff; color: #004085; }
        .status-In-Progress  { background: #fff3cd; color: #856404; }
        .status-Resolved     { background: #d4edda; color: #155724; }
        .status-Closed       { background: #e2e3e5; color: #383d41; }
        .status-Cancelled    { background: #f8d7da; color: #721c24; }
        .timeline-dot { width:12px;height:12px;border-radius:50%;display:inline-block;margin-right:8px; }
    </style>
</head>
<body>
    <div class="preloader"><div class="lds-ripple"><div class="lds-pos"></div><div class="lds-pos"></div></div></div>
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6"><?php include '../includes/client-navigation.php'?></header>
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6"><?php include '../includes/client-sidebar.php'?></div>
        </aside>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Maintenance Requests</h3>
                        <nav aria-label="breadcrumb"><ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Maintenance</li>
                        </ol></nav>
                    </div>
                </div>
            </div>
            <div class="container-fluid">

                <?php if($msg): ?>
                <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $msg; ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
                <?php endif; ?>

                <!-- ── Submit New Request ── -->
                <?php if($hasBooking): ?>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><i class="fas fa-tools mr-2 text-primary"></i>Report an Issue</h4>
                        <p class="text-muted mb-4">Submit a maintenance request for your room at <strong><?php echo htmlentities($hostelName); ?></strong> (Room <?php echo $roomNo; ?>).</p>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Category <span class="text-danger">*</span></label>
                                        <select name="category" class="form-control" required>
                                            <option value="">-- Select --</option>
                                            <option value="Plumbing">🔧 Plumbing</option>
                                            <option value="Electrical">💡 Electrical</option>
                                            <option value="Carpentry">🪵 Carpentry</option>
                                            <option value="Painting">🎨 Painting</option>
                                            <option value="Appliance">🔌 Appliance Repair</option>
                                            <option value="Pest Control">🐛 Pest Control</option>
                                            <option value="General">🏠 General</option>
                                            <option value="Other">📝 Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Priority <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-control" required>
                                            <option value="Low">🟢 Low – Can wait a few days</option>
                                            <option value="Medium" selected>🟡 Medium – Needs attention soon</option>
                                            <option value="High">🔴 High – Urgent issue</option>
                                            <option value="Emergency">🚨 Emergency – Immediate danger</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" class="form-control" placeholder="e.g. Leaking tap in the kitchen" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label>Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Describe the issue in detail…" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Attach Photo (optional)</label>
                                <div class="custom-file">
                                    <input type="file" name="photo" class="custom-file-input" id="photoInput" accept="image/*">
                                    <label class="custom-file-label" for="photoInput">Choose file…</label>
                                </div>
                                <small class="text-muted">Max 5 MB. JPG, PNG, GIF, or WebP.</small>
                            </div>
                            <button type="submit" name="submit_request" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Submit Request</button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>You need an <strong>active booking</strong> to submit maintenance requests.
                    <a href="book-hostel.php" class="alert-link ml-2">Book a Hostel →</a>
                </div>
                <?php endif; ?>

                <!-- ── My Requests ── -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><i class="fas fa-clipboard-list mr-2 text-info"></i>My Requests</h4>
                        <div class="table-responsive">
                            <table id="requestsTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Subject</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Vendor</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $i=1; while($r = $requests->fetch_object()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlentities($r->subject); ?></td>
                                        <td><?php echo htmlentities($r->category); ?></td>
                                        <td><span class="priority-badge priority-<?php echo $r->priority; ?>"><?php echo $r->priority; ?></span></td>
                                        <td><span class="priority-badge status-<?php echo str_replace(' ', '-', $r->status); ?>"><?php echo $r->status; ?></span></td>
                                        <td><?php echo $r->vendor_name ? htmlentities($r->vendor_name) . ' (' . htmlentities($r->vendor_phone) . ')' : '<span class="text-muted">Not assigned</span>'; ?></td>
                                        <td><?php echo date('d M Y', strtotime($r->created_at)); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#detailModal<?php echo $r->id; ?>"><i class="fas fa-eye"></i></button>
                                        </td>
                                    </tr>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="detailModal<?php echo $r->id; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">Request #<?php echo $r->id; ?> – <?php echo htmlentities($r->subject); ?></h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-4"><strong>Category:</strong> <?php echo $r->category; ?></div>
                                                        <div class="col-md-4"><strong>Priority:</strong> <span class="priority-badge priority-<?php echo $r->priority; ?>"><?php echo $r->priority; ?></span></div>
                                                        <div class="col-md-4"><strong>Status:</strong> <span class="priority-badge status-<?php echo str_replace(' ','-',$r->status); ?>"><?php echo $r->status; ?></span></div>
                                                    </div>
                                                    <hr>
                                                    <p><strong>Description:</strong></p>
                                                    <p><?php echo nl2br(htmlentities($r->description)); ?></p>
                                                    <?php if($r->photo_path): ?>
                                                    <p><strong>Photo:</strong></p>
                                                    <img src="../<?php echo $r->photo_path; ?>" class="img-fluid rounded" style="max-height:300px;">
                                                    <?php endif; ?>
                                                    <?php if($r->landlord_notes): ?>
                                                    <hr>
                                                    <p><strong>Landlord Notes:</strong></p>
                                                    <p class="text-info"><?php echo nl2br(htmlentities($r->landlord_notes)); ?></p>
                                                    <?php endif; ?>
                                                    <?php if($r->resolution_notes): ?>
                                                    <hr>
                                                    <p><strong>Resolution:</strong></p>
                                                    <p class="text-success"><?php echo nl2br(htmlentities($r->resolution_notes)); ?></p>
                                                    <small class="text-muted">Resolved on: <?php echo $r->resolved_at ? date('d M Y H:i', strtotime($r->resolved_at)) : 'N/A'; ?></small>
                                                    <?php endif; ?>
                                                    <?php if($r->vendor_name): ?>
                                                    <hr>
                                                    <p><strong>Assigned Vendor:</strong> <?php echo htmlentities($r->vendor_name); ?> – <?php echo htmlentities($r->vendor_phone); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <?php include '../includes/footer.php' ?>
        </div>
    </div>
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/extra-libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function(){
            $('#requestsTable').DataTable({ order: [[6, 'desc']] });
            $('.custom-file-input').on('change', function(){
                $(this).next('.custom-file-label').html(this.files[0].name);
            });
        });
    </script>
</body>
</html>
