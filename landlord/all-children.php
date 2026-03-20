<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $uid = $_SESSION['id'];

    // Security Check: Is daycare enabled for any of this landlord's hostels?
    $any_daycare_enabled = false;
    $check_q = "SELECT COUNT(*) as cnt FROM hostel_services WHERE is_enabled=1 AND service_key='daycare' AND hostel_id IN (SELECT id FROM hostels WHERE tenant_id=?)";
    $c_stmt = $mysqli->prepare($check_q);
    $c_stmt->bind_param('i', $uid);
    $c_stmt->execute();
    $c_res = $c_stmt->get_result();
    if($c_row = $c_res->fetch_object()){
        $any_daycare_enabled = ($c_row->cnt > 0);
    }
    if(!$any_daycare_enabled) {
        header("Location: dashboard.php");
        exit();
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Children Directory - Landlord Dashboard</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
</head>
<body>
    <div class="preloader">
        <div class="lds-ripple"><div class="lds-pos"></div><div class="lds-pos"></div></div>
    </div>
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6"><?php include 'includes/navigation.php'?></header>
        <aside class="left-sidebar" data-sidebarbg="skin6"><div class="scroll-sidebar"><?php include 'includes/sidebar.php'?></div></aside>
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Children Directory</h4>
                        <nav aria-label="breadcrumb"><ol class="breadcrumb m-0 p-0 text-muted"><li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Registered Children</li></ol></nav>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title">All Registered Children</h4>
                                <h6 class="card-subtitle mb-4 text-muted">A comprehensive list of all children registered by tenants in your properties.</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover no-wrap">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>Child Name</th>
                                                <th>Age</th>
                                                <th>Parent / Tenant</th>
                                                <th>Medical Information</th>
                                                <th>Registered Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch children of tenants in hostels owned by this landlord
                                            $query = "SELECT c.*, u.full_name as parent_name, u.email as parent_email 
                                                      FROM children c 
                                                      JOIN users u ON c.parent_id = u.id 
                                                      JOIN bookings b ON u.email = b.emailid 
                                                      JOIN hostels h ON b.hostel_id = h.id 
                                                      WHERE h.tenant_id = ? 
                                                      GROUP BY c.id 
                                                      ORDER BY c.full_name ASC";
                                            $stmt = $mysqli->prepare($query);
                                            $stmt->bind_param('i', $uid);
                                            $stmt->execute();
                                            $res = $stmt->get_result();

                                            if($res->num_rows == 0):
                                            ?>
                                            <tr><td colspan="5" class="text-center py-4 text-muted">No children registered in your properties.</td></tr>
                                            <?php
                                            else:
                                                while($row = $res->fetch_object()):
                                            ?>
                                            <tr>
                                                <td><div class="d-flex align-items-center"><div class="rounded-circle bg-light-primary text-primary px-3 py-2 mr-3"><i class="fas fa-child"></i></div><div class="font-weight-medium text-dark"><?php echo htmlentities($row->full_name); ?></div></div></td>
                                                <td><?php echo $row->age; ?> Years</td>
                                                <td>
                                                    <div class="font-weight-medium mb-0"><?php echo htmlentities($row->parent_name); ?></div>
                                                    <small class="text-muted"><?php echo htmlentities($row->parent_email); ?></small>
                                                </td>
                                                <td>
                                                    <?php if($row->medical_info): ?>
                                                        <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlentities($row->medical_info); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">None disclosed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($row->created_at)); ?></td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../includes/footer.php' ?>
        </div>
    </div>
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>
    <script>$(".preloader").fadeOut();</script>
</body>
</html>
