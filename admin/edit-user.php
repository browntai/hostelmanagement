<?php
session_start();
include('../includes/dbconn.php');

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

$id = intval($_GET['id']);

if(isset($_POST['update'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $tenant_id = intval($_POST['tenant_id']); // Be careful changing this
    
    // Optional: Password update
    $params = [$full_name, $email, $role, $status, $tenant_id, $first_name, $middle_name, $last_name, $gender, $contact_no, $id_no];
    $types = "ssssissssss";
    $password_clause = "";
    
    if(!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $password_clause = ", password=?";
        $params[] = $password;
        $types .= "s";
    }
    
    $params[] = $id;
    $types .= "i";

    $query = "UPDATE users SET full_name=?, email=?, role=?, status=?, tenant_id=?, first_name=?, middle_name=?, last_name=?, gender=?, contact_no=?, id_no=? $password_clause WHERE id=?";
    $stmt = $mysqli->prepare($query);
    
    // Create a dynamic bind_param call
    $stmt->bind_param($types, ...$params);
    
    if($stmt->execute()) {
        echo "<script>alert('User details updated successfully'); window.location.href='manage-users.php';</script>";
    } else {
        echo "<script>alert('Error updating user: " . $mysqli->error . "');</script>";
    }
}

// Fetch user data
$query = "SELECT * FROM users WHERE id=?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_object();

if(!$user) {
    header("Location: manage-users.php");
    exit();
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit User - Super Admin</title>
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
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Edit User</h3>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="super_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="manage-users.php">Manage Users</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
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
                                                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlentities($user->full_name); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email Address</label>
                                                    <input type="email" class="form-control" name="email" value="<?php echo htmlentities($user->email); ?>" required>
                                                </div>
                                            </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlentities($user->first_name); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Middle Name</label>
                                                    <input type="text" class="form-control" name="middle_name" value="<?php echo htmlentities($user->middle_name); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlentities($user->last_name); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Gender</label>
                                                    <select class="custom-select mr-sm-2" name="gender">
                                                        <option value="Male" <?php if($user->gender == 'Male') echo 'selected'; ?>>Male</option>
                                                        <option value="Female" <?php if($user->gender == 'Female') echo 'selected'; ?>>Female</option>
                                                        <option value="Other" <?php if($user->gender == 'Other') echo 'selected'; ?>>Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Contact Number</label>
                                                    <input type="text" class="form-control" name="contact_no" value="<?php echo htmlentities($user->contact_no); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>ID Number</label>
                                                    <input type="text" class="form-control" name="id_no" value="<?php echo htmlentities($user->id_no ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Role</label>
                                                    <select class="custom-select mr-sm-2" name="role" required>
                                                        <option value="client" <?php if($user->role == 'client') echo 'selected'; ?>>Client/Client</option>
                                                        <option value="caretaker" <?php if($user->role == 'caretaker') echo 'selected'; ?>>Caretaker</option>
                                                        <option value="landlord" <?php if($user->role == 'landlord') echo 'selected'; ?>>Landlord</option>
                                                        <option value="admin" <?php if($user->role == 'admin') echo 'selected'; ?>>Super Admin</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select class="custom-select mr-sm-2" name="status" required>
                                                        <option value="active" <?php if($user->status == 'active') echo 'selected'; ?>>Active</option>
                                                        <option value="suspended" <?php if($user->status == 'suspended') echo 'selected'; ?>>Suspended</option>
                                                        <option value="pending" <?php if($user->status == 'pending') echo 'selected'; ?>>Pending</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Tenant ID / Landlord ID</label>
                                                    <input type="number" class="form-control" name="tenant_id" value="<?php echo htmlentities($user->tenant_id); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                 <div class="form-group">
                                                    <label>New Password (Optional)</label>
                                                    <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <div class="text-right">
                                            <button type="submit" name="update" class="btn btn-info">Update User</button>
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
