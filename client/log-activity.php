<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    if(isset($_POST['delete_all'])){
        $uid = $_SESSION['id'];
        $query = "DELETE FROM userlog WHERE userId=?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $uid);
        if($stmt->execute()){
            echo "<script>alert('All logs deleted successfully');</script>";
            echo "<script>window.location.href='log-activity.php'</script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again');</script>";
        }
    }

?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<!-- By Brown Tom -->
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
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    
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
            
            <?php include '../includes/client-navigation.php'?>

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
                <?php include '../includes/client-sidebar.php'?>
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
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <div class="row mb-4 align-items-center">
                    <div class="col-md-7">
                        <h2 class="page-title text-dark font-weight-black mb-1">Security Audit Trail</h2>
                        <p class="text-muted">Review your recent system interactions and access points.</p>
                    </div>
                    <div class="col-md-5 text-md-right mt-3 mt-md-0">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete all activity logs? This action cannot be undone.');">
                            <button type="submit" name="delete_all" class="btn btn-light-danger px-4 rounded-pill font-weight-black shadow-sm">
                                <i data-feather="trash-2" class="feather-sm mr-2"></i> Purge History
                            </button>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
                            <div class="card-header bg-gradient-brand py-4 border-0 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-white font-weight-black"><i data-feather="list" class="mr-2 text-white"></i>Operation Pulse</h5>
                                <span class="badge badge-status-confirmed px-3 py-1 rounded-pill small font-weight-black">Real-time sync</span>
                            </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1" style="width: 80px;">ID</th>
                                            <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1">Network Entry</th>
                                            <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1">Session Identity</th>
                                            <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1">Geographic Origin</th>
                                            <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1 text-right">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php	
                                        $aid=$_SESSION['id'];
                                        $ret="SELECT * from userlog where userId=? ORDER BY loginTime DESC";
                                        $stmt= $mysqli->prepare($ret) ;
                                        $stmt->bind_param('i',$aid);
                                        $stmt->execute() ;
                                        $res=$stmt->get_result();
                                        $cnt=1;
                                        if($res->num_rows == 0):
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="bg-light p-4 rounded-circle d-inline-block mb-3">
                                                    <i data-feather="shield-off" class="text-muted" style="width: 40px; height: 40px;"></i>
                                                </div>
                                                <h4 class="text-dark font-weight-black">No Logs Found</h4>
                                                <p class="text-muted">Your activity history is currently clear.</p>
                                            </td>
                                        </tr>
                                    <?php
                                        else:
                                        while($row=$res->fetch_object()):
                                    ?>
                                        <tr>
                                            <td class="px-4 py-4">
                                                <span class="text-muted font-weight-black small"><?php echo str_pad($cnt, 2, '0', STR_PAD_LEFT); ?></span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light-brand-subtle p-2 rounded mr-3">
                                                        <i data-feather="cpu" class="text-brand feather-sm"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-dark font-weight-black d-block mb-0"><?php echo $row->userIp;?></span>
                                                        <small class="text-muted">v4/v6 Protocol</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span class="text-dark font-weight-medium small"><?php echo htmlentities($row->userEmail);?></span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="map-pin" class="text-danger mr-2 feather-xs"></i>
                                                    <span class="text-dark font-weight-black small"><?php echo htmlentities($row->city . ", " . $row->country);?></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="d-inline-block text-right">
                                                    <span class="text-dark font-weight-black d-block mb-0" style="font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($row->loginTime)); ?></span>
                                                    <small class="text-brand font-weight-bold"><?php echo date('h:i A', strtotime($row->loginTime)); ?></small>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                        $cnt=$cnt+1;
                                        endwhile;
                                        endif;
                                    ?>
									</tbody>
                                </table>
                            </div>
                        </div>
                    </div>


            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <?php include '../includes/footer.php' ?>
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
