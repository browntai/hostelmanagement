<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();
    
    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    // Handle marking room as booked
    if(isset($_GET['mark_booked']) && isset($_GET['room_id'])) {
        $roomId = intval($_GET['room_id']);
        $updateSql = "UPDATE rooms SET status = 'booked' WHERE id = ?";
        $stmt = $mysqli->prepare($updateSql);
        $stmt->bind_param('i', $roomId);
        if($stmt->execute()) {
            include_once('../includes/toast-helper.php');
            setToast('success', 'Room marked as booked successfully!');
        }
        $stmt->close();
        header("Location: manage-rooms.php");
        exit();
    }

    // Handle marking room as available
    if(isset($_GET['mark_available']) && isset($_GET['room_id'])) {
        $roomId = intval($_GET['room_id']);
        $updateSql = "UPDATE rooms SET status = 'available' WHERE id = ?";
        $stmt = $mysqli->prepare($updateSql);
        $stmt->bind_param('i', $roomId);
        if($stmt->execute()) {
            include_once('../includes/toast-helper.php');
            setToast('success', 'Room marked as available!');
        }
        $stmt->close();
        header("Location: manage-rooms.php");
        exit();
    }

    if(isset($_GET['del']))
    {
        $id=intval($_GET['del']);
        $adn="DELETE from rooms where id=?";
            $stmt= $mysqli->prepare($adn);
            $stmt->bind_param('i',$id);
            $stmt->execute();
            $stmt->close();	   
            echo "<script>alert('Record has been deleted');</script>" ;
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Room Management - Hostel Management System</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Room Management</h4>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <!-- Search and Add -->
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-8">
                        <div class="customize-input">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0 shadow-sm"><i data-feather="search" class="feather-sm"></i></span>
                                </div>
                                <input class="form-control border-0 shadow-sm" type="search" id="roomSearch" placeholder="Search my rooms by number or status..." aria-label="Search">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 text-right mt-3 mt-md-0">
                        <a href="add-rooms.php" class="btn btn-primary shadow-sm px-4">
                            <i data-feather="plus-circle" class="feather-sm mr-2"></i>Add New Room
                        </a>
                    </div>
                </div>

                <!-- Data Table Starts -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="roomsTable" class="table table-striped table-bordered no-wrap">
                                <thead>
                                    <tr>
                                        <th>Hostel</th>
                                        <th>Room No.</th>
                                        <th>Seater</th>
                                        <th>Monthly Fees</th>
                                        <th>Status</th>
                                        <th>Current Occupant</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php	
                                        $aid=$_SESSION['id'];
                                        $tenantWhere = $tm->getTenantWhereClause('rooms');
                                        $ret="SELECT rooms.*, tenants.name as tenant_name, hostels.name as hostel_name,
                                              (SELECT CONCAT(firstName, ' ', lastName) FROM bookings WHERE bookings.roomno = rooms.room_no AND bookings.tenant_id = rooms.tenant_id AND bookings.booking_status IN ('confirmed', 'pending') ORDER BY postingDate DESC LIMIT 1) as booked_by
                                              FROM rooms 
                                              LEFT JOIN tenants ON rooms.tenant_id = tenants.id
                                              LEFT JOIN hostels ON rooms.hostel_id = hostels.id
                                              $tenantWhere
                                              ORDER BY CASE WHEN hostels.name IS NULL THEN 1 ELSE 0 END, hostels.name, rooms.room_no";
                                        $stmt= $mysqli->prepare($ret) ;
                                        $stmt->execute() ;
                                        $res=$stmt->get_result();
                                        
                                        while($row=$res->fetch_object()) {
                                            $roomStatus = $row->status ?? 'available';
                                            $statusColor = ($roomStatus == 'booked') ? 'danger' : 'success';
                                            $statusIcon = ($roomStatus == 'booked') ? 'lock' : 'check';
                                    ?>
                                    <tr>
                                        <td><?php echo $row->hostel_name ? htmlentities($row->hostel_name) : '<span class="text-muted">Unassigned</span>'; ?></td>
                                        <td class="font-weight-medium text-dark"><?php echo $row->room_no; ?></td>
                                        <td><?php echo $row->seater; ?> Seater</td>
                                        <td>KSh <?php echo number_format($row->fees, 0); ?></td>
                                        <td>
                                            <span class="badge badge-light-<?php echo $statusColor; ?> text-<?php echo $statusColor; ?> px-2 py-1">
                                                <i data-feather="<?php echo $statusIcon; ?>" class="feather-xs mr-1"></i><?php echo ucfirst($roomStatus); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($row->booked_by): ?>
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="user" class="feather-xs text-muted mr-1"></i>
                                                    <small class="text-dark font-weight-medium text-truncate" style="max-width: 120px;"><?php echo htmlentities($row->booked_by); ?></small>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit-room.php?id=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-info btn-sm" title="Edit">
                                                    <i data-feather="edit-2" class="feather-xs"></i>
                                                </a>
                                                <?php if($roomStatus == 'available'): ?>
                                                    <a href="manage-rooms.php?mark_booked=1&room_id=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-warning btn-sm" title="Mark as Booked" onclick="return confirm('Mark this room as booked?');">
                                                        <i data-feather="lock" class="feather-xs"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="manage-rooms.php?mark_available=1&room_id=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-success btn-sm" title="Mark as Available" onclick="return confirm('Mark this room as available?');">
                                                        <i data-feather="unlock" class="feather-xs"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="manage-rooms.php?del=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-danger btn-sm" title="Delete" onclick="return confirm('Do you want to delete?');">
                                                    <i data-feather="trash-2" class="feather-xs"></i>
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
            $('#roomsTable').DataTable({
                "language": {
                    "search": "Quick Search: ",
                    "lengthMenu": "Show _MENU_ rooms"
                },
                "order": [[ 0, "asc" ], [ 1, "asc" ]]
            });
        });
    </script>
</body>
</html>
