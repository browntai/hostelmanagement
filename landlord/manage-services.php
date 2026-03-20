<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    include('../includes/hostel-helper.php');
    include_once('../includes/toast-helper.php');

    // Handle Service Toggle & Settings
    if(isset($_POST['toggle_service'])) {
        $hostel_id = intval($_POST['hostel_id']);
        $service_key = $_POST['service_key'];
        $is_enabled = intval($_POST['is_enabled']);
        $max_capacity = isset($_POST['max_capacity']) ? intval($_POST['max_capacity']) : 10;

        // Check if hostel belongs to tenant
        $check = $mysqli->prepare("SELECT id FROM hostels WHERE id=? AND tenant_id=?");
        $check->bind_param('ii', $hostel_id, $tenantId);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
            $upsert = $mysqli->prepare("INSERT INTO hostel_services (hostel_id, service_key, is_enabled, max_capacity) 
                                       VALUES (?, ?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE is_enabled = ?, max_capacity = ?");
            $upsert->bind_param('isiiii', $hostel_id, $service_key, $is_enabled, $max_capacity, $is_enabled, $max_capacity);
            if($upsert->execute()) {
                setToast('success', 'Service settings updated successfully!');
            }
        }
        header("Location: manage-services.php");
        exit();
    }

    // Handle Daycare Request Actions
    if(isset($_GET['action']) && isset($_GET['id'])) {
        $requestId = intval($_GET['id']);
        $action = $_GET['action'];
        $newStatus = ($action == 'approve') ? 'approved' : 'declined';
        
        // Verify the request belongs to a hostel owned by this landlord
        $verify = $mysqli->prepare("SELECT db.id FROM daycare_bookings db 
                                   JOIN hostels h ON db.hostel_id = h.id 
                                   WHERE db.id = ? AND h.tenant_id = ?");
        $verify->bind_param('ii', $requestId, $tenantId);
        $verify->execute();
        if($verify->get_result()->num_rows > 0) {
            $update = $mysqli->prepare("UPDATE daycare_bookings SET status = ? WHERE id = ?");
            $update->bind_param('si', $newStatus, $requestId);
            if($update->execute()) {
                setToast('success', "Daycare request " . ucfirst($newStatus) . "!");
            }
        }
        header("Location: manage-services.php");
        exit();
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Property Services - Hostel Management System</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
    <style>
        .service-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #2e5e99;
            border-color: #2e5e99;
        }
    </style>
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        
        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>
        
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/sidebar.php'?>
            </div>
        </aside>
        
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Property Services</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0 p-0 text-muted">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Manage Services</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <!-- Service Toggles -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Hostel Service Configuration</h4>
                                <div class="row">
                                    <?php
                                    $query = "SELECT h.*, hs.max_capacity FROM hostels h 
                                              LEFT JOIN hostel_services hs ON h.id = hs.hostel_id AND hs.service_key = 'daycare' 
                                              WHERE h.tenant_id = ? ORDER BY h.name ASC";
                                    $stmt = $mysqli->prepare($query);
                                    $stmt->bind_param('i', $tenantId);
                                    $stmt->execute();
                                    $hostels = $stmt->get_result();
                                    
                                    if($hostels->num_rows == 0):
                                    ?>
                                    <div class="col-12 text-center py-4">
                                        <p class="text-muted">No hostels found. Add a hostel first.</p>
                                    </div>
                                    <?php
                                    else:
                                        while($h = $hostels->fetch_object()):
                                            $daycare_enabled = isServiceEnabled($mysqli, $h->id, 'daycare');
                                            $capacity = $h->max_capacity ?? 10;
                                    ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card service-card h-100 shadow-none bg-light rounded-lg">
                                            <div class="card-body">
                                                <h5 class="font-weight-bold text-dark mb-3">
                                                    <i data-feather="home" class="feather-sm text-primary mr-2"></i>
                                                    <?php echo htmlentities($h->name); ?>
                                                </h5>
                                                
                                                <hr>
                                                
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <div>
                                                        <h6 class="mb-0 font-weight-medium">Daycare Service</h6>
                                                        <small class="text-muted">Allow residents to book childcare</small>
                                                    </div>
                                                    <form action="" method="POST" class="mb-0">
                                                        <input type="hidden" name="hostel_id" value="<?php echo $h->id; ?>">
                                                        <input type="hidden" name="service_key" value="daycare">
                                                        <input type="hidden" name="is_enabled" value="<?php echo $daycare_enabled ? 0 : 1; ?>">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="switch-<?php echo $h->id; ?>" <?php echo $daycare_enabled ? 'checked' : ''; ?> onchange="this.form.submit()">
                                                            <label class="custom-control-label" for="switch-<?php echo $h->id; ?>"></label>
                                                        </div>
                                                        <input type="hidden" name="toggle_service" value="1">
                                                    </form>
                                                </div>

                                                <?php if($daycare_enabled): ?>
                                                <div class="mt-3 bg-white p-2 rounded border-light">
                                                    <form action="" method="POST" class="d-flex align-items-center justify-content-between mb-0">
                                                        <input type="hidden" name="hostel_id" value="<?php echo $h->id; ?>">
                                                        <input type="hidden" name="service_key" value="daycare">
                                                        <input type="hidden" name="is_enabled" value="1">
                                                        <div class="form-group mb-0 mr-2 flex-grow-1">
                                                            <label class="small text-muted mb-0">Daily Capacity</label>
                                                            <input type="number" name="max_capacity" class="form-control form-control-sm" value="<?php echo $capacity; ?>" min="1">
                                                        </div>
                                                        <button type="submit" name="toggle_service" class="btn btn-sm btn-outline-primary mt-3">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="mt-2 text-right">
                                                    <a href="daycare-attendance.php?hostel_id=<?php echo $h->id; ?>" class="small font-weight-bold text-info">
                                                        <i class="fas fa-calendar-check mr-1"></i> Attendance Manager
                                                    </a>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <!-- Placeholder for future services -->
                                                <div class="d-flex justify-content-between align-items-center mt-4 opacity-5">
                                                    <div>
                                                        <h6 class="mb-0 font-weight-medium">Laundry Service</h6>
                                                        <small class="text-muted">Coming soon</small>
                                                    </div>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" disabled>
                                                        <label class="custom-control-label"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php 
                                        endwhile;
                                    endif; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daycare Requests Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title">Daycare Booking Requests</h4>
                                <div class="table-responsive">
                                    <table id="zero_config" class="table table-striped table-bordered no-wrap">
                                        <thead>
                                            <tr>
                                                <th>Client Name</th>
                                                <th>Hostel</th>
                                                <th>Child Details</th>
                                                <th>Date Requested</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $reqQuery = "SELECT db.*, u.full_name, h.name as hostel_name 
                                                        FROM daycare_bookings db 
                                                        JOIN users u ON db.client_id = u.id 
                                                        JOIN hostels h ON db.hostel_id = h.id 
                                                        WHERE h.tenant_id = ? 
                                                        ORDER BY db.created_at DESC";
                                            $reqStmt = $mysqli->prepare($reqQuery);
                                            $reqStmt->bind_param('i', $tenantId);
                                            $reqStmt->execute();
                                            $requests = $reqStmt->get_result();
                                            
                                            if($requests->num_rows == 0):
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No daycare requests found.</td>
                                            </tr>
                                            <?php
                                            else:
                                                while($r = $requests->fetch_object()):
                                                    $statusColor = ($r->status == 'approved') ? 'success' : (($r->status == 'declined') ? 'danger' : 'warning');
                                            ?>
                                            <tr>
                                                <td><?php echo htmlentities($r->full_name); ?></td>
                                                <td><?php echo htmlentities($r->hostel_name); ?></td>
                                                <td>
                                                    <strong><?php echo htmlentities($r->child_name); ?></strong><br>
                                                    <small class="text-muted">Age: <?php echo $r->child_age; ?> yrs</small>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($r->booking_date)); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $statusColor; ?> px-2 py-1">
                                                        <?php echo ucfirst($r->status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($r->status == 'pending'): ?>
                                                    <a href="?action=approve&id=<?php echo $r->id; ?>" class="btn btn-success btn-sm px-3" onclick="return confirm('Approve this request?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                    <a href="?action=decline&id=<?php echo $r->id; ?>" class="btn btn-danger btn-sm px-3" onclick="return confirm('Decline this request?')">
                                                        <i class="fas fa-times"></i> Decline
                                                    </a>
                                                    <?php else: ?>
                                                    <span class="text-muted small">No actions</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            endif; 
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/footer.php' ?>
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
    <script>
        $(".preloader").fadeOut();
    </script>
</body>

</html>
