<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    include('../includes/hostel-helper.php');

    // Handle delete action
    if(isset($_GET['del'])){
        $id = intval($_GET['del']);
        
        // Delete hostel (if super admin, don't filter by tenant_id)
        if ($tm->isSuperAdmin()) {
            $query = "DELETE FROM hostels WHERE id=?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('i', $id);
        } else {
            $query = "DELETE FROM hostels WHERE id=? AND tenant_id=?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('ii', $id, $tenantId);
        }
        $stmt->execute();
        
        echo "<script>alert('Hostel deleted successfully');</script>";
        echo "<script>window.location.href='manage-hostels.php';</script>";
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Manage Hostels - Hostel Management System</title>
    <link href="../assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
    
    <style>
        .hostel-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
        }
        .status-approved { background: #28a745; color: white; }
        .status-pending { background: #ffc107; color: #000; }
        .status-rejected { background: #dc3545; color: white; }
        .status-inactive { background: #6c757d; color: white; }
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Manage Hostels</h4>
                    </div>
                    <div class="col-5 align-self-center">
                        <div class="customize-input float-right">
                            <a href="add-hostel.php" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Add New Hostel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">All Hostels</h4>
                                
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-md-6 col-lg-8">
                        <div class="customize-input">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0 shadow-sm"><i data-feather="search" class="feather-sm"></i></span>
                                </div>
                                <input class="form-control border-0 shadow-sm" type="search" id="hostelSearch" placeholder="Search hostels by name or location..." aria-label="Search">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" id="hostelGrid">
                    <?php
                    $tenantWhere = $tm->getTenantWhereClause();
                    $query = "SELECT * FROM hostels $tenantWhere ORDER BY created_at DESC";
                    $res = $mysqli->query($query);
                    
                    if($res->num_rows == 0):
                    ?>
                    <div class="col-12 text-center py-5">
                        <i data-feather="home" class="text-muted mb-3" style="width: 60px; height: 60px;"></i>
                        <h4>No Hostels Found</h4>
                        <p class="text-muted">You haven't added any hostels yet.</p>
                        <a href="add-hostel.php" class="btn btn-primary mt-3">Add First Hostel</a>
                    </div>
                    <?php
                    else:
                        while($row = $res->fetch_object()):
                            $featured_image = getHostelFeaturedImage($mysqli, $row->id);
                            
                            // 1. Get Actual Room Count
                            $room_count_query = "SELECT COUNT(*) as count FROM rooms WHERE hostel_id = ?";
                            $stmt2 = $mysqli->prepare($room_count_query);
                            $stmt2->bind_param('i', $row->id);
                            $stmt2->execute();
                            $room_result = $stmt2->get_result();
                            $room_data = $room_result->fetch_object();
                            $actual_room_count = $room_data ? $room_data->count : 0;
                            $stmt2->close();

                            // 2. Get Expected Room Count from mapping
                            $expected_query = "SELECT SUM(available_count) as expected FROM hostel_type_mapping WHERE hostel_id = ?";
                            $stmt3 = $mysqli->prepare($expected_query);
                            $stmt3->bind_param('i', $row->id);
                            $stmt3->execute();
                            $expected_result = $stmt3->get_result();
                            $expected_data = $expected_result->fetch_object();
                            $expected_room_count = $expected_data ? intval($expected_data->expected) : 0;
                            $stmt3->close();

                            $is_uninitialized = ($expected_room_count > $actual_room_count);
                            $statusClass = ($row->status == 'approved') ? 'success' : (($row->status == 'pending') ? 'warning' : 'danger');
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4 hostel-card-item" data-name="<?php echo strtolower($row->name); ?>" data-loc="<?php echo strtolower($row->city . ' ' . $row->address); ?>">
                        <div class="card h-100 shadow-sm border-0 transition-all hover-shadow">
                            <div class="position-relative">
                                <img src="../<?php echo $featured_image; ?>" class="card-img-top" alt="Hostel" style="height: 180px; object-fit: cover;" onerror="this.src='../assets/images/hostel-placeholder.jpg'">
                                <div class="position-absolute" style="top: 15px; right: 15px;">
                                    <span class="badge badge-<?php echo $statusClass; ?> px-3 py-2 shadow-sm">
                                        <?php echo ucfirst($row->status); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-1">
                                    <h5 class="card-title text-dark font-weight-bold mb-0 text-truncate mr-2"><?php echo htmlentities($row->name); ?></h5>
                                    <?php if($is_uninitialized): ?>
                                        <span class="badge badge-light-danger text-danger border-danger small px-2">Uninitialized</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted small mb-3"><i data-feather="map-pin" class="feather-xs mr-1"></i> <?php echo htmlentities($row->city . ', ' . $row->address); ?></p>
                                
                                <div class="bg-light rounded p-3 mb-3">
                                    <div class="row text-center">
                                        <div class="col-6 border-right">
                                            <small class="text-muted d-block text-uppercase small">Rooms</small>
                                            <h5 class="mb-0 font-weight-bold"><?php echo $actual_room_count; ?> <small class="text-muted">/ <?php echo $expected_room_count; ?></small></h5>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block text-uppercase small">Support</small>
                                            <h6 class="mb-0 font-weight-medium small"><?php echo htmlentities($row->phone); ?></h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                    <div class="btn-group shadow-sm">
                                        <a href="edit-hostel.php?id=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-info btn-sm" title="Edit">
                                            <i data-feather="edit-2" class="feather-xs"></i>
                                        </a>
                                        <a href="hostel-details.php?id=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-success btn-sm" title="View Details">
                                            <i data-feather="eye" class="feather-xs"></i>
                                        </a>
                                        <a href="manage-hostels.php?del=<?php echo $row->id; ?>" class="btn btn-light bg-white border text-danger btn-sm" title="Delete" 
                                           onclick="return confirm('Are you sure you want to delete this hostel? All related data will be removed.');">
                                            <i data-feather="trash-2" class="feather-xs"></i>
                                        </a>
                                    </div>
                                    
                                    <?php if($is_uninitialized): ?>
                                    <a href="init-hostel-rooms.php?hostel_id=<?php echo $row->id; ?>" class="btn btn-sm btn-warning shadow-sm px-3">
                                        <i data-feather="tool" class="feather-xs mr-1"></i> Fix Rooms
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    endif; 
                    ?>
                </div>
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
    <script src="../dist/js/pages/datatable/datatable-basic.init.js"></script>
    
    <script>
        $(".preloader").fadeOut();
        $('#hostelSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('.hostel-card-item').filter(function() {
                var name = $(this).data('name');
                var loc = $(this).data('loc');
                $(this).toggle(name.indexOf(value) > -1 || loc.indexOf(value) > -1);
            });
        });
    </script>
</body>

</html>
