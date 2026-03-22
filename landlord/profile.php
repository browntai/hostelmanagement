<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    if(isset($_POST['update'])){
        if(isset($_POST['emailid'])){
            $email=$_POST['emailid'];
            $contactno=$_POST['contact'];
            $aid=$_SESSION['id'];
            
            $profile_pic = null;
            $upload_err = "";
            
            if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
                $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $allowed = array('jpg', 'jpeg', 'png', 'gif');
                if(in_array(strtolower($ext), $allowed)){
                    $new_name = "user_" . $aid . "_" . time() . "." . $ext;
                    $upload_target = "../uploads/profiles/" . $new_name;
                    if(!is_dir("../uploads/profiles")) mkdir("../uploads/profiles", 0777, true);
                    
                    if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_target)){
                        $profile_pic = $new_name;
                    } else {
                        $upload_err = "Failed to upload image.";
                    }
                }
            }

            if($profile_pic) {
                // Update with picture
                $query="UPDATE users set email=?, contact_no=?, profile_pic=? where id=?";
                $stmt = $mysqli->prepare($query);
                $rc=$stmt->bind_param('sssi',$email, $contactno, $profile_pic, $aid);
                $stmt->execute();
            } else {
                // Update without picture
                $query="UPDATE users set email=?, contact_no=? where id=?";
                $stmt = $mysqli->prepare($query);
                $rc=$stmt->bind_param('ssi',$email, $contactno, $aid);
                $stmt->execute();
            }
            
            // Also update admin table if it exists and has this user (for synchronization)
            $query2="UPDATE admin set email=? where id=?";
            $stmt2 = $mysqli->prepare($query2);
            $stmt2->bind_param('si',$email,$aid);
            $stmt2->execute();

            echo"<script>alert('Email id has been successfully updated');</script>";
        } else {
            echo"<script>alert('Error: Email ID field is missing in the submission.');</script>";
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">My Profile</h4>
                        <div class="d-flex align-items-center">
                            <h6 class="card-subtitle"><code>You cannot make changes in username and registered date!</code> </h6> 
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

                <form method="POST" enctype="multipart/form-data">

                    <div class="row">
                        <?php	
                        $aid=$_SESSION['id'];
                        $ret="SELECT * from users where id=?";
                        $stmt= $mysqli->prepare($ret) ;
                        $stmt->bind_param('i',$aid);
                        $stmt->execute();
                        $res=$stmt->get_result();
                        
                        // Fallback/Debug: If not found in users, check admin table (rare for landlords but good for sync)
                        if($res->num_rows == 0) {
                            $ret="SELECT * from admin where id=?";
                            $stmt= $mysqli->prepare($ret) ;
                            $stmt->bind_param('i',$aid);
                            $stmt->execute();
                            $res=$stmt->get_result();
                            
                            if($res->num_rows > 0) {
                                while($row=$res->fetch_object()) {
                                    $row->full_name = $row->username;
                                    $row->created_at = $row->reg_date;
                                    render_professional_profile($row);
                                }
                            } else {
                                echo '<div class="col-12"><div class="alert alert-danger">Profile not found. Please re-login.</div></div>';
                            }
                        } else {
                            while($row=$res->fetch_object()) {
                                render_professional_profile($row);
                            }
                        }

                        function render_professional_profile($row) {
                        ?>
                        <div class="col-lg-4 col-md-5">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body text-center">
                                    <p class="text-muted small text-uppercase font-weight-bold mb-4">Hostel Owner</p>
                                    <div class="mb-4">
                                        <?php 
                                        $pic = $row->profile_pic ? "../uploads/profiles/" . $row->profile_pic : "../assets/images/users/user-icn.png";
                                        ?>
                                        <div class="position-relative d-inline-block">
                                            <img src="<?php echo $pic; ?>" alt="user" class="rounded-circle shadow-sm" width="150" height="150" style="object-fit: cover; border: 4px solid #fff;">
                                            <span class="badge badge-primary position-absolute" style="bottom: 10px; right: 10px; border: 2px solid #fff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                                <i data-feather="check" class="feather-xs"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <h4 class="font-weight-bold text-dark mt-2"><?php echo htmlentities($row->full_name); ?></h4>
                                    <p class="text-muted mb-4">Landlord Account</p>
                                    
                                    <div class="custom-file mt-2">
                                        <input type="file" name="profile_pic" class="custom-file-input" id="profile_pic" accept="image/*">
                                        <label class="custom-file-label text-left" for="profile_pic">Update avatar...</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 col-md-7">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-header bg-gradient-info py-3">
                                    <h5 class="mb-0 text-white"><i data-feather="user-check" class="feather-sm mr-2"></i>Profile Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-medium small text-uppercase mb-2 d-block"><i data-feather="user" class="feather-xs mr-1 text-primary"></i> Full Name</label>
                                            <input type="text" value="<?php echo htmlentities($row->full_name); ?>" disabled class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-medium small text-uppercase mb-2 d-block"><i data-feather="mail" class="feather-xs mr-1 text-primary"></i> Email Address</label>
                                            <input type="email" class="form-control" name="emailid" id="emailid" value="<?php echo htmlentities($row->email); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-medium small text-uppercase mb-2 d-block"><i data-feather="phone" class="feather-xs mr-1 text-primary"></i> Contact Number</label>
                                            <input type="text" class="form-control" name="contact" id="contact" value="<?php echo htmlentities($row->contact_no); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-medium small text-uppercase mb-2 d-block"><i data-feather="calendar" class="feather-xs mr-1 text-primary"></i> Onboarded On</label>
                                            <input type="text" class="form-control bg-light border-0" value="<?php echo date('M d, Y', strtotime($row->created_at)); ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted font-weight-medium small text-uppercase mb-2 d-block"><i data-feather="activity" class="feather-xs mr-1 text-primary"></i> Account Status</label>
                                            <span class="badge badge-light-success text-success px-3 py-2 d-block text-left" style="font-size: 0.9rem;">
                                                Active Partner
                                            </span>
                                        </div>
                                    </div>

                                    <div class="alert alert-light border shadow-none mb-0 mt-2">
                                        <div class="d-flex align-items-start">
                                            <i data-feather="help-circle" class="text-muted mr-2 mt-1 feather-sm"></i>
                                            <p class="mb-0 small text-muted">Username and registration date are managed by the system administrator. If you need to change your registered name, please submit a request via the support portal.</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-top text-right">
                                        <button type="submit" name="update" class="btn btn-info px-4 shadow-sm">
                                            <i data-feather="save" class="feather-sm mr-2"></i>Update Profile
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary px-4 ml-2">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                 
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

</body>

</html>
