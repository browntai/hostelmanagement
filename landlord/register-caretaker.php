<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    if(isset($_POST['submit']))
    {
        $idno=$_POST['idno'];
        $fname=$_POST['fname'];
        $mname=$_POST['mname'];
        $lname=$_POST['lname'];
        $gender=$_POST['gender'];
        $contactno=$_POST['contact'];
        $uuid=$_POST['email'];
        $password=$_POST['password'];
        
        // Automate assignment: Use the first hostel found for this tenant
        $h_find = $mysqli->prepare("SELECT id FROM hostels WHERE tenant_id = ? LIMIT 1");
        $h_find->bind_param('i', $tenantId);
        $h_find->execute();
        $h_row = $h_find->get_result()->fetch_assoc();
        $assigned_hostel = $h_row['id'] ?? null;

        $password = md5($password);
        $full_name = trim($fname . ' ' . $mname . ' ' . $lname);
        
        // Check if email already registered
        $check_email = "SELECT email FROM users WHERE email=?";
        $cstmt = $mysqli->prepare($check_email);
        $cstmt->bind_param('s', $uuid);
        $cstmt->execute();
        $cres = $cstmt->get_result();
        if($cres->num_rows > 0) {
            include_once('../includes/toast-helper.php');
            setToast('error', 'Email already registered!');
        } else {
            $query="INSERT into users(tenant_id,email,password,role,full_name,first_name,middle_name,last_name,gender,contact_no,id_no,original_table,assigned_hostel_id) values(?,?,?,'caretaker',?,?,?,?,?,?,?,'caretaker_registration',?)";
            $stmt = $mysqli->prepare($query);
            $rc=$stmt->bind_param('isssssssssi',$tenantId,$uuid,$password,$full_name,$fname,$mname,$lname,$gender,$contactno,$idno,$assigned_hostel);
            $stmt->execute();
            include_once('../includes/toast-helper.php');
            setToast('success', 'Caretaker has been Registered!');
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
    <title>Register Caretaker — Hostel Management System</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <script type="text/javascript">
    function valid(){
        if(document.bookings.password.value != document.bookings.cpassword.value)
        {
            alert("Password and Confirm Password does not match");
            document.bookings.cpassword.focus();
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                            <i data-feather="user-check" class="feather-sm mr-2 text-info"></i>Caretaker Registration Form
                        </h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Register Caretaker</li>
                                </ol>
                            </nav>
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

                <form method="POST" name="bookings" onSubmit="return valid();">
                    <div class="row">
                        <!-- SECTION 1: Account Identification -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-gradient-info py-3">
                                    <h5 class="mb-0 text-white"><i data-feather="lock" class="feather-sm mr-2"></i>Account Identification</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block"><i data-feather="hash" class="feather-xs mr-1 text-primary"></i> ID Number / Code</label>
                                        <input type="text" name="idno" placeholder="Enter national ID or system code" id="idno" class="form-control border-focus-primary" required>
                                    </div>
                                    
                                    <div class="form-group mb-4">
                                        <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block"><i data-feather="mail" class="feather-xs mr-1 text-primary"></i> Registration Email</label>
                                        <input type="email" name="email" id="email" placeholder="caretaker@example.com" onBlur="checkAvailability()" required class="form-control border-focus-primary">
                                        <span id="user-availability-status" class="small mt-1 d-block"></span>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Set Password</label>
                                            <div class="input-group">
                                                <input type="password" name="password" id="password" placeholder="••••••••" required class="form-control border-focus-primary">
                                                <div class="input-group-append">
                                                    <span class="input-group-text toggle-password cursor-pointer"><i data-feather="eye" class="feather-xs"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Confirm Password</label>
                                            <div class="input-group">
                                                <input type="password" name="cpassword" id="cpassword" placeholder="••••••••" required class="form-control border-focus-primary">
                                                <div class="input-group-append">
                                                    <span class="input-group-text toggle-password cursor-pointer"><i data-feather="eye" class="feather-xs"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: Personal Details -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-gradient-primary py-3">
                                    <h5 class="mb-0 text-white"><i data-feather="user" class="feather-sm mr-2"></i>Personal Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">First Name</label>
                                            <input type="text" name="fname" id="fname" placeholder="First" required class="form-control border-focus-primary">
                                        </div>
                                        <div class="col-md-4 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Middle Name</label>
                                            <input type="text" name="mname" id="mname" placeholder="Middle" class="form-control border-focus-primary">
                                        </div>
                                        <div class="col-md-4 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Last Name</label>
                                            <input type="text" name="lname" id="lname" placeholder="Last" required class="form-control border-focus-primary">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Gender</label>
                                            <select class="custom-select border-focus-primary" id="gender" name="gender" required>
                                                <option value="">Select...</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Others">Others</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block"><i data-feather="phone" class="feather-xs mr-1 text-primary"></i> Contact Number</label>
                                            <input type="number" name="contact" id="contact" placeholder="0712345678" required class="form-control border-focus-primary">
                                        </div>
                                    </div>

                                    <div class="alert alert-light border small text-muted mb-0 mt-2">
                                        <i data-feather="info" class="feather-xs mr-1"></i> Ensure all details match the caretaker's official identification documents. The caretaker will have access to the landlord management dashboard.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-12 mt-2">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-4">
                                    <button type="submit" name="submit" class="btn btn-info btn-lg px-5 shadow-sm mr-2">
                                        <i data-feather="user-check" class="mr-2 feather-sm"></i>Register Caretaker
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary btn-lg px-5 ml-2">
                                        <i data-feather="rotate-ccw" class="mr-2 feather-sm"></i>Reset Form
                                    </button>
                                    <a href="manage-caretakers.php" class="btn btn-outline-primary btn-lg px-5 ml-2">
                                        <i data-feather="users" class="mr-2 feather-sm"></i>View All Caretakers
                                    </a>
                                </div>
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
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
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
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <?php 
        include_once('../includes/toast-helper.php');
        showAlerts(); 
    ?>
 
    <!-- customs -->
    <script src="../dist/js/show-password.js"></script>
    <script>
    function checkAvailability() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: "check-availability.php",
            data:'emailid='+$("#email").val(),
            type: "POST",
            success:function(data){
                $("#user-availability-status").html(data);
                $("#loaderIcon").hide();
            },
            error:function () {
                event.preventDefault();
                alert('error');
            }
        });
    }
    </script>
</body>

</html>
