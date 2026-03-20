<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $userId = $_SESSION['id'];
    
    // Fetch caretaker's assigned hostel
    $stmt = $mysqli->prepare("SELECT assigned_hostel_id FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $assignedHostelId = $userData['assigned_hostel_id'];

    if(!$assignedHostelId) {
        header("location:dashboard.php"); // Or show an error
        exit();
    }

    // Handle approval/rejection only if the booking belongs to the assigned hostel
    if(isset($_GET['approve'])) {
        $id = intval($_GET['approve']);
        $query = "UPDATE bookings SET booking_status = 'confirmed' WHERE id = ? AND hostel_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $id, $assignedHostelId);
        if($stmt->execute()) {
            header("Location: manage-bookings.php?msg=approved");
            exit();
        }
    }

    if(isset($_GET['reject'])) {
        $id = intval($_GET['reject']);
        $query = "UPDATE bookings SET booking_status = 'rejected' WHERE id = ? AND hostel_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $id, $assignedHostelId);
        if($stmt->execute()) {
            header("Location: manage-bookings.php?msg=rejected");
            exit();
        }
    }

    if(isset($_GET['del'])) {
        $id=intval($_GET['del']);
        $adn="DELETE from bookings where id=? AND hostel_id=?";
            $stmt= $mysqli->prepare($adn);
            $stmt->bind_param('ii',$id, $assignedHostelId);
            $stmt->execute();
            $stmt->close();	   
            header("Location: manage-bookings.php?msg=deleted");
            exit();
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Bookings - Caretaker Portal</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Hostel Booking Management</h4>
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item text-muted active" aria-current="page">Manage Bookings</li>
                                </ol>
                            </nav>
                        </div>
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

                <!-- Search -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="pub-filter-bar shadow-sm">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0"><i data-feather="search" class="text-primary feather-sm"></i></span>
                                </div>
                                <input class="form-control border-0" type="search" id="clientSearch" placeholder="Search by name, room, or status..." aria-label="Search" style="background: transparent;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="clientsGrid">
                    <?php	
                    $ret="SELECT * FROM bookings WHERE hostel_id = ? ORDER BY postingDate DESC";
                    $stmt= $mysqli->prepare($ret);
                    $stmt->bind_param('i', $assignedHostelId);
                    $stmt->execute();
                    $res=$stmt->get_result();

                    if($res->num_rows == 0):
                    ?>
                    <div class="col-12 text-center py-5">
                        <i data-feather="book-open" class="text-muted mb-3" style="width: 60px; height: 60px;"></i>
                        <h4>No Bookings Found</h4>
                        <p class="text-muted">There are no bookings for your assigned hostel.</p>
                    </div>
                    <?php
                    else:
                        while($row=$res->fetch_object()) {
                            $fullName = $row->firstName . ' ' . $row->lastName;
                            $initials = strtoupper(substr($row->firstName, 0, 1) . substr($row->lastName, 0, 1));
                            
                            $statusBadgeClass = 'badge-status-default';
                            $statusIcon = 'help-circle';
                            if($row->booking_status == 'confirmed' || $row->booking_status == 'approved') {
                                $statusBadgeClass = 'badge-status-confirmed';
                                $statusIcon = 'check-circle';
                            } else if($row->booking_status == 'pending') {
                                $statusBadgeClass = 'badge-status-pending';
                                $statusIcon = 'clock';
                            } else if($row->booking_status == 'rejected' || $row->booking_status == 'cancelled') {
                                $statusBadgeClass = 'badge-status-rejected';
                                $statusIcon = 'x-circle';
                            }
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4 client-card-item" 
                          data-name="<?php echo strtolower($fullName); ?>" 
                          data-room="<?php echo strtolower($row->roomno); ?>"
                          data-status="<?php echo strtolower($row->booking_status); ?>">
                        <div class="card pub-card h-100 shadow-md border-0 transition-all hover-shadow">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center mr-3 font-weight-ExtraBold shadow-sm" style="width: 55px; height: 55px; font-size: 1.1rem; border: 2px solid var(--theme-primary-color);">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h5 class="card-title text-dark font-weight-ExtraBold mb-1 text-truncate" style="font-family: var(--theme-highlight-font);">
                                            <?php echo htmlentities($fullName); ?>
                                        </h5>
                                        <span class="badge <?php echo $statusBadgeClass; ?> px-3 py-1 rounded-pill">
                                            <i data-feather="<?php echo $statusIcon; ?>" class="feather-xs mr-1"></i><?php echo ucfirst($row->booking_status); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-light rounded p-3 mb-4 border" style="border-style: dashed !important; border-color: rgba(0,0,0,0.05) !important;">
                                    <div class="row no-gutters mb-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block small text-uppercase font-weight-bold mb-1">Room No</small>
                                            <span class="text-dark font-weight-ExtraBold">#<?php echo $row->roomno; ?></span>
                                        </div>
                                        <div class="col-6 pl-2">
                                            <small class="text-muted d-block small text-uppercase font-weight-bold mb-1">Stay Start</small>
                                            <span class="text-dark font-weight-medium small"><?php echo date('d M Y', strtotime($row->stayfrom)); ?></span>
                                        </div>
                                    </div>
                                    <div class="row no-gutters">
                                        <div class="col-12">
                                            <small class="text-muted d-block small text-uppercase font-weight-bold mb-1">Contact No</small>
                                            <span class="text-secondary font-weight-medium"><i class="fas fa-phone-alt mr-1 small"></i><?php echo $row->contactno; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                    <div class="btn-group shadow-sm rounded overflow-hidden">
                                        <?php if($row->booking_status == 'pending'): ?>
                                            <a href="manage-bookings.php?approve=<?php echo $row->id; ?>" class="btn btn-white text-success btn-sm px-3 py-2" title="Approve" onclick="return confirm('Approve this request?');">
                                                <i data-feather="check" class="feather-sm"></i>
                                            </a>
                                            <a href="manage-bookings.php?reject=<?php echo $row->id; ?>" class="btn btn-white text-warning btn-sm px-3 py-2" title="Reject" onclick="return confirm('Reject this request?');">
                                                <i data-feather="x" class="feather-sm"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="manage-bookings.php?del=<?php echo $row->id; ?>" class="btn btn-white text-danger btn-sm px-3 py-2" title="Delete record" onclick="return confirm('Permanently delete this record?');">
                                            <i data-feather="trash-2" class="feather-sm"></i>
                                        </a>
                                    </div>
                                    <div class="text-right">
                                        <small class="text-muted d-block small">Applied on</small>
                                        <span class="text-muted small"><?php echo date('d M', strtotime($row->postingDate)); ?></span>
                                    </div>
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
            
            $('#clientSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('.client-card-item').filter(function() {
                    var name = $(this).data('name').toString().toLowerCase();
                    var room = $(this).data('room').toString().toLowerCase();
                    var status = $(this).data('status').toString().toLowerCase();
                    $(this).toggle(
                        name.indexOf(value) > -1 || 
                        room.indexOf(value) > -1 ||
                        status.indexOf(value) > -1
                    );
                });
            });
        });
    </script>
</body>
</html>
