<?php
session_start();
include('../includes/dbconn.php');
include('../includes/analytics-data.php');

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

$monthlyRevenue = getMonthlyRevenue($mysqli);
$occupancyStats = getOccupancyStats($mysqli);
$userGrowth = getUserGrowth($mysqli);

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Super Admin Dashboard - Hostel Management System</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.css" rel="stylesheet">
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
    
    <style>
        .quick-action-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .quick-action-card:hover {
            transform: translateY(-5px);
        }
        .ct-chart {
            height: 250px;
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
            <?php include 'includes/navigation.php'?>
        </header>

        <!-- Sidebar -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/super-sidebar.php'; ?>
            </div>
        </aside>

        <!-- Page Content -->
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <?php include 'includes/greetings-a.php'?>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                
                <!-- Quick Actions Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="card-title mb-3">Quick Actions</h4>
                    </div>
                    <div class="col-md-3">
                        <a href="manage-tenants.php" class="text-decoration-none">
                            <div class="card bg-brand-1 text-white quick-action-card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                                    <h6 class="text-white">Add Landlord</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="manage-approvals.php" class="text-decoration-none">
                            <div class="card bg-brand-2 text-white quick-action-card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h6 class="text-white">Verify Hostels</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="payments.php" class="text-decoration-none">
                            <div class="card bg-brand-3 text-white quick-action-card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                    <h6 class="text-white">System Payments</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="all-notifications.php" class="text-decoration-none">
                            <div class="card bg-brand-4 text-white quick-action-card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-bullhorn fa-2x mb-2"></i>
                                    <h6 class="text-white">Broadcast Alert</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-right text-center h-100">
                            <div class="card-body">
                                <h2 class="text-dark mb-1 font-weight-medium">
                                    <?php
                                    $sql = "SELECT count(*) as count FROM tenants";
                                    $result = $mysqli->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                    ?>
                                </h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Landlords</h6>
                                <div class="mt-2 text-primary">
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-right text-center h-100">
                            <div class="card-body">
                                <h2 class="text-warning mb-1 font-weight-medium">
                                    <?php
                                    $sql = "SELECT count(*) as count FROM bookings WHERE booking_status='pending'";
                                    $result = $mysqli->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                    ?>
                                </h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Pending Bookings</h6>
                                <div class="mt-2 text-warning">
                                    <i data-feather="clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-right text-center h-100">
                            <div class="card-body">
                                <h2 class="text-danger mb-1 font-weight-medium">
                                    <?php
                                    $sql = "SELECT count(*) as count FROM hostels WHERE status='pending'";
                                    $result = $mysqli->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                    ?>
                                </h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Hostel Requests</h6>
                                <div class="mt-2 text-danger">
                                    <i data-feather="alert-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h2 class="text-success mb-1 font-weight-medium">
                                    <?php
                                    $sql = "SELECT count(*) as count FROM bookings";
                                    $result = $mysqli->query($sql);
                                    $row = $result->fetch_assoc();
                                    echo $row['count'];
                                    ?>
                                </h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total Tenants</h6>
                                <div class="mt-2 text-success">
                                    <i data-feather="user-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Analytics Charts Row -->
                <div class="row mt-4">
                    <div class="col-lg-8 col-md-12">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">Global Revenue Overview (KSh)</h4>
                                <div id="revenue-chart" class="ct-chart mt-4"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">System-wide Occupancy</h4>
                                <div id="occupancy-donut" class="mt-4" style="height: 250px;"></div>
                                <div class="text-center mt-3">
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item"><i class="fas fa-circle text-primary font-10 mr-2"></i>Occupied</li>
                                        <li class="list-inline-item"><i class="fas fa-circle text-light font-10 mr-2"></i>Available</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Row -->
                 <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <h4 class="card-title">Recent System Bookings</h4>
                                    <div class="ml-auto">
                                         <a href="manage-clients.php" class="btn btn-primary btn-sm">View All</a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Client</th>
                                                <th>Hostel</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT r.*, h.name as hostel_name 
                                                    FROM bookings r
                                                    LEFT JOIN hostels h ON r.hostel_id = h.id 
                                                    ORDER BY r.id DESC LIMIT 5";
                                            $res = $mysqli->query($sql);
                                            while($row = $res->fetch_assoc()) {
                                                $sBadgeClass = 'badge-status-default';
                                                if($row['booking_status'] == 'confirmed') $sBadgeClass = 'badge-status-confirmed';
                                                if($row['booking_status'] == 'approved') $sBadgeClass = 'badge-status-approved';
                                                if($row['booking_status'] == 'pending') $sBadgeClass = 'badge-status-pending';
                                                if($row['booking_status'] == 'rejected') $sBadgeClass = 'badge-status-rejected';
                                                echo "<tr>";
                                                echo "<td>".htmlentities($row['firstName'] . ' ' . $row['lastName'])."</td>";
                                                echo "<td>".htmlentities($row['hostel_name'])."</td>";
                                                echo "<td><span class='badge $sBadgeClass px-2 py-1'>".ucfirst($row['booking_status'])."</span></td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <h4 class="card-title">Hostel Requests</h4>
                                    <div class="ml-auto">
                                         <a href="manage-approvals.php" class="btn btn-primary btn-sm">View All</a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Hostel</th>
                                                <th>Landlord</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT h.*, t.name as tenant_name 
                                                    FROM hostels h 
                                                    JOIN tenants t ON h.tenant_id = t.id 
                                                    ORDER BY h.created_at DESC LIMIT 5";
                                            $res = $mysqli->query($sql);
                                            while($row = $res->fetch_assoc()) {
                                                $statusBadge = ($row['status'] == 'pending') ? 'warning' : 'success';
                                                echo "<tr>";
                                                echo "<td>".htmlentities($row['name'])."</td>";
                                                echo "<td>".$row['tenant_name']."</td>";
                                                echo "<td><span class='badge badge-$statusBadge'>".ucfirst($row['status'])."</span></td>";
                                                echo "</tr>";
                                            }
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

    <!-- Scripts -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>

    <!-- Charts -->
    <script src="../assets/extra-libs/c3/d3.min.js"></script>
    <script src="../assets/extra-libs/c3/c3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.js"></script>
    
    <script>
    $(function () {
        // Revenue Bar Chart
        new Chartist.Bar('#revenue-chart', {
            labels: [<?php echo "'" . implode("','", array_column($monthlyRevenue, 'month')) . "'"; ?>],
            series: [[<?php echo implode(",", array_column($monthlyRevenue, 'total')); ?>]]
        }, {
            axisX: { showGrid: false },
            seriesBarDistance: 10,
            chartPadding: { top: 15, right: 15, bottom: 5, left: 0 }
        });

        // Occupancy Donut Chart
        c3.generate({
            bindto: '#occupancy-donut',
            data: {
                columns: [
                    ['Occupied', <?php echo $occupancyStats['occupied']; ?>],
                    ['Available', <?php echo $occupancyStats['available']; ?>]
                ],
                type: 'donut'
            },
            donut: {
                label: { show: false },
                title: "Occupancy",
                width: 25
            },
            legend: { hide: true },
            color: {
                pattern: ['#17c788', '#e9ecef']
            }
        });
    });
    </script>
</body>
</html>
