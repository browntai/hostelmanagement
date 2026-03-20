<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login.php');
check_login();
include_once('../includes/notification-helper.php');
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Notifications - Hostel Management</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <style>
        .notification-item {
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 4px solid transparent;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .notification-item.unread {
            border-left-color: #17c788;
            background-color: rgba(23, 199, 136, 0.03);
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        .dot-unread { background-color: #17c788; box-shadow: 0 0 5px rgba(23, 199, 136, 0.5); }
        .dot-read { background-color: #e9ecef; }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(23, 199, 136, 0.1);
            color: #17c788;
        }
    </style>
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
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">My Notifications</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0 text-muted small">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Inbox</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-0">
                                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                                    <h6 class="card-title mb-0">Notifications History</h6>
                                    <span class="badge badge-light-primary text-primary px-3 rounded-pill">Recent activity first</span>
                                </div>
                                <div class="notification-feed">
                                    <?php
                                    $uid = $_SESSION['id'];
                                    $ret = "SELECT * FROM notifications WHERE receiver_id = ? ORDER BY created_at DESC";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->bind_param('i', $uid);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    
                                    if($res->num_rows == 0):
                                    ?>
                                    <div class="text-center py-5">
                                        <i data-feather="bell-off" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                        <h5 class="text-muted">No notifications yet.</h5>
                                    </div>
                                    <?php
                                    else:
                                        while ($row = $res->fetch_object()) {
                                            $isUnread = ($row->is_read == 0);
                                            if ($isUnread) {
                                                markAsRead($row->id);
                                            }
                                    ?>
                                    <div class="notification-item p-4 border-bottom <?php echo $isUnread ? 'unread' : ''; ?>">
                                        <div class="d-flex align-items-start">
                                            <div class="notification-icon mt-1 mr-3">
                                                <i data-feather="<?php echo $isUnread ? 'bell' : 'mail'; ?>" class="feather-sm"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <h6 class="text-dark font-weight-bold mb-0 mr-2"><?php echo htmlentities($row->title); ?></h6>
                                                    <span class="status-dot <?php echo $isUnread ? 'dot-unread' : 'dot-read'; ?>" title="<?php echo $isUnread ? 'New' : 'Read'; ?>"></span>
                                                </div>
                                                <p class="text-muted mb-2"><?php echo htmlentities($row->message); ?></p>
                                                <div class="d-flex align-items-center text-muted small">
                                                    <i data-feather="calendar" class="feather-xs mr-1"></i>
                                                    <span><?php echo date('d M Y, H:i', strtotime($row->created_at)); ?></span>
                                                    <span class="mx-2">•</span>
                                                    <i data-feather="clock" class="feather-xs mr-1"></i>
                                                    <span><?php echo date('h:i A', strtotime($row->created_at)); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                        }
                                    endif;
                                    ?>
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
        });
    </script>
</body>
</html>
