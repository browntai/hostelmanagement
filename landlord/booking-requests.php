<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    // Approve booking
    if(isset($_GET['approve'])) {
        $id = intval($_GET['approve']);
        
        // Get client info for notification
        $stmt_info = $mysqli->prepare("SELECT emailid, firstName, lastName, roomno FROM bookings WHERE id = ?");
        $stmt_info->bind_param('i', $id);
        $stmt_info->execute();
        $stmt_info->bind_result($std_email, $fname, $lname, $roomno);
        $stmt_info->fetch();
        $stmt_info->close();

        $query = "UPDATE bookings SET booking_status = 'confirmed' WHERE id = ? AND tenant_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $id, $tenantId);
        if($stmt->execute()) {
            include_once('../includes/log-helper.php');
            include_once('../includes/toast-helper.php');
            include_once('../includes/notification-helper.php');
            
            // Log Activity
            $uemail = isset($_SESSION['login']) ? $_SESSION['login'] : 'unknown';
            logActivity($_SESSION['id'], $uemail, 'Landlord/Admin', 'Approve Booking', "Confirmed booking for $fname $lname (ID: $id)");
            
            // Send Notification to Client
            $stmt_u = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_u->bind_param('s', $std_email);
            $stmt_u->execute();
            $stmt_u->bind_result($std_uid);
            if($stmt_u->fetch()) {
                $stmt_u->close();
                sendNotification($std_uid, 'Booking Approved', "Your booking for room $roomno has been confirmed.");
            } else {
                $stmt_u->close();
            }

            // Notify Admin
            $a_q = "SELECT id FROM users WHERE role='admin' AND tenant_id IS NULL LIMIT 1";
            $a_res = $mysqli->query($a_q);
            if($a_row = $a_res->fetch_object()){
                sendNotification($a_row->id, 'Booking Confirmed', "Landlord {$_SESSION['full_name']} confirmed booking for $fname $lname (Room $roomno).", $_SESSION['id']);
            }

            // Mark room as booked (Confirming it's set)
            $updateRoomSql = "UPDATE rooms SET status = 'booked' WHERE room_no = ? AND hostel_id = (SELECT hostel_id FROM bookings WHERE id = ?)";
            $updateRoomStmt = $mysqli->prepare($updateRoomSql);
            $updateRoomStmt->bind_param('si', $roomno, $id);
            $updateRoomStmt->execute();
            $updateRoomStmt->close();

            setToast('success', 'Booking confirmed successfully!');
        }
        $stmt->close();
        header("Location: booking-requests.php");
        exit();
    }

    // Reject booking
    if(isset($_GET['reject'])) {
        $id = intval($_GET['reject']);
        
        // Get client info for notification
        $stmt_info = $mysqli->prepare("SELECT emailid, firstName, lastName FROM bookings WHERE id = ?");
        $stmt_info->bind_param('i', $id);
        $stmt_info->execute();
        $stmt_info->bind_result($std_email, $fname, $lname);
        $stmt_info->fetch();
        $stmt_info->close();

        $query = "UPDATE bookings SET booking_status = 'rejected' WHERE id = ? AND tenant_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $id, $tenantId);
        if($stmt->execute()) {
            include_once('../includes/log-helper.php');
            include_once('../includes/toast-helper.php');
            include_once('../includes/notification-helper.php');

            // Log Activity
            $uemail = isset($_SESSION['login']) ? $_SESSION['login'] : 'unknown';
            logActivity($_SESSION['id'], $uemail, 'Landlord/Admin', 'Reject Booking', "Rejected booking for $fname $lname (ID: $id)");
            
            // Send Notification to Client
            $stmt_u = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_u->bind_param('s', $std_email);
            $stmt_u->execute();
            $stmt_u->bind_result($std_uid);
            if($stmt_u->fetch()) {
                $stmt_u->close();
                sendNotification($std_uid, 'Booking Rejected', "Sorry, your booking request has been rejected. Please contact the landlord.");
            } else {
                $stmt_u->close();
            }

            // Notify Admin
            $a_q = "SELECT id FROM users WHERE role='admin' AND tenant_id IS NULL LIMIT 1";
            $a_res = $mysqli->query($a_q);
            if($a_row = $a_res->fetch_object()){
                sendNotification($a_row->id, 'Booking Rejected', "Landlord {$_SESSION['full_name']} rejected booking for $fname $lname.", $_SESSION['id']);
            }

            // Mark room as available again on rejection
            // Get room number and hostel_id first
            $stmt_r = $mysqli->prepare("SELECT roomno, hostel_id FROM bookings WHERE id = ?");
            $stmt_r->bind_param('i', $id);
            $stmt_r->execute();
            $stmt_r->bind_result($r_no, $h_id);
            if($stmt_r->fetch()){
                $stmt_r->close();
                $updateRoomSql = "UPDATE rooms SET status = 'available' WHERE room_no = ? AND hostel_id = ?";
                $updateRoomStmt = $mysqli->prepare($updateRoomSql);
                $updateRoomStmt->bind_param('si', $r_no, $h_id);
                $updateRoomStmt->execute();
                $updateRoomStmt->close();
            } else {
                $stmt_r->close();
            }

            setToast('error', 'Booking rejected.');
        }
        $stmt->close();
        header("Location: booking-requests.php");
        exit();
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Requests - Hostel Management System</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Incoming Booking Requests</h4>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <!-- Search -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="customize-input">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0 shadow-sm"><i data-feather="search" class="feather-sm"></i></span>
                                </div>
                                <input class="form-control border-0 shadow-sm" type="search" id="requestSearch" placeholder="Search incoming requests by name, hostel, or room..." aria-label="Search">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="requestsGrid">
                <?php
                $query = "SELECT bookings.*, hostels.name as hostel_name 
                          FROM bookings 
                          LEFT JOIN hostels ON bookings.hostel_id = hostels.id 
                          WHERE bookings.tenant_id = ? AND bookings.booking_status = 'pending'
                          ORDER BY bookings.postingDate DESC";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('i', $tenantId);
                $stmt->execute();
                $res = $stmt->get_result();
                
                if($res->num_rows == 0):
                ?>
                    <div class="col-12 text-center py-5">
                        <div class="mb-3">
                            <i data-feather="inbox" class="text-muted" style="width: 60px; height: 60px;"></i>
                        </div>
                        <h4>No Pending Requests</h4>
                        <p class="text-muted">You're all caught up! New requests will appear here.</p>
                    </div>
                <?php
                else:
                    while($row = $res->fetch_object()) {
                        $fullName = $row->firstName . ' ' . $row->lastName;
                        $initials = strtoupper(substr($row->firstName, 0, 1) . substr($row->lastName, 0, 1));
                ?>
                    <div class="col-md-6 col-lg-4 mb-4 request-card" 
                         data-name="<?php echo strtolower($fullName); ?>" 
                         data-hostel="<?php echo strtolower($row->hostel_name); ?>" 
                         data-room="<?php echo strtolower($row->roomno); ?>">
                        <div class="card h-100 shadow-sm border-0 transition-all hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 font-weight-bold" style="width: 45px; height: 45px; font-size: 0.9rem;">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div>
                                        <h5 class="card-title text-dark font-weight-bold mb-0">
                                            <a href="clients-profile.php?id=<?php echo $row->id; ?>" class="text-dark"><?php echo htmlentities($fullName); ?></a>
                                        </h5>
                                        <small class="text-muted"><i data-feather="mail" class="feather-xs mr-1"></i><?php echo $row->emailid; ?></small>
                                    </div>
                                </div>

                                <div class="bg-light rounded p-3 mb-3">
                                    <div class="row no-gutters">
                                        <div class="col-6 border-right">
                                            <small class="text-muted d-block small">Hostel</small>
                                            <span class="text-dark font-weight-medium small text-truncate d-block"><?php echo htmlentities($row->hostel_name); ?></span>
                                        </div>
                                        <div class="col-6 pl-3">
                                            <small class="text-muted d-block small">Room No</small>
                                            <span class="text-dark font-weight-medium">#<?php echo $row->roomno; ?></span>
                                        </div>
                                    </div>
                                    <div class="row no-gutters mt-2 border-top pt-2">
                                        <div class="col-6 border-right">
                                            <small class="text-muted d-block small">Duration</small>
                                            <span class="text-dark font-weight-medium small"><?php echo $row->duration; ?> Months</span>
                                        </div>
                                        <div class="col-6 pl-3">
                                            <small class="text-muted d-block small">Contact</small>
                                            <span class="text-dark font-weight-medium small"><?php echo $row->contactno; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div class="btn-group shadow-sm">
                                        <a href="booking-requests.php?approve=<?php echo $row->id; ?>" class="btn btn-success btn-sm px-3" onclick="return confirm('Confirm this booking?')">
                                            <i data-feather="check" class="feather-xs mr-1"></i>Approve
                                        </a>
                                        <a href="booking-requests.php?reject=<?php echo $row->id; ?>" class="btn btn-danger btn-sm px-3" onclick="return confirm('Reject this booking?')">
                                            <i data-feather="x" class="feather-xs mr-1"></i>Reject
                                        </a>
                                    </div>
                                    <small class="text-muted small"><?php echo date('d M', strtotime($row->postingDate)); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                    }
                endif; 
                ?>
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
        $(document).ready(function() {
            $(".preloader").fadeOut();
            
            $('#requestSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('.request-card').filter(function() {
                    var name = $(this).data('name').toString().toLowerCase();
                    var hostel = $(this).data('hostel').toString().toLowerCase();
                    var room = $(this).data('room').toString().toLowerCase();
                    $(this).toggle(name.indexOf(value) > -1 || hostel.indexOf(value) > -1 || room.indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>
