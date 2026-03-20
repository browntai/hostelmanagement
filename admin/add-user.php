<?php
session_start();
include('../includes/dbconn.php');
include('../includes/toast-helper.php');

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

if(isset($_POST['add'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $tenant_id = intval($_POST['tenant_id']);
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $contact_no = $_POST['contact_no'];
    $id_no = $_POST['id_no'];
    $password = md5($_POST['password']);

    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt_check = $mysqli->prepare($check_email);
    $stmt_check->bind_param('s', $email);
    $stmt_check->execute();
    if($stmt_check->get_result()->num_rows > 0) {
        setToast('error', 'Email already registered!');
    } else {
        $query = "INSERT INTO users (tenant_id, email, password, role, full_name, first_name, middle_name, last_name, gender, contact_no, id_no, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('isssssssssss', $tenant_id, $email, $password, $role, $full_name, $first_name, $middle_name, $last_name, $gender, $contact_no, $id_no, $status);
        
        if($stmt->execute()) {
            setToast('success', 'User created successfully!');
            header("location:manage-users.php");
            exit();
        } else {
            setToast('error', 'Error creating user: ' . $mysqli->error);
        }
    }
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add User - Super Admin</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
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
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Add New User</h3>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="super_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="manage-users.php">Manage Users</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Add User</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                 <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Full Name</label>
                                                    <input type="text" class="form-control" name="full_name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email Address</label>
                                                    <input type="email" class="form-control" name="email" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input type="text" class="form-control" name="first_name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Middle Name</label>
                                                    <input type="text" class="form-control" name="middle_name">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input type="text" class="form-control" name="last_name">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Gender</label>
                                                    <select class="custom-select mr-sm-2" name="gender">
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Contact Number</label>
                                                    <input type="text" class="form-control" name="contact_no">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>ID Number</label>
                                                    <input type="text" class="form-control" name="id_no">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Role</label>
                                                    <select class="custom-select mr-sm-2" name="role" required>
                                                        <option value="client">Client/Client</option>
                                                        <option value="caretaker">Caretaker</option>
                                                        <option value="landlord">Landlord</option>
                                                        <option value="admin">Super Admin</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select class="custom-select mr-sm-2" name="status" required>
                                                        <option value="active">Active</option>
                                                        <option value="suspended">Suspended</option>
                                                        <option value="pending">Pending</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Tenant ID (Enter 0 if Admin)</label>
                                                    <input type="number" class="form-control" name="tenant_id" required value="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                 <div class="form-group">
                                                    <label>Password</label>
                                                    <input type="password" class="form-control" name="password" required minlength="6">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <div class="text-right">
                                            <button type="submit" name="add" class="btn btn-info">Create User</button>
                                            <button type="reset" class="btn btn-dark">Reset</button>
                                            <a href="manage-users.php" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </div>
                                </form>
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
    <script>
        $(".preloader").fadeOut();
    </script>
</body>
</html>
