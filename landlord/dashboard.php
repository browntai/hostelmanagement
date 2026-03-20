<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    include('../includes/analytics-data.php');
    include('../includes/ai-helper.php');
    check_login();

    $tenantId = $_SESSION['tenant_id'];
    $monthlyRevenue = getMonthlyRevenue($mysqli, $tenantId);
    $occupancyStats = getOccupancyStats($mysqli, $tenantId);
    $daycare_stats = getDaycareStats($mysqli, $tenantId);

    // Check if daycare is active for ANY property
    $any_daycare_enabled = false;
    $check_query = "SELECT COUNT(*) as cnt FROM hostel_services WHERE is_enabled=1 AND service_key='daycare' AND hostel_id IN (SELECT id FROM hostels WHERE tenant_id=?)";
    $c_stmt = $mysqli->prepare($check_query);
    $c_stmt->bind_param('i', $tenantId);
    $c_stmt->execute();
    $c_res = $c_stmt->get_result();
    if($c_row = $c_res->fetch_object()){
        $any_daycare_enabled = ($c_row->cnt > 0);
    }
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
                
                <!-- Property Control Center (Quick Actions) -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="card-title text-dark font-weight-bold mb-3">Property Control Center</h4>
                    </div>
                    <div class="col-md-3">
                        <a href="add-hostel.php" class="text-decoration-none">
                            <div class="card bg-brand-1 text-white quick-action-card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                    <h6 class="font-weight-bold text-white">Register Hostel</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="add-rooms.php" class="text-decoration-none">
                            <div class="card bg-brand-2 text-white quick-action-card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <h6 class="font-weight-bold text-white">Manage Rooms</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="bookings.php" class="text-decoration-none">
                            <div class="card bg-brand-3 text-white quick-action-card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                                    <h6 class="font-weight-bold text-white">Booking Requests</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="payments.php" class="text-decoration-none">
                            <div class="card bg-brand-4 text-white quick-action-card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <h6 class="font-weight-bold text-white">Revenue Feed</h6>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="row">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h2 class="text-primary mb-1 font-weight-medium"><?php include 'counters/client-count.php'?></h2>
                                        <h6 class="text-dark font-weight-bold mb-0 w-100 text-truncate">Active Tenants</h6>
                                    </div>
                                    <div class="ml-auto mt-md-3 mt-lg-0">
                                        <div class="stat-icon bg-light-primary text-primary">
                                            <i data-feather="users" class="feather-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h2 class="text-info mb-1 font-weight-medium"><?php include 'counters/room-count.php'?></h2>
                                        <h6 class="text-dark font-weight-bold mb-0 w-100 text-truncate">Total Capacity</h6>
                                    </div>
                                    <div class="ml-auto mt-md-3 mt-lg-0">
                                        <div class="stat-icon bg-light-info text-info">
                                            <i data-feather="grid" class="feather-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h2 class="text-success mb-1 font-weight-medium"><?php include 'counters/booked-count.php'?></h2>
                                        <h6 class="text-dark font-weight-bold mb-0 w-100 text-truncate">Current Occupancy</h6>
                                    </div>
                                    <div class="ml-auto mt-md-3 mt-lg-0">
                                        <div class="stat-icon bg-light-success text-success">
                                            <i data-feather="home" class="feather-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <h2 class="text-warning mb-1 font-weight-medium"><?php include 'counters/wishlist-count.php'?></h2>
                                        <h6 class="text-dark font-weight-bold mb-0 w-100 text-truncate">User Interest</h6>
                                    </div>
                                    <div class="ml-auto mt-md-3 mt-lg-0">
                                        <div class="stat-icon bg-light-warning text-warning">
                                            <i data-feather="heart" class="feather-sm"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daycare Operations Section -->
                <?php if($any_daycare_enabled): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm overflow-hidden">
                            <div class="card-header bg-white border-0 py-3">
                                <h4 class="card-title text-dark font-weight-bold mb-0">
                                    <i class="fas fa-child text-primary mr-2"></i> Daycare Operations
                                </h4>
                            </div>
                            <div class="card-body bg-light-extra">
                                <div class="row text-center">
                                    <div class="col-md-3 border-right">
                                        <div class="p-2">
                                            <h3 class="mb-0 font-weight-bold text-primary"><?php echo $daycare_stats['pending']; ?></h3>
                                            <small class="text-muted font-weight-bold uppercase">Pending Requests</small>
                                            <div class="mt-2 text-center">
                                                <a href="manage-services.php" class="btn btn-xs btn-outline-primary">Process</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 border-right">
                                        <div class="p-2">
                                            <h3 class="mb-0 font-weight-bold text-success"><?php echo $daycare_stats['today_checked_in']; ?></h3>
                                            <small class="text-muted font-weight-bold uppercase">Active Check-ins</small>
                                            <div class="mt-2 text-center">
                                                <a href="daycare-attendance.php" class="btn btn-xs btn-outline-success">View Board</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 border-right">
                                        <div class="p-2">
                                            <h3 class="mb-0 font-weight-bold text-info"><?php echo $daycare_stats['total_children']; ?></h3>
                                            <small class="text-muted font-weight-bold uppercase">Registered Children</small>
                                            <div class="mt-2 text-center">
                                                <a href="all-children.php" class="btn btn-xs btn-outline-info">Directory</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-2">
                                            <div class="stat-icon bg-primary text-white mx-auto mb-2" style="width: 40px; height: 40px; line-height: 40px; border-radius: 50%;">
                                                <i class="fas fa-plus"></i>
                                            </div>
                                            <small class="text-muted font-weight-bold uppercase">Need Help?</small>
                                            <div class="mt-2 text-center">
                                                <a href="mailto:support@hostel.com" class="btn btn-xs btn-link">Contact Support</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Analytics -->
                <div class="row mt-4">
                    <div class="col-lg-8 col-md-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h4 class="card-title text-dark font-weight-bold">Earnings Performance</h4>
                                <div id="revenue-chart" class="ct-chart mt-4"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h4 class="card-title text-dark font-weight-bold">Occupancy Real-time</h4>
                                <div id="occupancy-donut" class="mt-4" style="height: 250px;"></div>
                                <div class="text-center mt-3">
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item text-dark font-weight-bold"><i class="fas fa-circle text-primary font-10 mr-2"></i>Occupied</li>
                                        <li class="list-inline-item text-muted font-weight-bold"><i class="fas fa-circle text-secondary font-10 mr-2"></i>Available</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI-Powered Insights -->
                <?php
                    $aiInsights = generateFinancialSummary($mysqli, $tenantId);
                    $predictions = getPredictiveInsights($mysqli, $tenantId);
                    $fraudFlags = detectPaymentAnomalies($mysqli, $tenantId);
                ?>
                <div class="row mt-4">
                    <div class="col-lg-6 col-md-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h4 class="card-title text-dark font-weight-bold"><i class="fas fa-robot text-primary mr-2"></i>AI Smart Summary</h4>
                                <p class="text-muted small mb-3">Auto-generated insights based on your portfolio data</p>
                                <?php foreach($aiInsights as $insight): ?>
                                <div class="alert alert-<?php echo $insight['type']; ?> py-2 px-3 mb-2 border-0" style="font-size:13px;">
                                    <strong><?php echo $insight['title']; ?>:</strong> <?php echo $insight['text']; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h4 class="card-title text-dark font-weight-bold"><i class="fas fa-brain text-info mr-2"></i>Predictive Alerts</h4>
                                <p class="text-muted small mb-3">AI-detected patterns and fraud signals</p>
                                <?php if(!empty($predictions)): foreach($predictions as $p): ?>
                                <div class="d-flex align-items-start mb-3 p-2 bg-light rounded">
                                    <span class="badge badge-<?php echo $p['severity']=='High'?'danger':($p['severity']=='Medium'?'warning':'info'); ?> mr-2 mt-1"><?php echo $p['severity']; ?></span>
                                    <div>
                                        <strong class="small"><?php echo $p['type']; ?></strong>
                                        <p class="mb-0 small text-muted"><?php echo $p['message']; ?></p>
                                        <small class="text-primary"><i class="fas fa-arrow-right"></i> <?php echo $p['action']; ?></small>
                                    </div>
                                </div>
                                <?php endforeach; endif; ?>
                                <?php if(!empty($fraudFlags)): ?>
                                <hr>
                                <h6 class="text-danger"><i class="fas fa-shield-alt mr-1"></i>Fraud Detection</h6>
                                <?php foreach($fraudFlags as $f): ?>
                                <div class="d-flex align-items-center mb-2 small">
                                    <span class="mr-2"><?php echo $f['icon']; ?></span>
                                    <div>
                                        <strong><?php echo $f['type']; ?></strong>: <?php echo $f['detail']; ?>
                                    </div>
                                </div>
                                <?php endforeach; endif; ?>
                                <?php if(empty($predictions) && empty($fraudFlags)): ?>
                                <div class="text-center py-3 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                    <p>No anomalies detected. Everything looks good!</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h4 class="card-title text-dark font-weight-bold mb-4">Recent Tenant Activity</h4>
                                <div class="table-responsive">
                                    <table id="zero_config" class="table table-hover no-wrap">
                                        <thead class="bg-light-brand-subtle">
                                            <tr class="text-brand">
                                                <th class="font-weight-bold">#</th>
                                                <th class="font-weight-bold">Tenant's Email</th>
                                                <th class="font-weight-bold">Time of Activity</th>
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
                                                <td class="text-muted"><?php echo $cnt;?></td>
                                                <td class="text-dark font-weight-medium"><?php echo $row->userEmail;?></td>
                                                <td class="text-muted"><i data-feather="clock" class="feather-xs mr-1"></i> <?php echo date('M d, Y H:i', strtotime($row->loginTime));?></td>
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
        }).on('draw', function(data) {
            if(data.type === 'bar') {
                data.element.attr({
                    style: 'stroke-width: 25px; stroke: #17c788; stroke-linecap: round'
                });
            }
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
