<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    include('../includes/notification-helper.php');
    include('../includes/tenant_manager.php');
    check_login();

    $uid = $_SESSION['id'];
    $tm  = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();
    $tenantWhere = $tm->getTenantWhereClause('mr', false);

    // ── Handle Actions ──
    $msg = ''; $msgType = '';

    // Update status / assign vendor / add notes
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_request'])) {
        $reqId      = intval($_POST['request_id']);
        $newStatus  = $mysqli->real_escape_string($_POST['status']);
        $vendorId   = !empty($_POST['vendor_id']) ? intval($_POST['vendor_id']) : null;
        $lNotes     = $mysqli->real_escape_string($_POST['landlord_notes']);
        $rNotes     = $mysqli->real_escape_string($_POST['resolution_notes']);
        $resolvedAt = ($newStatus === 'Resolved' || $newStatus === 'Closed') ? date('Y-m-d H:i:s') : null;

        $upd = $mysqli->prepare("UPDATE system_maintenance_requests
            SET status=?, assigned_vendor_id=?, landlord_notes=?, resolution_notes=?, resolved_at=?
            WHERE id=? AND tenant_id=?");
        $upd->bind_param('sisssii', $newStatus, $vendorId, $lNotes, $rNotes, $resolvedAt, $reqId, $tenantId);

        if ($upd->execute()) {
            $msg = "Request #$reqId updated successfully."; $msgType = 'success';

            // Notify the client
            $cStmt = $mysqli->prepare("SELECT client_id FROM system_maintenance_requests WHERE id=? AND tenant_id=?");
            $cStmt->bind_param('ii', $reqId, $tenantId);
            $cStmt->execute();
            $cRow = $cStmt->get_result()->fetch_object();
            if ($cRow) {
                $statusMsg = "Your maintenance request #$reqId has been updated to: $newStatus.";
                if ($vendorId) {
                    $vStmt = $mysqli->prepare("SELECT name, phone FROM system_vendors WHERE id=?");
                    $vStmt->bind_param('i', $vendorId);
                    $vStmt->execute();
                    $vendor = $vStmt->get_result()->fetch_object();
                    if ($vendor) $statusMsg .= " Vendor assigned: {$vendor->name} ({$vendor->phone}).";
                }
                sendNotification($cRow->client_id, 'Maintenance Update', $statusMsg, $uid);
            }
        } else {
            $msg = 'Update failed. Please try again.'; $msgType = 'danger';
        }
    }

    // ── Fetch all requests for this landlord's properties ──
    $query = "SELECT mr.*, h.name as hostel_name,
        u.full_name as client_name, u.email as client_email, u.contact_no as client_phone,
        v.name as vendor_name
        FROM system_maintenance_requests mr
        JOIN hostels h ON h.id = mr.hostel_id
        LEFT JOIN users u ON u.id = mr.client_id
        LEFT JOIN system_vendors v ON v.id = mr.assigned_vendor_id
        WHERE $tenantWhere
        ORDER BY
            CASE mr.priority
                WHEN 'Emergency' THEN 1
                WHEN 'High' THEN 2
                WHEN 'Medium' THEN 3
                WHEN 'Low' THEN 4
            END,
            mr.created_at DESC";
    $requests = $mysqli->query($query);

    // ── Fetch vendors ──
    $vendors = $mysqli->query("SELECT * FROM system_vendors WHERE status='active' ORDER BY specialty, name");

    // ── Stats ──
    $statsQ = "SELECT
        COUNT(*) as total,
        SUM(status='Open') as open_count,
        SUM(status='In Progress') as progress_count,
        SUM(status='Resolved' OR status='Closed') as resolved_count,
        SUM(priority='Emergency' AND status NOT IN ('Resolved','Closed','Cancelled')) as emergencies
        FROM system_maintenance_requests mr WHERE $tenantWhere";
    $stats = $mysqli->query($statsQ)->fetch_object();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Maintenance Management - ShimaHome</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <style>
        .priority-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .priority-Low        { background: #d4edda; color: #155724; }
        .priority-Medium     { background: #fff3cd; color: #856404; }
        .priority-High       { background: #f8d7da; color: #721c24; }
        .priority-Emergency  { background: #721c24; color: #fff; animation: pulse-emergency 1.5s infinite; }
        @keyframes pulse-emergency { 0%,100%{opacity:1} 50%{opacity:.7} }
        .status-Open         { background: #cce5ff; color: #004085; }
        .status-In-Progress  { background: #fff3cd; color: #856404; }
        .status-Resolved     { background: #d4edda; color: #155724; }
        .status-Closed       { background: #e2e3e5; color: #383d41; }
        .status-Cancelled    { background: #f8d7da; color: #721c24; }
        .stat-card { border-left: 4px solid; }
        .stat-card.border-danger  { border-color: #dc3545 !important; }
        .stat-card.border-warning { border-color: #ffc107 !important; }
        .stat-card.border-info    { border-color: #17a2b8 !important; }
        .stat-card.border-success { border-color: #28a745 !important; }
    </style>
</head>
<body>
    <div class="preloader"><div class="lds-ripple"><div class="lds-pos"></div><div class="lds-pos"></div></div></div>
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6"><?php include 'includes/navigation.php'?></header>
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6"><?php include 'includes/sidebar.php'?></div>
        </aside>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Maintenance Management</h3>
                        <nav aria-label="breadcrumb"><ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Maintenance</li>
                        </ol></nav>
                    </div>
                </div>
            </div>
            <div class="container-fluid">

                <?php if($msg): ?>
                <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show"><?php echo $msg; ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card border-danger mb-3">
                            <div class="card-body py-3">
                                <h5 class="text-danger mb-0"><?php echo $stats->emergencies ?? 0; ?></h5>
                                <small class="text-muted">🚨 Active Emergencies</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card border-warning mb-3">
                            <div class="card-body py-3">
                                <h5 class="text-warning mb-0"><?php echo $stats->open_count ?? 0; ?></h5>
                                <small class="text-muted">📋 Open Requests</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card border-info mb-3">
                            <div class="card-body py-3">
                                <h5 class="text-info mb-0"><?php echo $stats->progress_count ?? 0; ?></h5>
                                <small class="text-muted">🔧 In Progress</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card border-success mb-3">
                            <div class="card-body py-3">
                                <h5 class="text-success mb-0"><?php echo $stats->resolved_count ?? 0; ?></h5>
                                <small class="text-muted">✅ Resolved / Closed</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><i class="fas fa-tools mr-2 text-primary"></i>All Maintenance Requests</h4>
                        <div class="table-responsive">
                            <table id="maintTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Client</th>
                                        <th>Property / Room</th>
                                        <th>Subject</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Vendor</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if($requests): while($r = $requests->fetch_object()): ?>
                                    <tr>
                                        <td><?php echo $r->id; ?></td>
                                        <td>
                                            <?php echo htmlentities($r->client_name); ?>
                                            <br><small class="text-muted"><?php echo htmlentities($r->client_email); ?></small>
                                        </td>
                                        <td><?php echo htmlentities($r->hostel_name); ?> <br><small>Room <?php echo $r->room_no; ?></small></td>
                                        <td><?php echo htmlentities($r->subject); ?></td>
                                        <td><?php echo $r->category; ?></td>
                                        <td><span class="priority-badge priority-<?php echo $r->priority; ?>"><?php echo $r->priority; ?></span></td>
                                        <td><span class="priority-badge status-<?php echo str_replace(' ','-',$r->status); ?>"><?php echo $r->status; ?></span></td>
                                        <td><?php echo $r->vendor_name ? htmlentities($r->vendor_name) : '—'; ?></td>
                                        <td><?php echo date('d M Y', strtotime($r->created_at)); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?php echo $r->id; ?>"><i class="fas fa-edit"></i></button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $r->id; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <input type="hidden" name="request_id" value="<?php echo $r->id; ?>">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">Manage Request #<?php echo $r->id; ?></h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Request Info -->
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <strong>Client:</strong> <?php echo htmlentities($r->client_name); ?><br>
                                                                <strong>Phone:</strong> <?php echo htmlentities($r->client_phone); ?><br>
                                                                <strong>Property:</strong> <?php echo htmlentities($r->hostel_name); ?> – Room <?php echo $r->room_no; ?>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Category:</strong> <?php echo $r->category; ?><br>
                                                                <strong>Priority:</strong> <span class="priority-badge priority-<?php echo $r->priority; ?>"><?php echo $r->priority; ?></span><br>
                                                                <strong>Submitted:</strong> <?php echo date('d M Y H:i', strtotime($r->created_at)); ?>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <p><strong>Description:</strong></p>
                                                        <p><?php echo nl2br(htmlentities($r->description)); ?></p>
                                                        <?php if($r->photo_path): ?>
                                                        <img src="../<?php echo $r->photo_path; ?>" class="img-fluid rounded mb-3" style="max-height:200px;">
                                                        <?php endif; ?>
                                                        <hr>
                                                        <!-- Action Fields -->
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Update Status</label>
                                                                    <select name="status" class="form-control">
                                                                        <?php foreach(['Open','In Progress','Resolved','Closed','Cancelled'] as $s): ?>
                                                                        <option value="<?php echo $s; ?>" <?php if($r->status==$s) echo 'selected'; ?>><?php echo $s; ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Assign Vendor</label>
                                                                    <select name="vendor_id" class="form-control">
                                                                        <option value="">-- None --</option>
                                                                        <?php
                                                                        $vendors->data_seek(0);
                                                                        while($v = $vendors->fetch_object()): ?>
                                                                        <option value="<?php echo $v->id; ?>" <?php if($r->assigned_vendor_id==$v->id) echo 'selected'; ?>>
                                                                            <?php echo htmlentities($v->name); ?> (<?php echo $v->specialty; ?>) ⭐<?php echo $v->rating; ?>
                                                                        </option>
                                                                        <?php endwhile; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Landlord Notes (visible to client)</label>
                                                            <textarea name="landlord_notes" class="form-control" rows="2"><?php echo htmlentities($r->landlord_notes); ?></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Resolution Notes</label>
                                                            <textarea name="resolution_notes" class="form-control" rows="2"><?php echo htmlentities($r->resolution_notes); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_request" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; endif; ?>
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
    <script>$(function(){ $('#maintTable').DataTable({ order: [[5, 'asc'], [8, 'desc']] }); });</script>
</body>
</html>
