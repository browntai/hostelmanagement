<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();
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
    <title>Hostel Management System</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">

    
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Full Details</h4>
                        <div class="d-flex align-items-center">
                            <!-- <nav aria-label="breadcrumb">
                                
                            </nav> -->
                        </div>
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

                <?php	
                    include_once('../includes/tenant_manager.php');
                    $tm = new TenantManager($mysqli);
                    $tenantId = $tm->getCurrentTenantId();

                    $id=$_GET['id'];
                    if($tm->isSuperAdmin() && !$tm->getCurrentTenantId()) {
                        $ret="SELECT * from bookings where id=?";
                        $stmt= $mysqli->prepare($ret) ;
                        $stmt->bind_param('i',$id);
                    } else {
                        $ret="SELECT * from bookings where id=? AND tenant_id=?";
                        $stmt= $mysqli->prepare($ret) ;
                        $stmt->bind_param('ii',$id, $tenantId);
                    }
                    $stmt->execute();
                    $res=$stmt->get_result();
                    while($row=$res->fetch_object())
                    {
                ?>

                <!-- Header Stats Card -->
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-primary text-white shadow-sm mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h3 class="text-white mb-0"><?php echo $row->firstName;?> <?php echo $row->middleName;?> <?php echo $row->lastName;?></h3>
                                        <p class="mb-0 text-white-50"><i data-feather="calendar" class="feather-sm"></i> Registered on: <?php echo date('M d, Y', strtotime($row->postingDate));?></p>
                                    </div>
                                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                        <span class="badge badge-light px-3 py-2 text-primary font-weight-bold">
                                            ROOM: <?php echo $row->roomno;?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-lg-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title d-flex align-items-center mb-4">
                                    <i data-feather="user" class="text-primary mr-2"></i> Personal Information
                                </h4>
                                <div class="row mb-3">
                                    <div class="col-sm-5 text-muted">Email Address</div>
                                    <div class="col-sm-7 font-weight-medium"><?php echo $row->emailid;?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5 text-muted">Contact Number</div>
                                    <div class="col-sm-7 font-weight-medium"><?php echo $row->contactno;?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5 text-muted">Gender</div>
                                    <div class="col-sm-7 font-weight-medium"><?php echo ucfirst($row->gender);?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5 text-muted">Room Type</div>
                                    <div class="col-sm-7 font-weight-medium"><?php echo $row->seater;?> Seater</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5 text-muted">Stay Started</div>
                                    <div class="col-sm-7 font-weight-medium"><?php echo date('M d, Y', strtotime($row->stayfrom));?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stay & Billing Details -->
                    <div class="col-lg-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title d-flex align-items-center mb-4">
                                    <i data-feather="credit-card" class="text-primary mr-2"></i> Billing & Duration
                                </h4>
                                <div class="row mb-3">
                                    <div class="col-sm-5 text-muted">Duration</div>
                                    <div class="col-sm-7 font-weight-medium"><?php echo $row->duration;?> Months</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5 text-muted">Food Status</div>
                                    <div class="col-sm-7">
                                        <?php if(isset($row->foodstatus) && $row->foodstatus==1): ?>
                                            <span class="badge badge-success">Required</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Not Required</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5 text-muted">Fees Per Month</div>
                                    <div class="col-sm-7 font-weight-medium">KSh <?php echo number_format($row->feespm);?></div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-5 text-dark font-weight-bold">Total Fees</div>
                                    <div class="col-sm-7 h4 text-primary font-weight-bold">
                                        <?php 
                                            $dr=$row->duration;
                                            $fpm=$row->feespm;
                                            if(isset($row->foodstatus) && $row->foodstatus==1){ 
                                                $fd=211; 
                                                echo 'KSh '.number_format(($fd+$fpm)*$dr);
                                            } else {
                                                echo 'KSh '.number_format($dr*$fpm);
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="col-lg-12 mt-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title d-flex align-items-center mb-4">
                                    <i data-feather="phone-call" class="text-danger mr-2"></i> Emergency Contact & Guardian
                                </h4>
                                <div class="row">
                                    <div class="col-md-6 border-right">
                                        <h5 class="font-weight-bold mb-3">Self Emergency</h5>
                                        <div class="row mb-2">
                                            <div class="col-sm-5 text-muted">Contact No.</div>
                                            <div class="col-sm-7 font-weight-medium"><?php echo $row->egycontactno;?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="font-weight-bold mb-3 pl-md-3">Guardian Info</h5>
                                        <div class="row mb-2 pl-md-3">
                                            <div class="col-sm-5 text-muted">Guardian Name</div>
                                            <div class="col-sm-7 font-weight-medium"><?php echo $row->guardianName;?> (<?php echo $row->guardianRelation;?>)</div>
                                        </div>
                                        <div class="row mb-2 pl-md-3">
                                            <div class="col-sm-5 text-muted">Guardian Contact</div>
                                            <div class="col-sm-7 font-weight-medium"><?php echo $row->guardianContactno;?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses -->
                    <div class="col-lg-12 mt-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h4 class="card-title d-flex align-items-center mb-4">
                                    <i data-feather="map-pin" class="text-success mr-2"></i> Address Details
                                </h4>
                                <div class="row">
                                    <div class="col-md-6 border-right">
                                        <h5 class="font-weight-bold mb-3">Current Address</h5>
                                        <p class="mb-1 text-dark"><?php echo $row->corresAddress;?></p>
                                        <p class="text-muted"><?php echo $row->corresCIty;?>, <?php echo $row->corresPincode;?><?php if(isset($row->corresState)): ?><br><?php echo $row->corresState;?><?php endif; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="font-weight-bold mb-3 pl-md-3">Permanent Address</h5>
                                        <p class="mb-1 text-dark pl-md-3"><?php echo $row->pmntAddress;?></p>
                                        <p class="text-muted pl-md-3"><?php echo $row->pmntCity;?>, <?php echo $row->pmntPincode;?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php } ?>

            </div>

            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <?php include 'includes/footer.php' ?>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- apps -->
    <!-- apps -->
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="../dist/js/custom.min.js"></script>
    <!--This page JavaScript -->
    <script src="../assets/extra-libs/c3/d3.min.js"></script>
    <script src="../assets/extra-libs/c3/c3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.js"></script>
    <script src="../dist/js/pages/chartist/chartist-plugin-tooltip-v2.min.js"></script>
    <script src="../dist/js/pages/dashboards/dashboard1.min.js"></script>

</body>

</html>
