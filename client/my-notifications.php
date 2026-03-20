<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();
    include('../includes/notification-helper.php');

    $userId = $_SESSION['id'];

    // Mark all as read when visiting this page
    $mysqli->query("UPDATE notifications SET is_read = 1 WHERE receiver_id = $userId");

    // Fetch all notifications for the user
    $query = "SELECT * FROM notifications WHERE receiver_id = ? ORDER BY created_at DESC";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Notifications - Hostel Management System</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
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
            <?php include '../includes/client-navigation.php'?>
        </header>
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include '../includes/client-sidebar.php'?>
            </div>
        </aside>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">My Notifications</h4>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="page-title text-dark font-weight-black mb-1">Communication Hub</h2>
                        <p class="text-muted">A timeline of system alerts, updates, and direct notifications.</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
                            <div class="card-header bg-gradient-brand py-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-white font-weight-bold"><i data-feather="bell" class="feather-sm mr-2 text-white"></i>My Alerts</h5>
                                    <span class="badge badge-status-confirmed px-3 py-1 rounded-pill small font-weight-black">Real-time pulses</span>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="zero_config" class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1" style="width: 80px;">Index</th>
                                            <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1">Information Segment</th>
                                            <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1 text-right">Chronology</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $cnt = 1;
                                    if($result->num_rows == 0):
                                    ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-5">
                                                <div class="bg-light p-4 rounded-circle d-inline-block mb-3">
                                                    <i data-feather="mail" class="text-muted" style="width: 40px; height: 40px;"></i>
                                                </div>
                                                <h4 class="text-dark font-weight-black">Inbox Clear</h4>
                                                <p class="text-muted">You have no active notifications at this time.</p>
                                            </td>
                                        </tr>
                                    <?php
                                    else:
                                    while($row = $result->fetch_object()):
                                    ?>
                                        <tr>
                                            <td class="px-4 py-4">
                                                <span class="text-muted font-weight-black small"><?php echo str_pad($cnt, 2, '0', STR_PAD_LEFT); ?></span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="d-flex align-items-start">
                                                    <div class="bg-light-brand-subtle p-2 rounded mr-3 text-brand mt-1">
                                                        <i data-feather="info" class="feather-sm"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-dark font-weight-black d-block mb-1"><?php echo htmlentities($row->title); ?></span>
                                                        <p class="text-muted small mb-0 lh-base" style="max-width: 500px;"><?php echo htmlentities($row->message); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <div class="d-inline-block text-right">
                                                    <span class="text-dark font-weight-black d-block mb-0" style="font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($row->created_at)); ?></span>
                                                    <small class="text-brand font-weight-bold"><?php echo date('h:i A', strtotime($row->created_at)); ?></small>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                    $cnt++;
                                    endwhile;
                                    endif;
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../includes/footer.php' ?>
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
    <script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>
    <script>$(".preloader").fadeOut();</script>
</body>
</html>
