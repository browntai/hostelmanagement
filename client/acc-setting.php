<?php
    session_start();
    include('../includes/dbconn.php');
    date_default_timezone_set('America/Chicago');
    include('../includes/check-login.php');
    check_login();
    $ai=$_SESSION['id'];
    // code for change password
    if(isset($_POST['changepwd'])){
    $op=$_POST['oldpassword'];
    $op=md5($op);
    $np=$_POST['newpassword'];
    $np=md5($np);
    $udate=date('d-m-Y h:i:s', time());;
        $sql="SELECT password FROM users where password=? AND id=?";
        $chngpwd = $mysqli->prepare($sql);
        $chngpwd->bind_param('si',$op,$ai);
        $chngpwd->execute();
        $chngpwd->store_result(); 
        $row_cnt=$chngpwd->num_rows;;
        if($row_cnt>0){
            $con="update users set password=? where id=?";
            $chngpwd1 = $mysqli->prepare($con);
            $chngpwd1->bind_param('si',$np,$ai);
            $chngpwd1->execute();
            $_SESSION['msg']="Password has been updated !!";
        } else {
            $_SESSION['msg']="Old Password does not match !!";
        }	

    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
<!-- By Brown Tom -->
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
    <link href="../assets/css/public-pages.css" rel="stylesheet">

    <script type="text/javascript">
    function valid(){
    if(document.changepwd.newpassword.value!= document.changepwd.cpassword.value){
        alert("New Password and Confirmation Password does not match");
        document.changepwd.cpassword.focus();
        return false;
     }
        return true;
    }
    </script>
    
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
        <!-- By Brown Tom -->
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
                
                <div class="row mb-4">
                    <div class="col-12 text-center text-lg-left">
                        <h2 class="page-title text-dark font-weight-bold mb-1">Security Credentials</h2>
                        <p class="text-muted">Maintain the integrity of your residential portal access.</p>
                    </div>
                </div>

                <div class="row">
                    <?php $result ="SELECT updated_at FROM users WHERE id=?";
                    $stmt = $mysqli->prepare($result);
                    $stmt->bind_param('i',$ai);
                    $stmt->execute();
                    $stmt -> bind_result($last_updated);
                    $stmt -> fetch(); 
                    $stmt->close();
                    ?>

                    <div class="col-lg-8 mx-auto">
                        <div class="card shadow-sm border-0 rounded-lg overflow-hidden card-hover transition-3d">
                            <div class="bg-gradient-brand py-5 text-center px-4">
                                <div class="d-flex justify-content-between align-items-center text-white">
                                    <h5 class="mb-0 text-white font-weight-black"><i data-feather="lock" class="mr-2"></i>Change Master Password</h5>
                                    <small class="opacity-7 font-weight-bold">Last Sync: <?php echo $last_updated ? date('M d, Y', strtotime($last_updated)) : 'Never'; ?></small>
                                </div>
                            </div>
                            
                            <div class="card-body p-5">
                                <?php if(isset($_SESSION['msg'])): ?>
                                <div class="alert badge-light-brand text-brand border-0 rounded-lg p-3 mb-4 d-flex align-items-center" role="alert">
                                    <i data-feather="info" class="mr-3"></i>
                                    <div class="font-weight-bold"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
                                </div>
                                <?php endif; ?>

                                <form method="POST" name="changepwd" id="change-pwd" onSubmit="return valid();">
                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-black mb-2 d-block letter-spacing-1">Current Password</label>
                                            <div class="input-group input-group-lg">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text border-0 bg-light px-3"><i data-feather="key" class="feather-sm text-muted"></i></span>
                                                </div>
                                                <input type="password" name="oldpassword" id="oldpassword" class="form-control border-0 bg-light font-weight-medium" placeholder="Current encryption key" onBlur="checkpass()" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-light border-0 px-3 toggle-password" type="button">
                                                        <i data-feather="eye" class="feather-sm"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-header bg-white py-4 border-bottom d-flex align-items-center">
                                            <div class="bg-light-brand p-2 rounded mr-3">
                                                <i data-feather="shield" class="text-brand"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 text-dark font-weight-black">Security Protocols</h5>
                                                <small class="text-muted">Ensure your authentication credentials are updated periodically.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-black mb-2 d-block letter-spacing-1">New Password</label>
                                            <div class="input-group input-group-lg">
                                                <input type="password" name="newpassword" id="newpassword" class="form-control border-0 bg-light font-weight-medium rounded-lg" placeholder="Complexity required" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-black mb-2 d-block letter-spacing-1">Verify Password</label>
                                            <div class="input-group input-group-lg">
                                                <input type="password" name="cpassword" id="cpassword" class="form-control border-0 bg-light font-weight-medium rounded-lg" placeholder="Repeat new key" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-4">
                                        <button type="submit" name="changepwd" class="btn btn-brand btn-lg btn-block rounded-pill shadow-lg font-weight-black py-3">
                                            Update Credentials <i data-feather="shield" class="ml-2"></i>
                                        </button>
                                        <button type="reset" class="btn btn-block btn-link text-muted font-weight-bold mt-2">Clear Inputs</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="mt-4 bg-white p-4 rounded-lg shadow-sm">
                            <h6 class="text-dark font-weight-black mb-3 small text-uppercase letter-spacing-1">Security Best Practices</h6>
                            <ul class="list-unstyled mb-0 small text-muted">
                                <li class="mb-2 d-flex align-items-start"><i data-feather="check-circle" class="feather-xs text-brand mt-1 mr-2"></i> Use a mix of alphanumeric characters and symbols.</li>
                                <li class="mb-2 d-flex align-items-start"><i data-feather="check-circle" class="feather-xs text-brand mt-1 mr-2"></i> Avoid using common names or dictionary words.</li>
                                <li class="d-flex align-items-start"><i data-feather="check-circle" class="feather-xs text-brand mt-1 mr-2"></i> Change your password every 90 days for maximum safety.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                </form>

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

    <!-- customs -->
    <!-- customs -->
    <script src="../dist/js/show-password.js"></script>
    <script>
    function checkpass() {
        $("#loaderIcon").show();
        jQuery.ajax({
        url: "check-availability.php",
        data:'oldpassword='+$("#oldpassword").val(),
        type: "POST",
        success:function(data){
            $("#password-availability-status").html(data);
            $("#loaderIcon").hide();
            },
            error:function (){}
        });
    }
    </script>
</body>

</html>
