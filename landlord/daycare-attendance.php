<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    // Security Check: Is daycare enabled for any of this landlord's hostels?
    $any_daycare_enabled = false;
    $check_q = "SELECT COUNT(*) as cnt FROM hostel_services WHERE is_enabled=1 AND service_key='daycare' AND hostel_id IN (SELECT id FROM hostels WHERE tenant_id=?)";
    $c_stmt = $mysqli->prepare($check_q);
    $c_stmt->bind_param('i', $tenantId);
    $c_stmt->execute();
    $c_res = $c_stmt->get_result();
    if($c_row = $c_res->fetch_object()){
        $any_daycare_enabled = ($c_row->cnt > 0);
    }
    if(!$any_daycare_enabled) {
        header("Location: dashboard.php");
        exit();
    }

    include('../includes/hostel-helper.php');
    include_once('../includes/toast-helper.php');

    $selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $selected_hostel = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : null;

    // Handle Attendance Actions
    if(isset($_POST['update_attendance'])) {
        $booking_id = intval($_POST['booking_id']);
        $action = $_POST['action'];
        $notes = $_POST['landlord_notes'];
        $caretaker_id = isset($_POST['caretaker_id']) ? intval($_POST['caretaker_id']) : null;

        if($action == 'check_in') {
            $stmt = $mysqli->prepare("UPDATE daycare_bookings SET status='checked_in', check_in_time=NOW(), landlord_notes=?, assigned_caretaker_id=? WHERE id=?");
            $stmt->bind_param('sii', $notes, $caretaker_id, $booking_id);
        } elseif($action == 'check_out') {
            $stmt = $mysqli->prepare("UPDATE daycare_bookings SET status='checked_out', check_out_time=NOW(), landlord_notes=? WHERE id=?");
            $stmt->bind_param('si', $notes, $booking_id);
        } elseif($action == 'update_notes') {
            $stmt = $mysqli->prepare("UPDATE daycare_bookings SET landlord_notes=?, assigned_caretaker_id=? WHERE id=?");
            $stmt->bind_param('sii', $notes, $caretaker_id, $booking_id);
        }

        if(isset($stmt) && $stmt->execute()) {
            setToast('success', 'Attendance record updated!');
        }
        header("Location: daycare-attendance.php?date=$selected_date&hostel_id=$selected_hostel");
        exit();
    }

    // Fetch Hostels for dropdown
    $hQuery = "SELECT id, name FROM hostels WHERE tenant_id = ? ORDER BY name ASC";
    $hStmt = $mysqli->prepare($hQuery);
    $hStmt->bind_param('i', $tenantId);
    $hStmt->execute();
    $hostels = $hStmt->get_result();

    // Fetch Caretakers for dropdown
    $cQuery = "SELECT id, full_name FROM users WHERE tenant_id = ? AND role='caretaker' AND status='active'";
    $cStmt = $mysqli->prepare($cQuery);
    $cStmt->bind_param('i', $tenantId);
    $cStmt->execute();
    $caretakers = $cStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daycare Attendance - Hostel Management System</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
</head>
<body>
    <div class="preloader">
        <div class="lds-ripple"><div class="lds-pos"></div><div class="lds-pos"></div></div>
    </div>
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6"><?php include 'includes/navigation.php'?></header>
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6"><?php include 'includes/sidebar.php'?></div>
        </aside>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Daycare Attendance</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0 p-0 text-muted">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Attendance Manager</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted">Select Date</label>
                                <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small text-muted">Filter by Hostel</label>
                                <select name="hostel_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">All My Hostels</option>
                                    <?php while($h = $hostels->fetch_object()): ?>
                                    <option value="<?php echo $h->id; ?>" <?php echo ($selected_hostel == $h->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlentities($h->name); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <a href="daycare-attendance.php" class="btn btn-outline-secondary btn-block">Reset Filters</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Daily Attendance List - <?php echo date('M d, Y', strtotime($selected_date)); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered no-wrap">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Child & Parent</th>
                                        <th>Hostel</th>
                                        <th>Schedule</th>
                                        <th>Assignment</th>
                                        <th>Status / Times</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $attendanceQuery = "SELECT db.*, u.full_name as parent_name, h.name as hostel_name 
                                                       FROM daycare_bookings db 
                                                       JOIN users u ON db.client_id = u.id 
                                                       JOIN hostels h ON db.hostel_id = h.id 
                                                       WHERE h.tenant_id = ? AND db.booking_date = ? 
                                                       " . ($selected_hostel ? "AND db.hostel_id = $selected_hostel" : "") . "
                                                       AND db.status NOT IN ('pending', 'declined')
                                                       ORDER BY db.created_at ASC";
                                    $aStmt = $mysqli->prepare($attendanceQuery);
                                    $aStmt->bind_param('is', $tenantId, $selected_date);
                                    $aStmt->execute();
                                    $attendance = $aStmt->get_result();

                                    if($attendance->num_rows == 0):
                                    ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No confirmed daycare bookings for this selection.</td></tr>
                                    <?php
                                    else:
                                        while($row = $attendance->fetch_object()):
                                            $statusBadge = ($row->status == 'checked_in') ? 'primary' : (($row->status == 'checked_out') ? 'success' : 'info');
                                    ?>
                                    <tr>
                                        <td>
                                            <h6 class="font-weight-bold mb-0"><?php echo htmlentities($row->child_name); ?></h6>
                                            <small class="text-muted">Parent: <?php echo htmlentities($row->parent_name); ?></small>
                                        </td>
                                        <td><small><?php echo htmlentities($row->hostel_name); ?></small></td>
                                        <td>
                                            <?php if($row->check_in_time): ?>
                                                <small class="text-primary d-block">In: <?php echo date('H:i', strtotime($row->check_in_time)); ?></small>
                                            <?php endif; ?>
                                            <?php if($row->check_out_time): ?>
                                                <small class="text-success d-block">Out: <?php echo date('H:i', strtotime($row->check_out_time)); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="" method="POST" class="d-flex">
                                                <input type="hidden" name="booking_id" value="<?php echo $row->id; ?>">
                                                <input type="hidden" name="update_attendance" value="1">
                                                <input type="hidden" name="action" value="update_notes">
                                                <select name="caretaker_id" class="form-control form-control-sm mr-1" onchange="this.form.submit()">
                                                    <option value="">No Caregiver</option>
                                                    <?php foreach($caretakers as $ct): ?>
                                                    <option value="<?php echo $ct['id']; ?>" <?php echo ($row->assigned_caretaker_id == $ct['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlentities($ct['full_name']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $statusBadge; ?> mb-2"><?php echo strtoupper(str_replace('_', ' ', $row->status)); ?></span>
                                            <br>
                                            <button class="btn btn-sm btn-link p-0 text-info" data-toggle="modal" data-target="#noteModal-<?php echo $row->id; ?>">
                                                <i class="fas fa-edit"></i> Notes: <?php echo $row->landlord_notes ? 'View' : 'Add'; ?>
                                            </button>
                                        </td>
                                        <td>
                                            <?php if($row->status == 'approved'): ?>
                                                <form action="" method="POST">
                                                    <input type="hidden" name="booking_id" value="<?php echo $row->id; ?>">
                                                    <input type="hidden" name="action" value="check_in">
                                                    <button type="submit" name="update_attendance" class="btn btn-primary btn-sm btn-block">
                                                        <i class="fas fa-sign-in-alt mt-1"></i> Check-In
                                                    </button>
                                                </form>
                                            <?php elseif($row->status == 'checked_in'): ?>
                                                <form action="" method="POST">
                                                    <input type="hidden" name="booking_id" value="<?php echo $row->id; ?>">
                                                    <input type="hidden" name="action" value="check_out">
                                                    <button type="submit" name="update_attendance" class="btn btn-success btn-sm btn-block">
                                                        <i class="fas fa-sign-out-alt mt-1"></i> Check-Out
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Notes Modal -->
                                    <div class="modal fade" id="noteModal-<?php echo $row->id; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Attendance Notes - <?php echo htmlentities($row->child_name); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="booking_id" value="<?php echo $row->id; ?>">
                                                        <input type="hidden" name="action" value="update_notes">
                                                        <input type="hidden" name="caretaker_id" value="<?php echo $row->assigned_caretaker_id; ?>">
                                                        <div class="form-group">
                                                            <label>Daily Report / Notes for Parent</label>
                                                            <textarea name="landlord_notes" class="form-control" rows="4" placeholder="e.g. Ate well, participated in arts & crafts..."><?php echo htmlentities($row->landlord_notes); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_attendance" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
