<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    include('../includes/ai-helper.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    if(isset($_GET['approve'])) {
        $id = intval($_GET['approve']);
        $query = "UPDATE bookings SET booking_status = 'confirmed' WHERE id = ? AND tenant_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $id, $tenantId);
        if($stmt->execute()) {
            header("Location: manage-clients.php?msg=approved");
            exit();
        }
    }

    if(isset($_GET['reject'])) {
        $id = intval($_GET['reject']);
        $query = "UPDATE bookings SET booking_status = 'rejected' WHERE id = ? AND tenant_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $id, $tenantId);
        if($stmt->execute()) {
            header("Location: manage-clients.php?msg=rejected");
            exit();
        }
    }

    if(isset($_GET['del'])) {
        $id=intval($_GET['del']);
        $adn="DELETE from bookings where id=? AND tenant_id=?";
            $stmt= $mysqli->prepare($adn);
            $stmt->bind_param('ii',$id, $tenantId);
            $stmt->execute();
            $stmt->close();	   
            header("Location: manage-clients.php?msg=deleted");
            exit();
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Clients - Hostel Management System</title>
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Hostel Client Management</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page">Manage Clients</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="col-5 align-self-center text-right">
                        <a href="register-client.php" class="btn btn-info btn-sm shadow-sm">
                            <i data-feather="user-plus" class="feather-xs mr-1"></i> Add Client
                        </a>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i data-feather="check-circle" class="feather-sm mr-2"></i>
                    <strong>Updated!</strong> The record has been successfully <?php echo htmlspecialchars($_GET['msg']); ?>.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>

                <!-- Data Table Starts -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="clientsTable" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Hostel</th>
                                        <th>Room</th>
                                        <th>Trust Score</th>
                                        <th>Contact</th>
                                        <th>Date Joined</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php	
                                    $tenantWhere = $tm->getTenantWhereClause('bookings');
                                    $ret="SELECT bookings.*, hostels.name as hostel_name, tenants.name as tenant_name
                                          FROM bookings 
                                          LEFT JOIN hostels ON bookings.hostel_id = hostels.id 
                                          LEFT JOIN tenants ON bookings.tenant_id = tenants.id
                                          $tenantWhere
                                          ORDER BY bookings.stayfrom DESC";
                                    
                                    $stmt= $mysqli->prepare($ret);
                                    $stmt->execute();
                                    $res=$stmt->get_result();

                                    while($row=$res->fetch_object()) {
                                        $fullName = $row->firstName . ' ' . $row->lastName;
                                        
                                        $statusBadgeClass = 'badge-status-default';
                                        $statusIcon = 'help-circle';
                                        if($row->booking_status == 'confirmed') {
                                            $statusBadgeClass = 'badge-status-confirmed';
                                            $statusIcon = 'check-circle';
                                        } else if($row->booking_status == 'approved') {
                                            $statusBadgeClass = 'badge-status-approved';
                                            $statusIcon = 'check-circle';
                                        } else if($row->booking_status == 'pending') {
                                            $statusBadgeClass = 'badge-status-pending';
                                            $statusIcon = 'clock';
                                        } else if($row->booking_status == 'rejected') {
                                            $statusBadgeClass = 'badge-status-rejected';
                                            $statusIcon = 'x-circle';
                                        } else if($row->booking_status == 'cancelled') {
                                            $statusBadgeClass = 'badge-status-cancelled';
                                            $statusIcon = 'x-circle';
                                        }

                                        // AI Trust Score
                                        $clientUserId = null;
                                        $tsStmt = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
                                        $tsStmt->bind_param('s', $row->emailid);
                                        $tsStmt->execute();
                                        $tsRes = $tsStmt->get_result();
                                        if ($tsRow = $tsRes->fetch_object()) $clientUserId = $tsRow->id;
                                        $trust = null;
                                        if ($clientUserId) {
                                            $trust = calculateTrustScore($mysqli, $clientUserId);
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="clients-profile.php?id=<?php echo $row->id; ?>" class="text-dark font-weight-medium text-truncate d-block" style="max-width: 150px;"><?php echo htmlentities($fullName); ?></a>
                                        </td>
                                        <td><?php echo $row->hostel_name ? htmlentities($row->hostel_name) : 'N/A'; ?></td>
                                        <td>#<?php echo $row->roomno; ?></td>
                                        <td>
                                            <?php if($trust): ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge text-white px-2 py-1 mr-2" style="background:<?php echo $trust['gradeColor']; ?>; min-width: 30px;"><?php echo $trust['grade']; ?></span>
                                                    <small class="font-weight-medium text-dark"><?php echo $trust['score']; ?>%</small>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $row->contactno; ?></td>
                                        <td><?php echo date('d M Y', strtotime($row->stayfrom)); ?></td>
                                        <td>
                                            <span class="badge <?php echo $statusBadgeClass; ?> px-2 py-1">
                                                <i data-feather="<?php echo $statusIcon; ?>" class="feather-xs mr-1"></i><?php echo ucfirst($row->booking_status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="clients-profile.php?id=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-info btn-sm px-2" title="View Full Profile">
                                                    <i data-feather="eye" class="feather-sm"></i>
                                                </a>
                                                <?php if($row->booking_status == 'pending'): ?>
                                                    <a href="manage-clients.php?approve=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-success btn-sm px-2" title="Approve" onclick="return confirm('Approve this request?');">
                                                        <i data-feather="check" class="feather-sm"></i>
                                                    </a>
                                                    <a href="manage-clients.php?reject=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-warning btn-sm px-2" title="Reject" onclick="return confirm('Reject this request?');">
                                                        <i data-feather="x" class="feather-sm"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="manage-clients.php?del=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-danger btn-sm px-2" title="Delete record" onclick="return confirm('Permanently delete this record?');">
                                                    <i data-feather="trash-2" class="feather-sm"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Data Table Ends -->
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
    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();
            $('#clientsTable').DataTable({
                "language": {
                    "search": "Quick Search: ",
                    "lengthMenu": "Show _MENU_ clients"
                },
                "order": [[ 5, "desc" ]]
            });
        });
    </script>
</body>
</html>
