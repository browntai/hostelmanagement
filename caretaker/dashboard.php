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

    // Fetch caretaker info and assigned hostel
    $stmt = $mysqli->prepare("SELECT u.full_name, u.assigned_hostel_id, h.name as hostel_name 
                              FROM users u 
                              LEFT JOIN hostels h ON u.assigned_hostel_id = h.id 
                              WHERE u.id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    
    if(!$userData) {
        // Fallback or session error
        header("location:../logout.php");
        exit();
    }

    $assignedHostelId = $userData['assigned_hostel_id'];
    $hostelName = $userData['hostel_name'] ?? 'No Hostel Assigned';

    // Stats for the specific hostel
    $totalRooms = 0;
    $occupiedRooms = 0;
    $pendingBookings = 0;

    if($assignedHostelId) {
        // Total Rooms
        $res = $mysqli->query("SELECT COUNT(*) as count FROM rooms WHERE hostel_id = $assignedHostelId");
        $totalRooms = $res->fetch_assoc()['count'];

        // Occupied Rooms
        $res = $mysqli->query("SELECT COUNT(DISTINCT roomno) as count FROM bookings WHERE hostel_id = $assignedHostelId AND booking_status = 'approved'");
        $occupiedRooms = $res->fetch_assoc()['count'];

        // Pending Bookings
        $res = $mysqli->query("SELECT COUNT(*) as count FROM bookings WHERE hostel_id = $assignedHostelId AND booking_status = 'pending'");
        $pendingBookings = $res->fetch_assoc()['count'];

        // Check if daycare is active for assigned hostel
        $caretaker_daycare_enabled = false;
        $check_q = "SELECT is_enabled FROM hostel_services WHERE hostel_id = ? AND service_key = 'daycare'";
        $c_stmt = $mysqli->prepare($check_q);
        $c_stmt->bind_param('i', $assignedHostelId);
        $c_stmt->execute();
        $c_res = $c_stmt->get_result();
        if($c_row = $c_res->fetch_object()){
            $caretaker_daycare_enabled = (bool)$c_row->is_enabled;
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Caretaker Dashboard - <?php echo htmlspecialchars($hostelName); ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
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
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Welcome, <?php echo htmlspecialchars(explode(' ', $userData['full_name'])[0]); ?>!</h3>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted">Caretaker Portal</a></li>
                                    <li class="breadcrumb-item active text-muted" aria-current="page"><?php echo htmlspecialchars($hostelName); ?></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <?php if(!$assignedHostelId): ?>
                    <div class="alert alert-warning border-0 shadow-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Account Not Fully Setup:</strong> You have not been assigned to a hostel yet. Please contact your landlord to assign you to a property.
                    </div>
                <?php else: ?>
                    <div class="row statistics-cards">
                        <!-- Total Rooms -->
                        <div class="col-sm-6 col-lg-4">
                            <div class="card pub-card shadow-md border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h2 class="text-primary mb-1 font-weight-ExtraBold" style="color: var(--theme-primary-color) !important;"><?php echo $totalRooms; ?></h2>
                                            <h6 class="text-dark font-weight-bold mb-0">Total Rooms</h6>
                                        </div>
                                        <div class="ml-auto">
                                            <div class="pub-card-icon shadow-sm mb-0">
                                                <i data-feather="grid" class="feather-sm"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Occupied -->
                        <div class="col-sm-6 col-lg-4">
                            <div class="card pub-card shadow-md border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h2 class="text-success mb-1 font-weight-ExtraBold" style="color: #28a745 !important;"><?php echo $occupiedRooms; ?></h2>
                                            <h6 class="text-dark font-weight-bold mb-0">Occupied Rooms</h6>
                                        </div>
                                        <div class="ml-auto">
                                            <div class="pub-card-icon shadow-sm mb-0 bg-success">
                                                <i data-feather="user-check" class="feather-sm"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pending -->
                        <div class="col-sm-6 col-lg-4">
                            <div class="card pub-card shadow-md border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h2 class="text-warning mb-1 font-weight-ExtraBold" style="color: #ffc107 !important;"><?php echo $pendingBookings; ?></h2>
                                            <h6 class="text-dark font-weight-bold mb-0">Pending Bookings</h6>
                                        </div>
                                        <div class="ml-auto">
                                            <div class="pub-card-icon shadow-sm mb-0 bg-warning">
                                                <i data-feather="clock" class="feather-sm"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Daycare Duties -->
                    <?php if($caretaker_daycare_enabled): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card pub-card shadow-md border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4">
                                        <h4 class="card-title text-dark font-weight-bold mb-0">My Daycare Duties (Today)</h4>
                                        <div class="ml-auto">
                                            <a href="daycare-attendance.php" class="btn btn-pub-outline btn-sm">Manage Attendance</a>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover no-wrap">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Child Name</th>
                                                    <th>Age</th>
                                                    <th>Time Slot</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $today = date('Y-m-d');
                                                    $d_query = "SELECT * FROM daycare_bookings 
                                                                WHERE assigned_caretaker_id = ? AND booking_date = ? 
                                                                ORDER BY created_at ASC";
                                                    $d_stmt = $mysqli->prepare($d_query);
                                                    $d_stmt->bind_param('is', $userId, $today);
                                                    $d_stmt->execute();
                                                    $d_res = $d_stmt->get_result();
                                                    
                                                    if($d_res->num_rows > 0) {
                                                        while($row = $d_res->fetch_object()) {
                                                            $statusColor = 'warning';
                                                            if($row->status == 'approved') $statusColor = 'info';
                                                            if($row->status == 'checked_in') $statusColor = 'primary';
                                                            if($row->status == 'checked_out') $statusColor = 'success';
                                                            
                                                            echo "<tr>
                                                                    <td class='font-weight-medium text-dark'>" . htmlspecialchars($row->child_name) . "</td>
                                                                    <td>{$row->child_age} yrs</td>
                                                                    <td>" . htmlspecialchars($row->time_slot) . "</td>
                                                                    <td><span class='badge badge-{$statusColor}'>" . ucfirst(str_replace('_', ' ', $row->status)) . "</span></td>
                                                                    <td>
                                                                        <a href='daycare-attendance.php?id={$row->id}' class='btn btn-xs btn-primary'>View</a>
                                                                    </td>
                                                                  </tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No daycare assignments for today.</td></tr>";
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Recent Bookings for this Hostel -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card pub-card shadow-md border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4">
                                        <h4 class="card-title text-dark font-weight-bold mb-0">Recent Bookings (<?php echo htmlspecialchars($hostelName); ?>)</h4>
                                        <div class="ml-auto">
                                            <a href="manage-bookings.php" class="btn btn-pub-outline btn-sm">View All</a>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover no-wrap">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Tenant Name</th>
                                                    <th>Room No</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $b_query = "SELECT * FROM bookings 
                                                                WHERE hostel_id = ? 
                                                                ORDER BY postingDate DESC LIMIT 5";
                                                    $b_stmt = $mysqli->prepare($b_query);
                                                    $b_stmt->bind_param('i', $assignedHostelId);
                                                    $b_stmt->execute();
                                                    $b_res = $b_stmt->get_result();
                                                    $cnt = 1;
                                                    if($b_res->num_rows > 0) {
                                                        while($row = $b_res->fetch_object()) {
                                                            $statusClass = ($row->booking_status == 'approved' || $row->booking_status == 'confirmed') ? 'success' : ($row->booking_status == 'pending' ? 'warning' : 'danger');
                                                            $fullName = trim($row->firstName . ' ' . $row->middleName . ' ' . $row->lastName);
                                                            echo "<tr>
                                                                    <td>{$cnt}</td>
                                                                    <td class='font-weight-medium text-dark'>" . htmlspecialchars($fullName) . "</td>
                                                                    <td><span class='badge badge-light-secondary px-2'>Room #{$row->roomno}</span></td>
                                                                    <td>" . date('M d, Y', strtotime($row->postingDate)) . "</td>
                                                                    <td><span class='badge badge-{$statusClass}'>" . ucfirst(htmlspecialchars($row->booking_status)) . "</span></td>
                                                                  </tr>";
                                                            $cnt++;
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No recent bookings found.</td></tr>";
                                                    }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
</body>
</html>
