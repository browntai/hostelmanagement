<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    // Handle booking cancellation
    if(isset($_POST['cancel_booking'])){
        $booking_id = $_POST['booking_id'];
        $email = $_SESSION['login'];
        $tenantId = $_SESSION['tenant_id'];
        
        // Ensure the booking belongs to the logged-in client and is in a cancellable state
        $query = "UPDATE bookings SET booking_status='cancelled' WHERE id=? AND emailid=? AND tenant_id=? AND booking_status IN ('pending', 'confirmed')";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('isi', $booking_id, $email, $tenantId);
        if($stmt->execute() && $stmt->affected_rows > 0){
            $_SESSION['msg'] = "Booking cancelled successfully.";
        } else {
            $_SESSION['msg'] = "Unable to cancel booking. It may already be cancelled or processed.";
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
                    <div class="col-md-8">
                        <h2 class="page-title text-dark font-weight-black mb-1">Residential Portfolio</h2>
                        <p class="text-muted">An overview of your active lease agreements and unit allocations.</p>
                    </div>
                </div>

                <?php if(isset($_SESSION['msg'])): ?>
                <div class="alert badge-light-brand text-brand border-0 rounded-lg p-3 mb-4 d-flex align-items-center shadow-sm" role="alert">
                    <i data-feather="info" class="mr-3"></i>
                    <div class="font-weight-bold"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
                </div>
                <?php endif; ?>

                <?php	
                $aid=$_SESSION['login'];
                $ret="SELECT * from bookings where emailid=? ORDER BY postingDate DESC";
                $stmt= $mysqli->prepare($ret) ;
                $stmt->bind_param('s',$aid);
                $stmt->execute() ;
                $res=$stmt->get_result();
                $cnt=1;
                
                if($res->num_rows > 0):
                ?>
                <div class="row">
                    <?php
                    while($row=$res->fetch_object()):
                        $dr=$row->duration;
                        $fpm=$row->feespm;
                        $totalFees = $dr * $fpm;

                        $status = strtolower($row->booking_status);
                        // Status-specific badge classes for high-contrast visibility
                        $badgeClass = 'badge-status-default';
                        if ($status == 'confirmed') $badgeClass = 'badge-status-confirmed';
                        elseif ($status == 'approved') $badgeClass = 'badge-status-approved';
                        elseif ($status == 'pending') $badgeClass = 'badge-status-pending';
                        elseif ($status == 'cancelled') $badgeClass = 'badge-status-cancelled';
                        elseif ($status == 'rejected') $badgeClass = 'badge-status-rejected';
                    ?>
                    
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm border-0 h-100 rounded-lg overflow-hidden card-hover transition-all">
                            <div class="card-header bg-gradient-brand py-4 d-flex justify-content-between align-items-center border-0">
                                <h5 class="mb-0 text-white font-weight-black"><i data-feather="key" class="mr-2 text-white"></i>Unit #<?php echo $row->roomno;?></h5>
                                <span class="badge <?php echo $badgeClass; ?> px-3 py-2 rounded-pill small font-weight-black"><?php echo strtoupper($status); ?></span>
                            </div>
                            
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light-brand-subtle p-3 rounded mr-3 text-brand">
                                                <i data-feather="calendar" class="feather-sm"></i>
                                            </div>
                                            <div>
                                                <small class="text-brand font-weight-black text-uppercase letter-spacing-1 d-block" style="font-size: 0.65rem;">Registry Effective Date</small>
                                                <span class="text-dark font-weight-black"><?php echo date('M d, Y', strtotime($row->postingDate)); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-sm-6 mb-4">
                                        <div class="p-3 bg-light rounded-lg border-0 h-100">
                                            <h6 class="text-muted small text-uppercase font-weight-black mb-3 border-bottom pb-2 letter-spacing-1">Lease Logistics</h6>
                                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">Access Date:</span>
                                                <span class="text-dark font-weight-black small"><?php echo $row->stayfrom;?></span>
                                            </div>
                                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">Term:</span>
                                                <span class="text-dark font-weight-black small"><?php echo $row->duration;?> Months</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">Configuration:</span>
                                                <span class="text-dark font-weight-black small"><?php echo $row->seater;?> Seater</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-sm-6 mb-4">
                                        <div class="p-3 bg-light-brand-subtle rounded-lg border-brand h-100 shadow-sm">
                                            <h6 class="text-brand small text-uppercase font-weight-black mb-3 border-bottom border-brand pb-2 letter-spacing-1">Financial Quote</h6>
                                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">Monthly:</span>
                                                <span class="text-dark font-weight-black small">KSh <?php echo number_format($fpm, 0); ?></span>
                                            </div>
                                            <div class="pt-2 mt-2 border-top border-brand-subtle d-flex justify-content-between align-items-center">
                                                <span class="text-brand font-weight-bold small">Total Net:</span>
                                                <span class="text-brand font-weight-black h6 mb-0">KSh <?php echo number_format($totalFees, 0);?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                 <div class="bg-light p-3 rounded-lg border-0 mb-4">
                                    <div class="row align-items-center">
                                        <div class="col-12 mb-3">
                                            <h6 class="text-brand small text-uppercase font-weight-black mb-1 letter-spacing-1">Residential Entity</h6>
                                            <p class="mb-0 text-dark font-weight-black h6"><?php echo htmlentities($row->firstName . ' ' . $row->lastName); ?></p>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <i data-feather="phone" class="feather-xs text-muted mr-2"></i>
                                                <small class="text-muted font-weight-bold">Guardian Contact: <span class="text-dark font-weight-black ml-1"><?php echo htmlentities($row->guardianContactno); ?></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                                     <?php if(in_array($status, ['pending', 'confirmed', 'approved'])): ?>
                                        <a href="view-invoice.php?id=<?php echo $row->id; ?>" class="btn btn-brand rounded-pill px-4 shadow-sm font-weight-black mb-2 mr-2">
                                            <i data-feather="file-text" class="feather-sm mr-2"></i> Document Access
                                        </a>
                                        
                                        <?php if($status != 'cancelled' && $status != 'rejected'): ?>
                                        <form method="POST" onsubmit="return confirm('Initiate booking cancellation request?');" class="ml-sm-auto mb-2">
                                            <input type="hidden" name="booking_id" value="<?php echo $row->id; ?>">
                                            <button type="submit" name="cancel_booking" class="btn btn-light-danger rounded-pill px-4 font-weight-black transition-all">
                                                <i data-feather="slash" class="feather-sm mr-2"></i> Revoke Lease
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <?php else: ?>
                    <div class="row justify-content-center py-5">
                        <div class="col-lg-6 col-md-8 text-center">
                            <div class="bg-light-brand-subtle p-5 rounded-circle d-inline-block mb-4">
                                <i data-feather="layout" class="text-brand" style="width: 50px; height: 50px;"></i>
                            </div>
                            <h3 class="text-dark font-weight-black mb-3">Void Portfolio</h3>
                            <p class="text-muted mb-5 px-lg-5">You do not currently have any active or historical residential allocations in our ecosystem.</p>
                            <a href="book-hostel.php" class="btn btn-brand btn-lg px-5 rounded-pill shadow-lg font-weight-black py-3">
                                Secure First Slot <i data-feather="plus" class="ml-2"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            </div><!-- By Brown Tom -->
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
