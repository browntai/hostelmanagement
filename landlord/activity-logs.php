<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login.php');
check_login();
include('../includes/tenant_manager.php');
$tm = new TenantManager($mysqli);
$tenantId = $tm->getCurrentTenantId();

if(isset($_POST['delete_all'])){
    $uid = $_SESSION['id'];
    $query = "DELETE FROM user_activity_logs WHERE user_id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $uid);
    if($stmt->execute()){
        include_once('../includes/toast-helper.php');
        setToast('success', 'Your activity logs have been deleted successfully.');
        header("Location: activity-logs.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Logs - Hostel Management</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
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
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-md-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">My Activity Logs</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page">Activity Logs</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="col-md-5 align-self-center text-md-right mt-3 mt-md-0">
                        <form method="POST" onsubmit="return confirm('Delete all YOUR activity logs? This cannot be undone.');">
                            <button type="submit" name="delete_all" class="btn btn-danger shadow-sm px-4 rounded-pill">
                                <i data-feather="trash-2" class="feather-sm mr-2"></i>Clear My Logs
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <!-- Search -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="customize-input">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0 shadow-sm"><i data-feather="search" class="feather-sm"></i></span>
                                </div>
                                <input class="form-control border-0 shadow-sm" type="search" id="logSearch" placeholder="Search activity by action or details..." aria-label="Search">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-4">Tracking your actions and system interactions.</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover no-wrap custom-table" id="logsTable">
                                        <thead class="bg-light">
                                            <tr class="text-muted font-weight-bold">
                                                <th class="border-0">Action</th>
                                                <th class="border-0">Details</th>
                                                <th class="border-0">IP Address</th>
                                                <th class="border-0">Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Landlord should see their own logs or logs for their tenant users
                                            // Joining with users to filter by tenant_id
                                            $ret = "SELECT logs.* FROM user_activity_logs logs 
                                                   JOIN users u ON logs.user_id = u.id 
                                                   WHERE u.tenant_id = ? 
                                                   ORDER BY logs.created_at DESC LIMIT 500";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->bind_param('i', $tenantId);
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            
                                            if($res->num_rows == 0):
                                            ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <i data-feather="activity" class="text-muted mb-2" style="width: 40px; height: 40px;"></i>
                                                    <p class="text-muted">No activity logs found for your account.</p>
                                                </td>
                                            </tr>
                                            <?php
                                            else:
                                                while ($row = $res->fetch_object()) {
                                            ?>
                                            <tr class="log-row" data-search="<?php echo strtolower($row->action . ' ' . $row->details); ?>">
                                                <td>
                                                    <span class="text-dark font-weight-medium small"><?php echo htmlentities($row->action); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-muted small d-inline-block text-truncate" style="max-width: 400px;" title="<?php echo htmlentities($row->details); ?>">
                                                        <?php echo htmlentities($row->details); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><i data-feather="layers" class="feather-xs mr-1"></i><?php echo htmlentities($row->ip_address); ?></small>
                                                </td>
                                                <td>
                                                    <span class="text-dark small"><?php echo date('d M Y, H:i', strtotime($row->created_at)); ?></span>
                                                </td>
                                            </tr>
                                            <?php
                                                }
                                            endif;
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
    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();
            
            $('#logSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#logsTable tbody tr.log-row').filter(function() {
                    $(this).toggle($(this).data('search').indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>
