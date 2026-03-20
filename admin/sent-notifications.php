<?php
session_start();
include('../includes/dbconn.php');
include('../includes/check-login.php');
check_login();

// Fetch notifications sent by the current user
$currentUserId = $_SESSION['id'];
$query = "SELECT n.*, u.full_name as receiver_name, u.email as receiver_email 
          FROM notifications n 
          LEFT JOIN users u ON n.receiver_id = u.id 
          WHERE n.sender_id = ? 
          ORDER BY n.created_at DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $currentUserId);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sent Notifications - Hostel Management</title>
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
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
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Sent Notifications</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page">Sent Notifications</li>
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
                                <h4 class="card-title">Outbox</h4>
                                <h6 class="card-subtitle">History of notifications you have sent to others.</h6>
                                <div class="table-responsive">
                                    <table id="sent_notifications_table" class="table table-striped table-hover table-bordered no-wrap">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Recipient</th>
                                                <th>Title</th>
                                                <th>Message</th>
                                                <th>Date Sent</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($row = $res->fetch_assoc()) {
                                                $statusClass = $row['is_read'] ? 'secondary' : 'success';
                                                $statusLabel = $row['is_read'] ? 'Read' : 'Delivered';
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlentities($row['receiver_name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlentities($row['receiver_email']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlentities($row['title']); ?></td>
                                                    <td><?php echo htmlentities($row['message']); ?></td>
                                                    <td><?php echo date('M j, Y H:i', strtotime($row['created_at'])); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $statusClass; ?>">
                                                            <?php echo $statusLabel; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php
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
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/extra-libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            $('#sent_notifications_table').DataTable({
                "order": [[3, "desc"]]
            });
            $(".preloader").fadeOut();
        });
    </script>
</body>
</html>
