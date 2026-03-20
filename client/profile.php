<?php
    session_start();
    include('../includes/dbconn.php');
    date_default_timezone_set('America/Chicago');
    include('../includes/check-login.php');
    check_login();
    $aid=$_SESSION['id'];
    if(isset($_POST['update']))
    {
    $fname=$_POST['fname'];
    $mname=$_POST['mname'];
    $lname=$_POST['lname'];
    $gender=$_POST['gender'];
    $contactno=$_POST['contact'];
    $full_name = trim($fname . ' ' . $mname . ' ' . $lname);
    
    // Handle Profile Picture
    $pic_sql = "";
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        if(in_array(strtolower($ext), $allowed)){
            $new_name = "user_" . $aid . "_" . time() . "." . $ext;
            if(!is_dir("../uploads/profiles")) mkdir("../uploads/profiles", 0777, true);
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], "../uploads/profiles/" . $new_name);
            $pic_sql = ", profile_pic='$new_name'";
        }
    }

    $query="UPDATE users SET first_name=?, middle_name=?, last_name=?, gender=?, contact_no=?, full_name=? $pic_sql WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ssssssi', $fname, $mname, $lname, $gender, $contactno, $full_name, $aid);
    $stmt->execute();
    echo"<script>alert('Profile updated Successfully');</script>";
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
    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">

    <!-- <script type="text/javascript">
    function valid(){
        if(document.bookings.password.value!= document.bookings.cpassword.value){
            alert("Password and Re-Type Password Field do not match !!");
            document.bookings.cpassword.focus();
        return false;
            } return true;
     }
    </script> -->
    
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
            <!-- By Brown Tom -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                
                <div class="row mb-4">
                    <div class="col-12 text-center text-lg-left">
                        <h2 class="page-title text-dark font-weight-bold mb-1">Account Persona</h2>
                        <p class="text-muted">Manage your personal identity and contact credentials.</p>
                    </div>
                </div>

                <div class="row">
                    <?php	
                    $aid=$_SESSION['id'];
                    $ret="select * from users where id=?";
                    $stmt= $mysqli->prepare($ret) ;
                    $stmt->bind_param('i',$aid);
                    $stmt->execute();
                    $res=$stmt->get_result();
                    while($row=$res->fetch_object()) {
                    ?>
                    <!-- Left Column: Avatar & Summary -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 h-100 rounded-lg overflow-hidden">
                            <div class="bg-gradient-brand py-5 text-center px-4">
                                <?php 
                                $pic = $row->profile_pic ? "../uploads/profiles/" . $row->profile_pic : "../assets/images/users/1.jpg";
                                ?>
                                <div class="position-relative d-inline-block avatar-container mb-3">
                                    <img src="<?php echo $pic; ?>" alt="user" class="rounded-circle shadow-lg border border-white" width="140" height="140" style="object-fit: cover; border-width: 4px !important;">
                                    <span class="status-indicator bg-success"></span>
                                </div>
                                <h4 class="font-weight-black text-white mb-0"><?php echo htmlentities($row->full_name); ?></h4>
                                <small class="text-white opacity-7">Residential Partner Since <?php echo date('Y', strtotime($row->created_at)); ?></small>
                            </div>
                            
                            <div class="card-body pt-4">
                                <div class="mb-4">
                                    <label class="text-muted small text-uppercase font-weight-black mb-3 d-block letter-spacing-1">Identity Access</label>
                                    <div class="bg-light p-3 rounded-lg border-0 mb-3">
                                        <small class="text-muted d-block mb-1">Digital ID</small>
                                        <span class="text-dark font-weight-bold"><?php echo $row->id_no; ?></span>
                                    </div>
                                    
                                    <div class="custom-file-premium mb-2">
                                        <input type="file" name="profile_pic" class="custom-file-input" id="profile_pic" accept="image/*" style="display: none;" onchange="$('#file-name-display').text(this.files[0].name)">
                                        <button type="button" class="btn btn-brand btn-block rounded-pill font-weight-bold py-2" onclick="$('#profile_pic').click()">
                                            <i data-feather="camera" class="feather-sm mr-2 text-white"></i> Update Portrait
                                        </button>
                                        <small id="file-name-display" class="text-brand small d-block text-center mt-2"></small>
                                    </div>
                                    <small class="text-muted text-center d-block opacity-7" style="font-size: 0.7rem;">High-res JPG or PNG recommended.</small>
                                </div>

                                <div class="bg-light-brand-subtle p-3 rounded-lg border-brand">
                                    <div class="d-flex align-items-center mb-1">
                                        <i data-feather="shield" class="text-brand mr-2 feather-xs"></i>
                                        <small class="text-brand font-weight-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.65rem;">Membership Verified</small>
                                    </div>
                                    <p class="mb-0 text-dark small opacity-8">Your account details are securely encrypted and managed under strict residential compliance.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Form Fields -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 h-100 rounded-lg overflow-hidden">
                            <div class="card-header bg-white py-4 border-bottom d-flex align-items-center">
                                <div class="bg-light-brand p-2 rounded mr-3">
                                    <i data-feather="settings" class="text-brand"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-dark font-weight-black">Profile Credentials</h5>
                                    <small class="text-muted">Ensure your primary details are accurate for lease processing.</small>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <!-- Full Name Segment -->
                                    <div class="col-12 mb-4">
                                        <small class="text-brand-light text-uppercase font-weight-black letter-spacing-1 mb-3 d-block">Full Legal Name</small>
                                        <div class="row">
                                            <div class="col-md-4 mb-3 mb-md-0">
                                                <input type="text" name="fname" id="fname" class="form-control form-control-lg border-0 bg-light rounded-lg font-weight-medium" value="<?php echo $row->first_name;?>" placeholder="First" required>
                                            </div>
                                            <div class="col-md-4 mb-3 mb-md-0">
                                                <input type="text" name="mname" id="mname" class="form-control form-control-lg border-0 bg-light rounded-lg font-weight-medium" value="<?php echo $row->middle_name;?>" placeholder="Middle">
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="lname" id="lname" class="form-control form-control-lg border-0 bg-light rounded-lg font-weight-medium" value="<?php echo $row->last_name;?>" placeholder="Last" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Personal Details -->
                                    <div class="col-md-6 mb-4">
                                        <small class="text-brand text-uppercase font-weight-black letter-spacing-1 mb-3 d-block">Gender Reference</small>
                                        <select class="custom-select custom-select-lg border-0 bg-light rounded-lg font-weight-medium" id="gender" name="gender">
                                            <option value="<?php echo $row->gender;?>"><?php echo $row->gender;?></option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <small class="text-brand text-uppercase font-weight-black letter-spacing-1 mb-3 d-block">Contact Hotline</small>
                                        <input type="text" name="contact" id="contact" maxlength="10" class="form-control form-control-lg border-0 bg-light rounded-lg font-weight-medium" value="<?php echo $row->contact_no;?>" required>
                                    </div>

                                    <!-- Security Info -->
                                    <div class="col-12 mb-4">
                                        <small class="text-brand-light text-uppercase font-weight-black letter-spacing-1 mb-3 d-block">Primary Correspondance (Immutable)</small>
                                        <div class="input-group input-group-lg">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text border-0 bg-light px-3"><i data-feather="mail" class="feather-sm text-muted"></i></span>
                                            </div>
                                            <input type="email" name="email" id="email" class="form-control border-0 bg-light font-weight-medium text-muted" value="<?php echo $row->email;?>" readonly style="cursor: not-allowed;">
                                        </div>
                                        <small class="text-info mt-2 d-block font-weight-medium"><i data-feather="info" class="feather-xs mr-1"></i> Contact support to modify your registered email address.</small>
                                    </div>
                                </div>

                                <div class="pt-4 mt-2">
                                    <button type="submit" name="update" class="btn btn-brand btn-lg btn-block rounded-pill shadow-lg font-weight-black py-3">
                                        Commit Changes <i data-feather="arrow-right" class="ml-2"></i>
                                    </button>
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
    <!-- apps --><!-- By Brown Tom -->
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
