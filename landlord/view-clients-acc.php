<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    // Authenticate landlord access (In landlord directory, we assume the user is a landlord or super admin impersonating)
    if($_SESSION['role'] !== 'landlord' && !isset($_SESSION['impersonate_tenant_id'])) {
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
                    <div class="col-5 align-self-center text-right">
                        <a href="register-client.php" class="btn btn-success btn-sm shadow-sm">
                            <i data-feather="user-plus" class="feather-xs mr-1"></i> Add User
                        </a>
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

                <!-- Search & Filters -->
                <div class="row mb-4">
                    <div class="col-md-6 ml-auto">
                        <div class="input-group shadow-sm border-0">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0"><i data-feather="search" class="feather-sm"></i></span>
                            </div>
                            <input type="text" id="clientSearch" class="form-control border-0" placeholder="Search by name, email or ID...">
                        </div>
                    </div>
                </div>

                <!-- Card Grid Starts -->
                <div class="row" id="clientGrid">
                    <?php	
                    $aid=$_SESSION['id'];
                    $tenantWhere = $tm->getTenantWhereClause('users');
                    // Ensure landlords only see clients in their tenant/property group 
                    // (TenantManager handles this via getTenantWhereClause)
                    $ret="SELECT users.*, tenants.name as tenant_name 
                          FROM users 
                          LEFT JOIN tenants ON users.tenant_id = tenants.id
                          WHERE users.role = 'client' " . ($tenantWhere ? " AND ".ltrim($tenantWhere, 'WHERE ') : "");
                    
                    $stmt= $mysqli->prepare($ret) ;
                    $stmt->execute() ;
                    $res=$stmt->get_result();
                    $cnt=1;
                    while($row=$res->fetch_object()) {
                        $initials = strtoupper(substr($row->first_name, 0, 1) . (isset($row->last_name) ? substr($row->last_name, 0, 1) : ""));
                    ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3 mb-4 client-card" 
                         data-search="<?php echo strtolower($row->first_name . ' ' . $row->last_name . ' ' . $row->email . ' ' . $row->id_no); ?>">
                        <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden transition-all hover-shadow">
                            <!-- Gradient Top Border -->
                            <div class="bg-gradient-success" style="height: 4px;"></div>
                            
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0 mr-3">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-success font-weight-bold" 
                                             style="width: 50px; height: 50px; font-size: 1.2rem; border: 2px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                                            <?php echo $initials; ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="mb-0 text-dark font-weight-bold text-truncate"><?php echo htmlentities($row->first_name . ' ' . $row->last_name); ?></h5>
                                        <span class="badge badge-light-success text-success small px-2 py-1">ID: <?php echo $row->id_no; ?></span>
                                    </div>
                                    <div class="flex-shrink-0 ml-2">
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i data-feather="more-vertical" class="feather-sm"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                                <a class="dropdown-item text-danger" href="view-clients-acc.php?del=<?php echo $row->id;?>" 
                                                   onclick="return confirm('Do you want to delete this client account?');">
                                                    <i data-feather="trash-2" class="feather-sm mr-2"></i>Remove Access
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-sm mt-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i data-feather="mail" class="feather-sm text-muted mr-3"></i>
                                        <span class="text-muted small text-truncate" title="<?php echo $row->email; ?>"><?php echo $row->email; ?></span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i data-feather="phone" class="feather-sm text-muted mr-3"></i>
                                        <span class="text-muted small"><?php echo $row->contact_no; ?></span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i data-feather="user" class="feather-sm text-muted mr-3"></i>
                                        <span class="text-muted small"><?php echo ucfirst($row->gender); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php if($tm->isSuperAdmin()): ?>
                            <div class="card-footer bg-light border-0 py-2">
                                <small class="text-muted"><i data-feather="home" class="feather-xs mr-1"></i> <?php echo $row->tenant_name ? htmlentities($row->tenant_name) : 'No Tenant'; ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php $cnt++; } ?>
                </div>

                <style>
                .transition-all { transition: all 0.3s ease; }
                .hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
                .badge-light-success { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
                .space-y-sm > div { margin-bottom: 0.5rem; }
                .feather-xs { width: 12px; height: 12px; }
                </style>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('clientSearch');
                    const cards = document.querySelectorAll('.client-card');

                    if(searchInput) {
                        searchInput.addEventListener('input', function() {
                            const query = this.value.toLowerCase();
                            cards.forEach(card => {
                                const data = card.getAttribute('data-search');
                                if (data.includes(query)) {
                                    card.style.display = '';
                                } else {
                                    card.style.display = 'none';
                                }
                            });
                        });
                    }
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
