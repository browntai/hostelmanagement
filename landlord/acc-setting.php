<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    if(isset($_POST['changepwd'])){
        $op=$_POST['oldpassword'];
        $op=md5($op); // Consistency with login.php
        $np=$_POST['newpassword'];
        $np=md5($np); // Consistency with login.php
        $ai=$_SESSION['id'];
        $udate=date('Y-m-d H:i:s');
        
        // Check in admin table first (legacy)
        $sql="SELECT password FROM admin where id=?";
        $chngpwd = $mysqli->prepare($sql);
        $chngpwd->bind_param('i',$ai);
        $chngpwd->execute();
        $res = $chngpwd->get_result();
        $row = $res->fetch_object();
        
        // If not in admin, check users table
        if(!$row) {
            $sql="SELECT password FROM users where id=?";
            $chngpwd = $mysqli->prepare($sql);
            $chngpwd->bind_param('i',$ai);
            $chngpwd->execute();
            $res = $chngpwd->get_result();
            $row = $res->fetch_object();
        }

        if($row && $row->password == $op) {
            // Update admin table if it exists for this user
            $con="update admin set password=?,updation_date=? where id=?";
            $chngpwd1 = $mysqli->prepare($con);
            $chngpwd1->bind_param('ssi',$np,$udate,$ai);
            $chngpwd1->execute();
            
            // Update users table
            $con2="update users set password=? where id=?";
            $chngpwd2 = $mysqli->prepare($con2);
            $chngpwd2->bind_param('si',$np,$ai);
            $chngpwd2->execute();
            
            $_SESSION['msg']="Password has been successfully changed";
        } else {
            $_SESSION['msg']="Current Password does not match";
        }	
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
    <title>Hostel Management System</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.css" rel="stylesheet">
     <!-- This page plugin CSS -->
     <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Change Password</h4>
                        
                        
                        <?php if(isset($_POST['changepwd']))
                            { ?>
                                <div class="alert alert-secondary alert-dismissible bg-secondary text-white border-0 fade show"
                                    role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <strong>Info - </strong> <?php echo htmlentities($_SESSION['msg']); ?> <?php echo htmlentities($_SESSION['msg']=""); ?>
                                </div>
						<?php } ?>

                            
                        
                        
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

                <form method="POST">
                    <div class="row">
                        <?php	
                        $aid=$_SESSION['id'];
                        // For Landlords, we primarily use the users table now, but check admin as fallback
                        $ret="SELECT * from users where id=?";
                        $stmt= $mysqli->prepare($ret) ;
                        $stmt->bind_param('i',$aid);
                        $stmt->execute();
                        $res=$stmt->get_result();
                        
                        if($res->num_rows == 0) {
                            $ret="SELECT * from admin where id=?";
                            $stmt= $mysqli->prepare($ret) ;
                            $stmt->bind_param('i',$aid);
                            $stmt->execute();
                            $res=$stmt->get_result();
                        }

                        while($row=$res->fetch_object()) {
                            // Map fields if coming from admin table
                            if(!isset($row->full_name)) $row->full_name = isset($row->username) ? $row->username : 'Landlord';
                        ?>
                        <div class="col-lg-4 col-md-5">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <?php 
                                        $pic = (isset($row->profile_pic) && $row->profile_pic) ? "../uploads/profiles/" . $row->profile_pic : "../assets/images/users/admin-icn.png";
                                        ?>
                                        <div class="position-relative d-inline-block">
                                            <img src="<?php echo $pic; ?>" alt="user" class="rounded-circle shadow-sm" width="120" height="120" style="object-fit: cover; border: 3px solid #fff;">
                                        </div>
                                    </div>
                                    <h4 class="font-weight-bold text-dark mt-2"><?php echo htmlentities($row->full_name); ?></h4>
                                    <p class="text-muted small">Property Owner / Landlord</p>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="bg-light p-3 rounded text-left mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i data-feather="clock" class="text-primary mr-2 feather-sm"></i>
                                            <small class="text-muted">Last Password Update:</small>
                                        </div>
                                        <p class="mb-0 font-weight-medium small"><?php echo (isset($row->updation_date) && $row->updation_date) ? date('M d, Y • H:i', strtotime($row->updation_date)) : 'Never'; ?></p>
                                    </div>

                                    <div class="alert alert-info border-0 small text-left mb-0">
                                        <i data-feather="shield" class="feather-sm mr-1"></i> Keep your credentials secure to protect your property data.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 col-md-7">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-gradient-primary py-3">
                                    <h5 class="mb-0 text-white"><i data-feather="lock" class="feather-sm mr-2"></i>Security Credentials</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <label class="text-muted font-weight-medium small text-uppercase mb-2 d-block">Current Password</label>
                                        <div class="input-group shadow-none">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i data-feather="key" class="feather-sm text-muted"></i></span>
                                            </div>
                                            <input type="password" name="oldpassword" id="oldpassword" class="form-control border-left-0" onBlur="checkpass()" required placeholder="Enter current password">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-white toggle-password" style="cursor: pointer;">
                                                    <i data-feather="eye" class="feather-sm"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <span id="password-availability-status" class="small mt-1 d-block"></span>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="text-muted font-weight-medium small text-uppercase mb-2 d-block">New Password</label>
                                                <div class="input-group shadow-none">
                                                    <input type="password" class="form-control" name="newpassword" id="newpassword" required placeholder="Min. 8 characters">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white toggle-password" style="cursor: pointer;">
                                                            <i data-feather="eye" class="feather-sm"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="text-muted font-weight-medium small text-uppercase mb-2 d-block">Confirm New Password</label>
                                                <div class="input-group shadow-none">
                                                    <input type="password" class="form-control" id="cpassword" name="cpassword" required placeholder="Repeat new password">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white toggle-password" style="cursor: pointer;">
                                                            <i data-feather="eye" class="feather-sm"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-top pt-4 mt-2 text-right">
                                        <button type="submit" name="changepwd" class="btn btn-primary px-4 shadow-sm font-weight-medium">
                                            <i data-feather="refresh-cw" class="mr-2 feather-sm"></i>Update Credentials
                                        </button>
                                        <button type="reset" class="btn btn-light px-4 ml-2">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </form>

                 
                 </form>
                 
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
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>

    <script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>
    <script src="../dist/js/show-password.js"></script>
    <script>
    function checkAvailability() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "check-availability-admin.php",
            data:'emailid='+$("#emailid").val(),
            type: "POST",
        success:function(data){
            $("#user-availability-status").html(data);
            $("#loaderIcon").hide();
        },
        error:function (){}
        });
    }
    </script>

    <script>
    function checkpass() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "check-availability-admin.php",
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
