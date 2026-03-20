<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    include('../includes/analytics-data.php');
    check_login();

    $tenantId = $_SESSION['tenant_id'];
    $monthlyRevenue = getMonthlyRevenue($mysqli, $tenantId);
    $occupancyStats = getOccupancyStats($mysqli, $tenantId);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Landlord Dashboard - Hostel Management System</title>
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
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/sidebar.php'?>
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                       <?php include 'includes/greetings.php'?>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                
                <!-- Quick Actions Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="card-title mb-3">Quick Actions</h4>
                    </div>
                    <div class="col-md-3">
                        <a href="add-hostel.php" class="text-decoration-none">
                            <div class="card bg-primary text-white quick-action-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                    <h6>Add Hostel</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="add-rooms.php" class="text-decoration-none">
                            <div class="card bg-info text-white quick-action-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-bed fa-2x mb-2"></i>
                                    <h6>Add Room</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="bookings.php" class="text-decoration-none">
                            <div class="card bg-success text-white quick-action-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-list-alt fa-2x mb-2"></i>
                                    <h6>View Bookings</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="payments.php" class="text-decoration-none">
                            <div class="card bg-warning text-white quick-action-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-check-alt fa-2x mb-2"></i>
                                    <h6>Payments</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- *************************************************************** -->
                <!-- Start First Cards -->
                <!-- *************************************************************** -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-right text-center h-100">
                            <div class="card-body">
                                <h2 class="text-dark mb-1 font-weight-medium" id="dash-total-clients"><?php include 'counters/client-count.php'?></h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">My Tenants</h6>
                                <div class="mt-2">
                                    <span class="opacity-7 text-primary"><i data-feather="user-plus"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-right text-center h-100">
                            <div class="card-body">
                                <h2 class="text-dark mb-1 w-100 text-truncate font-weight-medium" id="dash-total-rooms"><?php include 'counters/room-count.php'?></h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Total Rooms</h6>
                                <div class="mt-2">
                                    <span class="opacity-7 text-success"><i data-feather="grid"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-right text-center h-100">
                            <div class="card-body">
                                <h2 class="text-dark mb-1 font-weight-medium" id="dash-occupied-rooms"><?php include 'counters/booked-count.php'?></h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Occupied Rooms</h6>
                                <div class="mt-2">
                                    <span class="opacity-7 text-danger"><i data-feather="book-open"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <h2 class="text-dark mb-1 font-weight-medium"><?php include 'counters/wishlist-count.php'?></h2>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Wishlist</h6>
                                <div class="mt-2">
                                    <span class="opacity-7 text-warning"><i class="fas fa-heart"></i></span>
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
                                <h4 class="card-title">Earnings Overview (KSh)</h4>
                                <div id="revenue-chart" class="ct-chart mt-4"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="card-title">Room Occupancy</h4>
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

                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Recent Tenant Activity</h4>
                                <div class="table-responsive">
                                    <table id="zero_config" class="table table-striped table-bordered no-wrap">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Client's Email</th>
                                                <th scope="col">Last Activity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php	
                                            $aid=$_SESSION['id'];
                                            include_once '../includes/tenant_manager.php';
                                            $tm = new TenantManager($mysqli);
                                            $limit = $tm->getTenantWhereClause(true);
                                            
                                            $ret="SELECT * from userlog $limit ORDER BY loginTime DESC LIMIT 10";
                                            $stmt= $mysqli->prepare($ret) ;
                                            $stmt->execute() ;
                                            $res=$stmt->get_result();
                                            $cnt=1;
                                            while($row=$res->fetch_object()) {
                                        ?>
                                            <tr>
                                                <td><?php echo $cnt;?></td>
                                                <td><?php echo $row->userEmail;?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($row->loginTime));?></td>
                                            </tr>
                                        <?php
                                            $cnt=$cnt+1;
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden containers to prevent errors from original dashboard1.js if it looks for them -->
                <div style="display:none;">
                    <div id="admin-campaign-v2"></div>
                    <div id="admin-net-income" class="net-income"></div>
                    <div id="admin-stats" class="stats"></div>
                    <div id="admin-visitbylocate"></div>
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
    
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>
</body>
</html>
