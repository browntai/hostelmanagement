<?php
session_start();
include('../includes/dbconn.php');

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

// Delete Action
if(isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $adn="DELETE from users where id=?";
    $stmt= $mysqli->prepare($adn);
    $stmt->bind_param('i',$id);
    if($stmt->execute()){
        include_once('../includes/toast-helper.php');
        if(function_exists('setToast')){
            setToast('success', 'User deleted successfully.');
        } else {
             echo "<script>alert('User deleted successfully');</script>";
        }
    }
    $stmt->close();	   
    echo "<script>window.location.href='manage-users.php';</script>" ;
    exit();
}

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users - Super Admin</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <style>
        .user-card {
            transition: all 0.3s ease;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
        .profile-initial {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: #fff;
            border-radius: 12px;
            background: linear-gradient(135deg, #17c788 0%, #1c2d41 100%);
        }
        .badge-light-primary { background-color: rgba(23, 199, 136, 0.1); color: #17c788; }
        .badge-light-danger { background-color: rgba(255, 79, 99, 0.1); color: #ff4f63; }
        .badge-light-success { background-color: rgba(44, 208, 126, 0.1); color: #2cd07e; }
        .badge-light-warning { background-color: rgba(255, 188, 52, 0.1); color: #ffbc34; }
    </style>
</head>
<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>

    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        
        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>

        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/super-sidebar.php'; ?>
            </div>
        </aside>

        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Manage Users</h3>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0 text-muted small">
                                    <li class="breadcrumb-item"><a href="super_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Users & Access</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="col-5 align-self-center text-right">
                        <a href="add-user.php" class="btn btn-info btn-sm shadow-sm">
                            <i data-feather="user-plus" class="feather-xs mr-1"></i> Add User
                        </a>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <!-- Data Table Starts -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM users ORDER BY id DESC";
                                    $res = $mysqli->query($sql);
                                    while($row = $res->fetch_assoc()) {
                                        $statusBadge = 'secondary';
                                        if($row['status'] == 'active') $statusBadge = 'success';
                                        if($row['status'] == 'suspended') $statusBadge = 'danger';
                                        
                                        $roleBadge = 'primary';
                                        if($row['role'] == 'admin') $roleBadge = 'danger';
                                        if($row['role'] == 'landlord') $roleBadge = 'info';
                                        if($row['role'] == 'client') $roleBadge = 'success';
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td class="font-weight-medium text-dark">
                                            <?php echo htmlentities($row['full_name']); ?>
                                            <?php if($row['id'] == $_SESSION['id']): ?>
                                                <span class="badge badge-light-warning text-warning small ml-1">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="text-muted small"><?php echo htmlentities($row['email']); ?></span></td>
                                        <td><span class="badge badge-light-<?php echo $roleBadge; ?> text-<?php echo $roleBadge; ?> small"><?php echo ucfirst($row['role']); ?></span></td>
                                        <td><span class="badge badge-light-<?php echo $statusBadge; ?> text-<?php echo $statusBadge; ?> small"><?php echo ucfirst($row['status']); ?></span></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit-user.php?id=<?php echo $row['id']; ?>" class="btn btn-light bg-white border text-info btn-sm px-2" title="Edit Profile">
                                                    <i data-feather="edit" class="feather-xs"></i>
                                                </a>
                                                <?php if($row['id'] != $_SESSION['id']): ?>
                                                    <a href="super_impersonate.php?tenant_id=<?php echo $row['tenant_id']; ?>" class="btn btn-light bg-white border text-primary btn-sm px-2" title="Login As User">
                                                        <i data-feather="log-in" class="feather-xs"></i>
                                                    </a>
                                                    <a href="manage-users.php?del=<?php echo $row['id']; ?>" class="btn btn-light bg-white border text-danger btn-sm px-2" onclick="return confirm('Delete this user permanently?')" title="Delete Account">
                                                        <i data-feather="trash-2" class="feather-xs"></i>
                                                    </a>
                                                <?php endif; ?>
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

                <!-- Empty State -->
                <div id="noResults" class="row py-5 d-none">
                    <div class="col-12 text-center py-5">
                        <i data-feather="user-x" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                        <h4 class="text-muted">No users found matching your search.</h4>
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
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();
            $('#usersTable').DataTable({
                "language": {
                    "search": "Quick Search: ",
                    "lengthMenu": "Show _MENU_ users"
                }
            });
        });
    </script>
</body>
</html>
