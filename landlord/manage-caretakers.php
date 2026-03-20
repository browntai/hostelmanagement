<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    // Handle suspend/activate/delete actions
    if(isset($_GET['action']) && isset($_GET['id'])) {
        $uid = intval($_GET['id']);
        $action = $_GET['action'];

        if($action === 'suspend') {
            $mysqli->query("UPDATE users SET status='suspended' WHERE id=$uid AND tenant_id=$tenantId AND role='caretaker'");
            include_once('../includes/toast-helper.php');
            setToast('warning', 'Caretaker account suspended.');
        } elseif($action === 'activate') {
            $mysqli->query("UPDATE users SET status='active' WHERE id=$uid AND tenant_id=$tenantId AND role='caretaker'");
            include_once('../includes/toast-helper.php');
            setToast('success', 'Caretaker account activated.');
        } elseif($action === 'delete') {
            $mysqli->query("DELETE FROM users WHERE id=$uid AND tenant_id=$tenantId AND role='caretaker'");
            include_once('../includes/toast-helper.php');
            setToast('error', 'Caretaker account deleted.');
        }
        header("Location: manage-caretakers.php");
        exit;
    }

    // Fetch all caretakers for this tenant with their assigned hostel name
    $stmt = $mysqli->prepare("SELECT u.id, u.full_name, u.email, u.contact_no, u.id_no, u.gender, u.status, u.created_at, h.name as hostel_name 
                              FROM users u 
                              LEFT JOIN hostels h ON u.assigned_hostel_id = h.id 
                              WHERE u.role='caretaker' AND u.tenant_id=? 
                              ORDER BY u.created_at DESC");
    $stmt->bind_param('i', $tenantId);
    $stmt->execute();
    $result = $stmt->get_result();
    $caretakers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Manage Caretakers — Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>

    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">

        <header class="topbar" data-navbarbg="skin6">
            <?php include 'includes/navigation.php'?>
        </header>

        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/sidebar.php'?>
            </div>
        </aside>

        <div class="page-wrapper">
            <!-- Breadcrumb -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">
                            <i data-feather="users" class="feather-sm mr-2 text-info"></i>Manage Caretakers
                        </h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Manage Caretakers</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="col-5 align-self-center text-right">
                        <a href="register-caretaker.php" class="btn btn-info btn-sm shadow-sm">
                            <i data-feather="user-plus" class="feather-xs mr-1"></i> Register Caretaker
                        </a>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-gradient-info py-3 d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 text-white">
                                    <i data-feather="users" class="feather-sm mr-2"></i>
                                    Caretaker Accounts
                                    <span class="badge badge-light text-info ml-2"><?php echo count($caretakers); ?></span>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if(empty($caretakers)): ?>
                                    <div class="text-center py-5 text-muted">
                                        <i data-feather="user-x" style="width:48px;height:48px;" class="mb-3"></i>
                                        <p class="mb-2 font-weight-medium">No caretakers registered yet.</p>
                                        <a href="register-caretaker.php" class="btn btn-sm btn-info mt-2">
                                            <i data-feather="user-plus" class="feather-xs mr-1"></i> Register First Caretaker
                                        </a>
                                    </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-0" id="caretaker-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Full Name</th>
                                                <th>Assigned Hostel</th>
                                                <th>Email</th>
                                                <th>Contact</th>
                                                <th>Gender</th>
                                                <th>ID No.</th>
                                                <th>Status</th>
                                                <th>Registered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach($caretakers as $c): ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm mr-2 rounded-circle bg-info d-flex align-items-center justify-content-center text-white font-weight-bold" style="width:32px;height:32px;font-size:13px;">
                                                            <?php echo strtoupper(substr($c['full_name'], 0, 1)); ?>
                                                        </div>
                                                        <?php echo htmlspecialchars($c['full_name']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-primary text-primary border">
                                                        <i data-feather="home" class="feather-xs mr-1"></i>
                                                        <?php echo htmlspecialchars($c['hostel_name'] ?? 'None'); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($c['email']); ?></td>
                                                <td><?php echo htmlspecialchars($c['contact_no'] ?? '—'); ?></td>
                                                <td><?php echo htmlspecialchars($c['gender'] ?? '—'); ?></td>
                                                <td><code><?php echo htmlspecialchars($c['id_no'] ?? '—'); ?></code></td>
                                                <td>
                                                    <?php if($c['status'] === 'active'): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Suspended</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                                                <td>
                                                    <?php if($c['status'] === 'active'): ?>
                                                        <a href="manage-caretakers.php?action=suspend&id=<?php echo $c['id']; ?>"
                                                           class="btn btn-warning btn-sm"
                                                           onclick="return confirm('Suspend this caretaker?')">
                                                            <i data-feather="slash" class="feather-xs"></i> Suspend
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="manage-caretakers.php?action=activate&id=<?php echo $c['id']; ?>"
                                                           class="btn btn-success btn-sm">
                                                            <i data-feather="check" class="feather-xs"></i> Activate
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="manage-caretakers.php?action=delete&id=<?php echo $c['id']; ?>"
                                                       class="btn btn-danger btn-sm ml-1"
                                                       onclick="return confirm('Permanently delete this caretaker account?')">
                                                        <i data-feather="trash-2" class="feather-xs"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
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
</body>

</html>
