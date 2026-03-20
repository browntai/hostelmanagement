<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    // Restrict this page to Super Admin only
    if(!$tm->isSuperAdmin()) {
        header("Location: dashboard.php");
        exit();
    }

    if(isset($_GET['del'])) {
        $id=intval($_GET['del']);
        $adn="DELETE from users where id=? AND role='client'";
            $stmt= $mysqli->prepare($adn);
            $stmt->bind_param('i',$id);
            $stmt->execute();
            $stmt->close();	   
            echo "<script>alert('Record has been deleted');</script>" ;
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

    <script language="javascript" type="text/javascript">
    var popUpWin=0;
    function popUpWindow(URLStr, left, top, width, height){
        if(popUpWin) {
         if(!popUpWin.closed) popUpWin.close();
            }
            popUpWin = open(URLStr,'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width='+510+',height='+430+',left='+left+', top='+top+',screenX='+left+',screenY='+top+'');
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Client's Account</h4>
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

                <!-- Data Table Starts -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="accountsTable" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>ID No.</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Gender</th>
                                        <?php if($tm->isSuperAdmin()): ?>
                                        <th>Associated Tenant</th>
                                        <?php endif; ?>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php	
                                    $aid=$_SESSION['id'];
                                    $tenantWhere = $tm->getTenantWhereClause('users');
                                    $ret="SELECT users.*, tenants.name as tenant_name 
                                          FROM users 
                                          LEFT JOIN tenants ON users.tenant_id = tenants.id
                                          WHERE users.role = 'client' " . ($tenantWhere ? " AND ".ltrim($tenantWhere, 'WHERE ') : "");
                                    $stmt= $mysqli->prepare($ret) ;
                                    $stmt->execute() ;
                                    $res=$stmt->get_result();
                                    
                                    while($row=$res->fetch_object()) {
                                    ?>
                                    <tr>
                                        <td class="font-weight-medium text-dark"><?php echo $row->first_name . ' ' . $row->last_name; ?></td>
                                        <td><?php echo $row->id_no; ?></td>
                                        <td><span class="text-muted small"><?php echo $row->email; ?></span></td>
                                        <td><?php echo $row->contact_no; ?></td>
                                        <td><?php echo ucfirst($row->gender); ?></td>
                                        <?php if($tm->isSuperAdmin()): ?>
                                        <td>
                                            <span class="badge badge-light-secondary text-secondary small">
                                                <?php echo $row->tenant_name ? htmlentities($row->tenant_name) : 'No Tenant'; ?>
                                            </span>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-light bg-white border btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" class="feather-sm"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                                    <a class="dropdown-item text-danger" href="view-clients-acc.php?del=<?php echo $row->id;?>" 
                                                       onclick="return confirm('Do you want to delete this account?');">
                                                        <i data-feather="trash-2" class="feather-sm mr-2"></i>Delete Account
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Data Table Ends -->

                <style>
                .badge-light-primary { background-color: rgba(23, 199, 136, 0.1); color: #17c788; }
                .badge-light-secondary { background-color: rgba(116, 126, 130, 0.1); color: #747e82; }
                </style>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    $(".preloader").fadeOut();
                    $('#accountsTable').DataTable({
                        "language": {
                            "search": "Quick Search: ",
                            "lengthMenu": "Show _MENU_ accounts"
                        }
                    });
                });
                </script>

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
