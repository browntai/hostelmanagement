<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    if(isset($_POST['submit'])){
        $seater=$_POST['seater'];
        $fees=$_POST['fees'];
        $hostel_id = isset($_POST['hostel']) ? intval($_POST['hostel']) : NULL;
        $id=$_GET['id'];
        $query="UPDATE rooms set seater=?,fees=?,hostel_id=? where id=? AND tenant_id=?";
        $stmt = $mysqli->prepare($query);
        $rc=$stmt->bind_param('iiiii',$seater,$fees,$hostel_id,$id,$tenantId);
        if($stmt->execute()){
            include_once('../includes/log-helper.php');
            include_once('../includes/toast-helper.php');
            $uemail = isset($_SESSION['login']) ? $_SESSION['login'] : 'unknown';
            logActivity($_SESSION['id'], $uemail, 'Landlord/Admin', 'Update Room', "Updated room details for ID: $id");
            setToast('success', 'Room details has been updated');
        } else {
            include_once('../includes/toast-helper.php');
            setToast('error', 'Error updating room details');
        }
        header("Location: manage-rooms.php");
        exit();
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Edit Room Details</h4>
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

                <form method="POST">

                    <div class="row">


                    <?php	
                        $id=$_GET['id'];
						$ret="SELECT * from rooms where id=? AND tenant_id=?";
                        $stmt= $mysqli->prepare($ret) ;
                     $stmt->bind_param('ii',$id, $tenantId);
                     $stmt->execute() ;//ok
                     $res=$stmt->get_result();
                     //$cnt=1;
                       while($row=$res->fetch_object())
                      {
                          ?>


                        <div class="col-sm-12 col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Hostel</h4>
                                        <div class="form-group mb-4">
                                            <select class="custom-select mr-sm-2" id="hostel" name="hostel">
                                                <option value="">None (Unassigned)</option>
                                                <?php
                                                $h_query = "SELECT id, name FROM hostels WHERE tenant_id = ?";
                                                $h_stmt = $mysqli->prepare($h_query);
                                                $h_stmt->bind_param('i', $tenantId);
                                                $h_stmt->execute();
                                                $h_res = $h_stmt->get_result();
                                                while($h_row = $h_res->fetch_object()){
                                                    $selected = ($h_row->id == $row->hostel_id) ? 'selected' : '';
                                                    echo "<option value=\"{$h_row->id}\" $selected>" . htmlentities($h_row->name) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Room Number</h4>
                                        <div class="form-group">
                                            <input type="text" name="rmno" value="<?php echo $row->room_no;?>" id="rmno" class="form-control" disabled>
                                        </div>
                                    
                                </div>
                            </div>
                        </div>



                        <div class="col-sm-12 col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Seater</h4>
                                        <div class="form-group mb-4">
                                            <select class="custom-select mr-sm-2" id="seater" name="seater" required="required">
                                                <option value="<?php echo $row->seater;?>"><?php echo $row->seater;?></option>
                                                <option value="1">Single Seater</option>
                                                <option value="2">Two Seater</option>
                                                <option value="3">Three Seater</option>
                                                <option value="4">Four Seater</option>
                                                <option value="5">Five Seater</option>
                                            </select>
                                        </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-sm-12 col-md-6 col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Total Fees</h4>
                                        <div class="form-group">
                                            <input type="number" name="fees" id="fees" value="<?php echo $row->fees;?>" class="form-control">
                                        </div>
                                </div>
                            </div>
                        </div>

                        <?php } ?>

                    </div>
                

                        <div class="form-actions">
                            <div class="text-center">
                                <button type="submit" name="submit" class="btn btn-success">Update</button>
                                <button type="reset" class="btn btn-danger">Reset</button>
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
