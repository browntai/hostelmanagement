<?php
session_start();
include('../includes/dbconn.php');

// Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

// Fetch all notifications with receiver details
$query = "SELECT n.*, u.full_name as receiver_name, u.email as receiver_email, u.role as receiver_role 
          FROM notifications n 
          LEFT JOIN users u ON n.receiver_id = u.id 
          ORDER BY n.created_at DESC";
$res = $mysqli->query($query);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Notifications - Super Admin</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
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
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">System Wide Notifications</h3>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Manage All Alerts</h4>
                                <div class="table-responsive">
                                    <table id="notifications_table" class="table table-striped table-bordered no-wrap">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Receiver</th>
                                                <th>Role</th>
                                                <th>Title</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while($row = $res->fetch_assoc()) {
                                                $statusClass = $row['is_read'] ? 'secondary' : 'success';
                                                $statusLabel = $row['is_read'] ? 'Read' : 'Unread';
                                                
                                                $roleClass = 'info';
                                                if($row['receiver_role'] == 'admin') $roleClass = 'danger';
                                                if($row['receiver_role'] == 'landlord') $roleClass = 'primary';

                                                echo "<tr>";
                                                echo "<td>".$row['id']."</td>";
                                                echo "<td>".htmlentities($row['receiver_name'])."<br><small>".htmlentities($row['receiver_email'])."</small></td>";
                                                echo "<td><span class='badge badge-".$roleClass."'>".ucfirst($row['receiver_role'])."</span></td>";
                                                echo "<td>".htmlentities($row['title'])."</td>";
                                                echo "<td>".htmlentities($row['message'])."</td>";
                                                echo "<td><span class='badge badge-".$statusClass."'>".$statusLabel."</span></td>";
                                                echo "<td>".date('Y-m-d H:i', strtotime($row['created_at']))."</td>";
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
    <script src="../assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../assets/extra-libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(".preloader").fadeOut();
        $('#notifications_table').DataTable({
            "order": [[ 0, "desc" ]]
        });
    </script>
</body>
</html>
