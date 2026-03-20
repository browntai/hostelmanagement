<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    if(!isset($_GET['hostel_id'])){
        header("Location: manage-hostels.php");
        exit();
    }

    $hostel_id = intval($_GET['hostel_id']);

    // Fetch hostel name
    $hQuery = "SELECT name FROM hostels WHERE id=? AND tenant_id=?";
    $hStmt = $mysqli->prepare($hQuery);
    $hStmt->bind_param('ii', $hostel_id, $tenantId);
    $hStmt->execute();
    $hRes = $hStmt->get_result();
    $hostel = $hRes->fetch_assoc();
    $hStmt->close();

    if(!$hostel){
        echo "<script>alert('Error: Hostel not found or unauthorized access.'); window.location.href='manage-hostels.php';</script>";
        exit();
    }

    // --- SYNC LOGIC: Create missing placeholders if they don't exist ---
    $mappingQuery = "SELECT type_id, available_count, price_per_month FROM hostel_type_mapping WHERE hostel_id=?";
    $mStmt = $mysqli->prepare($mappingQuery);
    $mStmt->bind_param('i', $hostel_id);
    $mStmt->execute();
    $mRes = $mStmt->get_result();
    
    while($mRow = $mRes->fetch_assoc()){
        $type_id = $mRow['type_id'];
        $expected = $mRow['available_count'];
        $price = $mRow['price_per_month'];

        // Count current rooms for this type
        $cQuery = "SELECT COUNT(*) as count FROM rooms WHERE hostel_id=? AND room_type_id=?";
        $cStmt = $mysqli->prepare($cQuery);
        $cStmt->bind_param('ii', $hostel_id, $type_id);
        $cStmt->execute();
        $cRes = $cStmt->get_result();
        $cData = $cRes->fetch_assoc();
        $actual = $cData['count'];
        $cStmt->close();

        if($actual < $expected){
            for($i = $actual + 1; $i <= $expected; $i++){
                $seater = 1;
                $temp_room_no = "PENDING-" . $hostel_id . "-" . $type_id . "-" . $i;
                $roomInsert = "INSERT INTO rooms (hostel_id, room_type_id, seater, room_no, fees, tenant_id) VALUES (?, ?, ?, ?, ?, ?)";
                $riStmt = $mysqli->prepare($roomInsert);
                $riStmt->bind_param('iiiidi', $hostel_id, $type_id, $seater, $temp_room_no, $price, $tenantId);
                $riStmt->execute();
                $riStmt->close();
            }
        }
    }
    $mStmt->close();
    // --- END SYNC LOGIC ---

    // Handle updates
    if(isset($_POST['update_rooms'])){
        foreach($_POST['room_no'] as $room_id => $room_no){
            $room_id = intval($room_id);
            $seater = intval($_POST['seater'][$room_id]);
            
            $uQuery = "UPDATE rooms SET room_no=?, seater=? WHERE id=? AND hostel_id=? AND tenant_id=?";
            $uStmt = $mysqli->prepare($uQuery);
            $uStmt->bind_param('siiii', $room_no, $seater, $room_id, $hostel_id, $tenantId);
            $uStmt->execute();
            $uStmt->close();
        }
        echo "<script>alert('Room numbers have been updated successfully!'); window.location.href='manage-hostels.php';</script>";
    }

    // Fetch all rooms for this hostel
    $rQuery = "SELECT rooms.*, hostel_types.type_name 
               FROM rooms 
               LEFT JOIN hostel_types ON rooms.room_type_id = hostel_types.id 
               WHERE rooms.hostel_id=? AND rooms.tenant_id=?
               ORDER BY rooms.room_type_id, rooms.id";
    $rStmt = $mysqli->prepare($rQuery);
    $rStmt->bind_param('ii', $hostel_id, $tenantId);
    $rStmt->execute();
    $rooms = $rStmt->get_result();
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Room Initialization - <?php echo htmlentities($hostel['name']); ?></title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
</head>
<body>
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
                    <div class="col-12 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Room Initialization</h4>
                        <h5>Hostel: <?php echo htmlentities($hostel['name']); ?></h5>
                        <p class="text-muted">Please assign actual room numbers and capacities to the units you created.</p>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered no-wrap">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Unit Type</th>
                                                    <th>Room Number</th>
                                                    <th>Seater (Capacity)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($row = $rooms->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlentities($row['type_name']); ?></td>
                                                    <td>
                                                        <input type="text" name="room_no[<?php echo $row['id']; ?>]" 
                                                               value="<?php echo (strpos($row['room_no'], 'PENDING') !== false) ? '' : htmlentities($row['room_no']); ?>" 
                                                               class="form-control" placeholder="Enter Room Number" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="seater[<?php echo $row['id']; ?>]" 
                                                               value="<?php echo htmlentities($row['seater']); ?>" 
                                                               class="form-control" min="1" required>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="form-actions mt-3">
                                        <div class="text-center">
                                            <button type="submit" name="update_rooms" class="btn btn-success">Save Room Assignments</button>
                                            <a href="manage-hostels.php" class="btn btn-dark">Skip for Now</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php' ?>
        </div>
    </div>
    
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>
</body>
</html>
