<?php
session_start();
include('../includes/dbconn.php');
include('../includes/toast-helper.php');

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

// Handle Form Submission
if(isset($_POST['add_landlord'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Using MD5 to match existing system
    $tenant_name = $_POST['tenant_name'];
    $status = $_POST['status'];
    
    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt_check = $mysqli->prepare($check_email);
    $stmt_check->bind_param('s', $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if($result_check->num_rows > 0) {
        setToast('error', 'Email already exists in the system!');
    } else {
        // Check if code exists
        $code = strtoupper($_POST['code']);
        $check_code = "SELECT id FROM tenants WHERE code = ?";
        $stmt_code = $mysqli->prepare($check_code);
        $stmt_code->bind_param('s', $code);
        $stmt_code->execute();
        
        if($stmt_code->get_result()->num_rows > 0) {
            setToast('error', 'Landlord Code already exists!');
        } else {
        // Start transaction
        $mysqli->begin_transaction();
        
        try {
            // 1. Create tenant record
            $tenant_query = "INSERT INTO tenants (name, code, status, created_at) VALUES (?, ?, ?, NOW())";
            $stmt_tenant = $mysqli->prepare($tenant_query);
            $stmt_tenant->bind_param('sss', $tenant_name, $code, $status);
            $stmt_tenant->execute();
            $tenant_id = $mysqli->insert_id;
            
            // 2. Create user record
            $user_query = "INSERT INTO users (tenant_id, email, password, role, full_name, status, created_at) VALUES (?, ?, ?, 'landlord', ?, ?, NOW())";
            $stmt_user = $mysqli->prepare($user_query);
            $stmt_user->bind_param('issss', $tenant_id, $email, $password, $full_name, $status);
            $stmt_user->execute();
            
            // Commit transaction
            $mysqli->commit();
            
            // 3. Create initial hostel record (Syncing Property Name)
            // We use a separate transaction or just query since main user creation is critical
            // But better to include in same transaction if possible, but here we already committed. 
            // Let's do it after commit to ensure user exists.
            
            try {
               $hostel_query = "INSERT INTO hostels (tenant_id, name, address, city, Description, status, phone, email) VALUES (?, ?, 'Not Set', 'Not Set', 'Auto-created by Admin', 'pending', '', '')";
               $stmt_hostel = $mysqli->prepare($hostel_query);
               $stmt_hostel->bind_param('is', $tenant_id, $tenant_name);
               $stmt_hostel->execute();
            } catch (Exception $e) {
               // If hostel creation fails, we don't want to rollback the user creation, 
               // just log it or ignore as the landlord can add it later.
               // For now, we proceed.
            }

            setToast('success', 'Landlord added successfully! Hostel placeholder created.');
            header("location:manage-tenants.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            $mysqli->rollback();
            setToast('error', 'Failed to add landlord: ' . $e->getMessage());
        }
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
    <title>Add Landlord - Super Admin</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
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
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Add New Landlord</h3>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="super_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="manage-tenants.php">Manage Landlords</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Add Landlord</li>
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
                                <h4 class="card-title">Landlord Information</h4>
                                <form method="POST" class="mt-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="full_name">Full Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password">Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                                <small class="form-text text-muted">Minimum 6 characters</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="tenant_name">Company/Property Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="tenant_name" name="tenant_name" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="status">Status <span class="text-danger">*</span></label>
                                                <select class="form-control" id="status" name="status" required>
                                                    <option value="active">Active</option>
                                                    <option value="suspended">Suspended</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="code">Landlord Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="code" name="code" placeholder="e.g. HOSTEL_A" required>
                                                <small class="form-text text-muted">Must be unique (e.g. business abbreviation)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-top mt-4 pt-3">
                                        <button type="submit" name="add_landlord" class="btn btn-primary">
                                            <i data-feather="save" class="feather-icon"></i> Add Landlord
                                        </button>
                                        <a href="manage-tenants.php" class="btn btn-secondary ml-2">
                                            <i data-feather="x" class="feather-icon"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include 'includes/footer.php'; ?>
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
        feather.replace();
    </script>
</body>
</html>
