<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    // Verify role
    if($_SESSION['role'] != 'caretaker') {
        header("location:../login.php");
        exit();
    }

    $userId = $_SESSION['id'];
    $tenantId = $_SESSION['tenant_id'];

    // Security Check: Is daycare enabled for the caretaker's assigned hostel?
    $caretaker_daycare_enabled = false;
    $stmt = $mysqli->prepare("SELECT hs.is_enabled FROM hostel_services hs 
                              JOIN users u ON hs.hostel_id = u.assigned_hostel_id 
                              WHERE u.id = ? AND hs.service_key = 'daycare'");
    if($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_object()) {
            $caretaker_daycare_enabled = (bool)$row->is_enabled;
        }
        $stmt->close();
    }

    if(!$caretaker_daycare_enabled) {
        header("Location: dashboard.php");
        exit();
    }

    include_once('../includes/toast-helper.php');

    $selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // Handle Attendance Actions
    if(isset($_POST['update_attendance'])) {
        $booking_id = intval($_POST['booking_id']);
        $action = $_POST['action'];
        $notes = $_POST['caretaker_notes'];

        if($action == 'check_in') {
            $stmt = $mysqli->prepare("UPDATE daycare_bookings SET status='checked_in', check_in_time=NOW(), landlord_notes=CONCAT(COALESCE(landlord_notes,''), '\nCaretaker: ', ?) WHERE id=? AND assigned_caretaker_id=?");
            $stmt->bind_param('sii', $notes, $booking_id, $userId);
        } elseif($action == 'check_out') {
            $stmt = $mysqli->prepare("UPDATE daycare_bookings SET status='checked_out', check_out_time=NOW(), landlord_notes=CONCAT(COALESCE(landlord_notes,''), '\nCaretaker (Out): ', ?) WHERE id=? AND assigned_caretaker_id=?");
            $stmt->bind_param('sii', $notes, $booking_id, $userId);
        }

        if(isset($stmt) && $stmt->execute()) {
            setToast('success', 'Attendance updated!');
        }
        header("Location: daycare-attendance.php?date=$selected_date");
        exit();
    }

    // Fetch My Assignments for the date
    $query = "SELECT db.*, u.full_name as parent_name, h.name as hostel_name 
              FROM daycare_bookings db 
              JOIN users u ON db.client_id = u.id 
              JOIN hostels h ON db.hostel_id = h.id 
              WHERE db.assigned_caretaker_id = ? AND db.booking_date = ? 
              ORDER BY db.created_at ASC";
    $aStmt = $mysqli->prepare($query);
    $aStmt->bind_param('is', $userId, $selected_date);
    $aStmt->execute();
    $attendance = $aStmt->get_result();
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Daycare Duties - Caretaker Portal</title>
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">My Daycare Duties</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0 p-0 text-muted">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Attendance</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-4">
                                <label class="small text-muted">Select Date</label>
                                <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Assignments for <?php echo date('M d, Y', strtotime($selected_date)); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-hover no-wrap">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Child Details</th>
                                        <th>Slot</th>
                                        <th>Status</th>
                                        <th>Times</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($attendance->num_rows == 0): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No assignments for this date.</td></tr>
                                    <?php else: while($row = $attendance->fetch_object()): 
                                        $statusColor = ($row->status == 'checked_in') ? 'primary' : (($row->status == 'checked_out') ? 'success' : 'info');
                                    ?>
                                    <tr>
                                        <td>
                                            <h6 class="font-weight-bold mb-0"><?php echo htmlentities($row->child_name); ?></h6>
                                            <small class="text-muted">Parent: <?php echo htmlentities($row->parent_name); ?></small>
                                        </td>
                                        <td><span class="badge badge-light-secondary"><?php echo htmlentities($row->time_slot); ?></span></td>
                                        <td><span class="badge badge-<?php echo $statusColor; ?>"><?php echo strtoupper(str_replace('_', ' ', $row->status)); ?></span></td>
                                        <td>
                                            <?php if($row->check_in_time): ?>
                                            <small class="d-block text-primary">In: <?php echo date('H:i', strtotime($row->check_in_time)); ?></small>
                                            <?php endif; ?>
                                            <?php if($row->check_out_time): ?>
                                            <small class="d-block text-success">Out: <?php echo date('H:i', strtotime($row->check_out_time)); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row->status == 'approved'): ?>
                                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#checkInModal-<?php echo $row->id; ?>">Check-In</button>
                                            <?php elseif($row->status == 'checked_in'): ?>
                                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#checkOutModal-<?php echo $row->id; ?>">Check-Out</button>
                                            <?php else: ?>
                                                <span class="text-muted small">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Check-In Modal -->
                                    <div class="modal fade" id="checkInModal-<?php echo $row->id; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST" class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Check-In: <?php echo htmlentities($row->child_name); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="booking_id" value="<?php echo $row->id; ?>">
                                                    <input type="hidden" name="action" value="check_in">
                                                    <div class="form-group">
                                                        <label>Initial Observations / Notes</label>
                                                        <textarea name="caretaker_notes" class="form-control" rows="3" placeholder="e.g. Came in sleeping, brought diaper bag..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="update_attendance" class="btn btn-primary">Confirm Check-In</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Check-Out Modal -->
                                    <div class="modal fade" id="checkOutModal-<?php echo $row->id; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST" class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Check-Out: <?php echo htmlentities($row->child_name); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="booking_id" value="<?php echo $row->id; ?>">
                                                    <input type="hidden" name="action" value="check_out">
                                                    <div class="form-group">
                                                        <label>Daily Activity Report</label>
                                                        <textarea name="caretaker_notes" class="form-control" rows="4" placeholder="How was the child's day?"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="update_attendance" class="btn btn-success">Confirm Check-Out</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <?php endwhile; endif; ?>
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
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>
    <script>$(".preloader").fadeOut();</script>
</body>
</html>
