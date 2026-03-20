<?php
session_start();
include('../includes/dbconn.php');
    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    // Status Actions
    if(isset($_GET['suspend'])) {
        $id = intval($_GET['suspend']);
        $query = "UPDATE tenants SET status = 'suspended' WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $id);
        if($stmt->execute()) {
            echo "<script>alert('Tenant suspended successfully!'); window.location.href='manage-tenants.php';</script>";
        }
    }

    if(isset($_GET['activate'])) {
        $id = intval($_GET['activate']);
        $query = "UPDATE tenants SET status = 'active' WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $id);
        if($stmt->execute()) {
            echo "<script>alert('Tenant activated successfully!'); window.location.href='manage-tenants.php';</script>";
        }
    }

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Landlords - Super Admin</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
</head>
<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>

    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        
        <!-- Topbar -->
        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>

        <!-- Sidebar -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/super-sidebar.php'; ?>
            </div>
        </aside>

        <!-- Page Content -->
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Manage Landlords</h3>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="super_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Manage Landlords</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <!-- Tenant List -->
                 <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <h4 class="card-title">All Landlords</h4>
                                    <div class="ml-auto">
                                        <a href="add-landlord.php" class="btn btn-primary"><i data-feather="plus" class="feather-icon"></i> Add New Landlord</a>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="tenants_table" class="table table-striped table-bordered no-wrap">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Code</th>
                                                <th>Admin Email</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT * FROM users WHERE role = 'landlord' ORDER BY id DESC";
                                            $res = $mysqli->query($sql);
                                            while($row = $res->fetch_assoc()) {
                                                $statusClass = 'secondary';
                                                if($row['status'] == 'active') $statusClass = 'success';
                                                if($row['status'] == 'suspended') $statusClass = 'danger';
                                                
                                                echo "<tr>";
                                                echo "<td>".$row['id']."</td>";
                                                echo "<td>".htmlentities($row['full_name'])."</td>";
                                                echo "<td><span class='badge badge-info'>ID: ".$row['tenant_id']."</span></td>";
                                                echo "<td>".htmlentities($row['email'])."</td>";
                                                echo "<td><span class='badge badge-".$statusClass."'>".ucfirst($row['status'])."</span></td>";
                                                echo "<td>---</td>";
                                                echo "<td>
                                                        <a href='edit-user.php?id=".$row['id']."' class='btn btn-sm btn-info' title='Edit'><i data-feather='edit' class='feather-icon'></i></a>
                                                        <a href='super_impersonate.php?tenant_id=".$row['tenant_id']."' class='btn btn-sm btn-primary' title='Login as this landlord'><i data-feather='log-in' class='feather-icon'></i></a> ";
                                                
                                                if($row['status'] == 'active') {
                                                    echo "<a href='manage-users.php?suspend=".$row['id']."' class='btn btn-sm btn-danger' title='Suspend'><i data-feather='slash' class='feather-icon'></i></a>";
                                                } else {
                                                    echo "<a href='manage-users.php?activate=".$row['id']."' class='btn btn-sm btn-success' title='Activate'><i data-feather='play' class='feather-icon'></i></a>";
                                                }
                                                
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>

            </div>
            
            <?php include 'includes/footer.php' ?>
        </div>
    </div>

    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>
    <!-- DataTables -->
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/extra-libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(".preloader").fadeOut();
        $('#tenants_table').DataTable({
            "order": [[ 0, "desc" ]]
        });
    </script>
</body>
</html>
