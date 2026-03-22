<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    include('../includes/analytics-data.php');
    check_login();

    $uid = $_SESSION['id'];
    $email = $_SESSION['login'];

    // 1. Fetch User Details (Name, Profile Pic)
    $uQuery = "SELECT first_name, last_name, profile_pic FROM users WHERE id=?";
    $uStmt = $mysqli->prepare($uQuery);
    $uStmt->bind_param('i', $uid);
    $uStmt->execute();
    $uRes = $uStmt->get_result();
    $user = $uRes->fetch_object();
    $fullName = $user ? $user->first_name . " " . $user->last_name : "User";
    $profilePic = ($user && $user->profile_pic) ? "../uploads/profiles/" . $user->profile_pic : "../assets/images/users/1.jpg";

    // 2. Fetch Booking Status & Room Details
    $bQuery = "SELECT b.booking_status, b.roomno, b.postingDate, b.duration, h.name as hostel_name 
               FROM bookings b 
               JOIN hostels h ON b.hostel_id = h.id 
               WHERE b.emailid=? 
               ORDER BY b.postingDate DESC LIMIT 1";
    $bStmt = $mysqli->prepare($bQuery);
    $bStmt->bind_param('s', $email);
    $bStmt->execute();
    $bRes = $bStmt->get_result();
    $booking = $bRes->fetch_object();
    
    $bookingStatus = $booking ? $booking->booking_status : "No Booking";
    $roomNo = ($booking && in_array(strtolower($bookingStatus), ['confirmed', 'approved'])) ? $booking->roomno : "N/A";
    $hostelName = $booking ? $booking->hostel_name : "N/A";

    // Stay Duration Logic
    $endDateTimestamp = 0;
    $daysRemaining = 0;
    $showCountdown = false;

    if ($booking && in_array(strtolower($bookingStatus), ['confirmed', 'approved'])) {
        $postingDate = $booking->postingDate;
        $durationMonths = $booking->duration;
        // Calculate end date
        $endDateTimestamp = strtotime($postingDate . " + " . $durationMonths . " months");
        $showCountdown = true;
    }

    // 3. Fetch Total Payments
    $pQuery = "SELECT SUM(amount) as total_paid FROM payments WHERE client_id=? AND status='verified'";
    $pStmt = $mysqli->prepare($pQuery);
    $pStmt->bind_param('i', $uid);
    $pStmt->execute();
    $pRes = $pStmt->get_result();
    $pRow = $pRes->fetch_object();
    $totalPaid = $pRow && $pRow->total_paid ? $pRow->total_paid : 0;

    // 4. Fetch System Stats (e.g. Total Hostels Available)
    $hQuery = "SELECT COUNT(*) as count FROM hostels";
    $hRes = $mysqli->query($hQuery);
    $hRow = $hRes->fetch_object();
    $hostelsCount = $hRow ? $hRow->count : 0;

    // 5. Fetch Recent Activity
    $lQuery = "SELECT * FROM userlog WHERE userId=? ORDER BY loginTime DESC LIMIT 5";
    $lStmt = $mysqli->prepare($lQuery);
    $lStmt->bind_param('i', $uid);
    $lStmt->execute();
    $logRes = $lStmt->get_result();

    // Determine Greeting
    $hour = date('H');
    if ($hour < 12) { $greeting = "Good Morning"; }
    elseif ($hour < 17) { $greeting = "Good Afternoon"; }
    else { $greeting = "Good Evening"; }

    $daycare_overview = getClientDaycareOverview($mysqli, $uid);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Dashboard - Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <style>
        .card-hover:hover { transform: translateY(-5px); transition: all 0.3s ease; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .bg-gradient-primary { background: linear-gradient(135deg, #129d6b 0%, #17c788 100%); color: white; }
        .bg-gradient-success { background: linear-gradient(135deg, #00b09b, #96c93d); color: white; }
        .bg-gradient-warning { background: linear-gradient(135deg, #f7b733, #fc4a1a); color: white; }
        .bg-gradient-danger { background: linear-gradient(135deg, #ff5f6d, #ffc371); color: white; }
        
        .countdown-item {
            display: inline-block;
            text-align: center;
            padding: 10px;
            margin: 0 5px;
            background: rgba(23, 199, 136, 0.06);
            border-radius: 10px;
            min-width: 70px;
        }
        .countdown-number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #17c788;
        }
        .countdown-label {
            display: block;
            font-size: 11px;
            color: #74777b;
            text-transform: uppercase;
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
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6">
            <?php include '../includes/client-navigation.php'?>
        </header>
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include '../includes/client-sidebar.php'?>
            </div>
        </aside>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1"><?php echo $greeting . ", " . htmlentities($user->first_name); ?>!</h3>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                
                <!-- Stats Row -->
                <div class="row">
                    <!-- Booking Status -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-hover border-right">
                            <div class="card-body">
                                <div class="d-flex d-lg-flex d-md-block align-items-center">
                                    <div>
                                        <div class="d-inline-flex align-items-center">
                                            <h2 class="text-dark mb-1 font-weight-medium">
                                                <?php 
                                                    $statusClass = 'badge-status-default';
                                if(strtolower($bookingStatus) == 'confirmed') $statusClass = 'badge-status-confirmed';
                                elseif(strtolower($bookingStatus) == 'approved') $statusClass = 'badge-status-approved';
                                elseif(strtolower($bookingStatus) == 'pending') $statusClass = 'badge-status-pending';
                                elseif(strtolower($bookingStatus) == 'rejected') $statusClass = 'badge-status-rejected';
                                elseif(strtolower($bookingStatus) == 'cancelled') $statusClass = 'badge-status-cancelled';
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?> font-12" style="padding: 5px 10px;"><?php echo htmlentities($bookingStatus); ?></span>
                                            </h2>
                                        </div>
                                        <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Booking Status</h6>
                                    </div>
                                    <div class="ml-auto mt-md-3 mt-lg-0">
                                        <span class="opacity-7 text-muted"><i data-feather="file-text"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- My Room -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-hover border-right">
                            <div class="card-header bg-gradient-brand py-4 d-flex justify-content-between align-items-center border-0">
                                <h5 class="mb-0 text-white font-weight-black"><i data-feather="key" class="mr-2 text-white"></i>Unit #<?php echo htmlentities($roomNo);?></h5>
                                <span class="badge <?php echo $statusClass; ?> px-3 py-2 rounded-pill small font-weight-black"><?php echo strtoupper(htmlentities($bookingStatus)); ?></span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex d-lg-flex d-md-block align-items-center">
                                    <div>
                                        <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">My Room No</h6>
                                    </div>
                                    <div class="ml-auto mt-md-3 mt-lg-0">
                                        <span class="opacity-7 text-muted"><i data-feather="key"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Paid -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-hover border-right">
                            <div class="card-body">
                                <div class="d-flex d-lg-flex d-md-block align-items-center">
                                    <div>
                                        <div class="d-inline-flex align-items-center">
                                            <h2 class="text-dark mb-1 font-weight-medium">KSh <?php echo number_format($totalPaid); ?></h2>
                                        </div>
                                        <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total Paid</h6>
                                    </div>
                                    <div class="ml-auto mt-md-3 mt-lg-0">
                                        <span class="opacity-7 text-muted"><i data-feather="dollar-sign"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Hostels Available -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-hover border-right">
                            <div class="card-body">
                                <div class="d-flex d-lg-flex d-md-block align-items-center">
                                    <div>
                                        <h2 class="text-dark mb-1 font-weight-medium"><?php echo $hostelsCount; ?></h2>
                                        <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">All Hostels</h6>
                                    </div>
                                    <div class="ml-auto mt-md-3 mt-lg-0">
                                        <span class="opacity-7 text-muted"><i data-feather="home"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Row -->
                <div class="row">
                    <!-- Quick Actions & Profile -->
                    <div class="col-lg-4 col-md-12">
                        <!-- Profile Card -->
                        <div class="card overflow-hidden">
                            <div class="card-body text-center bg-gradient-primary text-white" style="border-radius: 4px 4px 0 0;">
                                <div class="profile-pic mb-3 mt-3">
                                    <img src="<?php echo $profilePic; ?>" width="100" class="rounded-circle border border-white" alt="user" />
                                </div>
                                <h4 class="mt-2 text-white"><?php echo htmlentities($fullName); ?></h4>
                                <p class="text-white-50"><?php echo htmlentities($email); ?></p>
                                <a href="profile.php" class="btn btn-sm btn-light text-primary rounded-pill px-4">Edit Profile</a>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title text-muted mb-3">Quick Actions</h5>
                                <div class="d-flex flex-column">
                                    <?php if(strtolower($bookingStatus) == 'no booking' || strtolower($bookingStatus) == 'rejected'): ?>
                                    <a href="book-hostel.php" class="btn btn-outline-primary mb-2 text-left"><i data-feather="plus-circle" class="mr-2 feather-sm"></i> Book a Hostel</a>
                                    <?php endif; ?>
                                    <a href="make-payment.php" class="btn btn-outline-success mb-2 text-left"><i data-feather="credit-card" class="mr-2 feather-sm"></i> Make Payment</a>
                                    <a href="room-details.php" class="btn btn-outline-info mb-2 text-left"><i data-feather="info" class="mr-2 feather-sm"></i> My Room Details</a>
                                    <a href="maintenance-requests.php" class="btn btn-outline-warning mb-2 text-left"><i class="fas fa-wrench mr-2"></i> Report an Issue</a>
                                    <a href="log-activity.php" class="btn btn-outline-secondary mb-2 text-left"><i data-feather="activity" class="mr-2 feather-sm"></i> View Full Activity Log</a>
                                </div>
                            </div>
                        </div>

                        <!-- Daycare Quick Info -->
                        <?php 
                        $daycare_enabled = false;
                        if($booking && in_array(strtolower($bookingStatus), ['confirmed', 'approved'])) {
                            $daycare_enabled = isServiceEnabled($mysqli, $booking->hostel_id, 'daycare');
                        }
                        if($daycare_enabled):
                            if($daycare_overview['children_count'] > 0 || $daycare_overview['last_booking']): 
                        ?>
                        <div class="card border-0 shadow-sm mt-4 overflow-hidden">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-dark font-weight-bold"><i class="fas fa-child text-primary mr-2"></i> Daycare Status</h5>
                                <a href="daycare-service.php" class="btn btn-xs btn-link p-0">Book More</a>
                            </div>
                            <div class="card-body pt-0">
                                <?php if($daycare_overview['last_booking']): 
                                    $lb = $daycare_overview['last_booking'];
                                    $statusColor = 'warning';
                                    if($lb->status == 'approved' || $lb->status == 'confirmed') $statusColor = 'info';
                                    if($lb->status == 'checked_in') $statusColor = 'primary';
                                    if($lb->status == 'checked_out') $statusColor = 'success';
                                ?>
                                    <div class="bg-light p-3 rounded mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small text-muted"><?php echo date('M d, Y', strtotime($lb->booking_date)); ?></span>
                                            <span class="badge badge-<?php echo $statusColor; ?>"><?php echo ucfirst(str_replace('_', ' ', $lb->status)); ?></span>
                                        </div>
                                        <div class="small font-weight-medium text-dark"><?php echo $lb->time_slot; ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="row text-center mt-2">
                                    <div class="col-6 border-right">
                                        <h4 class="mb-0 font-weight-bold"><?php echo $daycare_overview['children_count']; ?></h4>
                                        <small class="text-muted">Children</small>
                                    </div>
                                    <div class="col-6">
                                        <a href="register-child.php" class="small font-weight-bold text-primary d-block mt-2">Manage</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card border-0 shadow-sm mt-4 bg-light-extra">
                            <div class="card-body text-center p-4">
                                <div class="stat-icon bg-light-primary text-primary mx-auto mb-3" style="width: 50px; height: 50px; line-height: 50px; border-radius: 50%;">
                                    <i class="fas fa-child"></i>
                                </div>
                                <h5 class="text-dark font-weight-bold">Daycare Services</h5>
                                <p class="small text-muted">Register your children and book daycare slots easily.</p>
                                <a href="register-child.php" class="btn btn-sm btn-primary btn-block">Get Started</a>
                            </div>
                        </div>
                        <?php endif; endif; ?>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-8 col-md-12">
                        
                        <!-- Stay Countdown Widget -->
                        <?php if($showCountdown): ?>
                        <div class="card bg-light border-primary">
                            <div class="card-body text-center">
                                <h4 class="card-title text-primary"><i class="far fa-clock"></i> Time Until Stay Ends</h4>
                                <div id="countdown-timer" class="my-4">
                                    <div class="countdown-item">
                                        <span class="countdown-number" id="days">00</span>
                                        <span class="countdown-label">Days</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="countdown-number" id="hours">00</span>
                                        <span class="countdown-label">Hours</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="countdown-number" id="minutes">00</span>
                                        <span class="countdown-label">Minutes</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="countdown-number" id="seconds">00</span>
                                        <span class="countdown-label">Seconds</span>
                                    </div>
                                </div>
                                <div id="timer-actions">
                                    <p class="text-muted mb-3">Your stay expires on <strong><?php echo date('d M Y', $endDateTimestamp); ?></strong></p>
                                    <div class="btn-group">
                                        <a href="book-hostel.php" class="btn btn-success"><i class="fas fa-sync-alt"></i> Renew Stay</a>
                                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#reviewModal"><i class="fas fa-star"></i> Check Out & Rate</button>
                                    </div>
                                </div>
                                <div id="expired-message" style="display:none;">
                                    <h4 class="text-danger font-weight-bold">Your stay has expired!</h4>
                                    <p>Please renew your stay or check out immediately.</p>
                                    <a href="book-hostel.php" class="btn btn-primary">Book New Hostel</a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Recent Activity</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Action/IP</th>
                                                <th>Time</th>
                                                <th>Location</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if($logRes->num_rows > 0):
                                                while($log = $logRes->fetch_object()): 
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="font-weight-medium">Login</span>
                                                    <br/>
                                                    <span class="text-muted font-12"><?php echo $log->userIp; ?></span>
                                                </td>
                                                <td><?php echo date('d M Y h:i A', strtotime($log->loginTime)); ?></td>
                                                <td><?php echo htmlentities($log->city . ", " . $log->country); ?></td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            else:
                                            ?>
                                            <tr><td colspan="3" class="text-center text-muted">No recent activity found.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Current Booking Summary (if exists) -->
                        <?php if($booking): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <h4 class="card-title mb-0">Current Stay</h4>
                                    <div class="ml-auto">
                                        <a href="room-details.php" class="btn btn-sm btn-primary">Full Details</a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Hostel</label>
                                        <h5 class="text-dark font-weight-medium"><?php echo htmlentities($hostelName); ?></h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Room Number</label>
                                        <h5 class="text-dark font-weight-medium"><?php echo htmlentities($roomNo); ?></h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Booked On</label>
                                        <h5 class="text-dark font-weight-medium"><?php echo date('d M Y', strtotime($booking->postingDate)); ?></h5>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Duration</label>
                                        <h5 class="text-dark font-weight-medium"><?php echo $booking->duration; ?> Months</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

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
    
    <!-- Countdown Timer Script -->
    <?php if($showCountdown): ?>
    <script>
        // Set the date we're counting down to
        var countDownDate = <?php echo $endDateTimestamp * 1000; ?>;

        // Update the count down every 1 second
        var x = setInterval(function() {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the elements with id="demo"
            document.getElementById("days").innerHTML = (days < 10 ? "0" : "") + days;
            document.getElementById("hours").innerHTML = (hours < 10 ? "0" : "") + hours;
            document.getElementById("minutes").innerHTML = (minutes < 10 ? "0" : "") + minutes;
            document.getElementById("seconds").innerHTML = (seconds < 10 ? "0" : "") + seconds;

            // If the count down is finished, write some text
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown-timer").style.display = "none";
                document.getElementById("timer-actions").style.display = "none";
                document.getElementById("expired-message").style.display = "block";
            }
        }, 1000);
    </script>
    <?php endif; ?>
    <?php include '../includes/chatbot-widget.php'; ?>
    <?php $profileLink = 'profile.php'; include '../includes/profile-reminder-modal.php'; ?>
</body>
</html>
