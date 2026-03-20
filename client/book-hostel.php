<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();
    include('../includes/hostel-helper.php');
    
    // Get hostel ID if provided
    $selected_hostel = null;
    if(isset($_GET['hostel_id'])){
        $selected_hostel = intval($_GET['hostel_id']);
        $hostel_info = getHostelById($mysqli, $selected_hostel);
    }
    
    if(isset($_POST['submit'])){
        $hostel_id = isset($_POST['hostel_id']) ? intval($_POST['hostel_id']) : null;
        $roomno=$_POST['room'];
        $tenantId = $_SESSION['tenant_id']; // Default fallback
        $hostelQuery = "SELECT tenant_id FROM hostels WHERE id=?";
        $hSt = $mysqli->prepare($hostelQuery);
        $hSt->bind_param('i', $hostel_id);
        $hSt->execute();
        $hRes = $hSt->get_result();
        if($hRow = $hRes->fetch_object()){
             $tenantId = $hRow->tenant_id;
        }

        // Fetch seater (capacity) for this room
        // Note: Rooms are linked to hostel_id AND tenant_id, but usually primarily hostel_id. 
        // We use the fetched tenantId to be consistent.
        $hQuery = "SELECT seater FROM rooms WHERE room_no=? AND hostel_id=?";
        $hStmt = $mysqli->prepare($hQuery);
        $hStmt->bind_param('si', $roomno, $hostel_id);
        $hStmt->execute();
        $hRes = $hStmt->get_result();
        $capacity = 0;
        if($hRow = $hRes->fetch_assoc()) {
            $capacity = $hRow['seater'];
        }

        // Check if room is full
        $countQuery = "SELECT count(*) FROM bookings WHERE roomno=? AND hostel_id=?";
        $countStmt = $mysqli->prepare($countQuery);
        $countStmt->bind_param('ii', $roomno, $hostel_id);
        $countStmt->execute();
        $countStmt->bind_result($currentCount);
        $countStmt->fetch();
        $countStmt->close();

        if ($currentCount >= $capacity && $capacity > 0) {
            include_once('../includes/toast-helper.php');
            setToast('error', 'Error: This room is already full!');
            header("Location: book-hostel.php".($selected_hostel?"?hostel_id=$selected_hostel":""));
            exit();
        } else {
            $seater=$_POST['seater'];
            $feespm=$_POST['fpm'];
            // $foodstatus removed
            $stayfrom=$_POST['stayf'];
            $duration=$_POST['duration'];
            $fname=$_POST['fname'];
            $mname=$_POST['mname'];
            $lname=$_POST['lname'];
            $gender=$_POST['gender'];
            $contactno=$_POST['contact'];
            $emailid=$_POST['email'];
            $emcntno=$_POST['econtact'];
            $gurname=$_POST['gname'];
            $gurrelation=$_POST['grelation'];
            $gurcntno=$_POST['gcontact'];
            $caddress=$_POST['address'];
            $ccity=$_POST['city'];
            $cpincode=$_POST['pincode'];
            $paddress=$_POST['paddress'];
            $pcity=$_POST['pcity'];
            $ppincode=$_POST['ppincode'];
            $booking_status = 'pending';
            $query="INSERT into bookings(roomno,seater,feespm,stayfrom,duration,firstName,middleName,lastName,gender,contactno,emailid,egycontactno,guardianName,guardianRelation,guardianContactno,corresAddress,corresCIty,corresPincode,pmntAddress,pmntCity,pmntPincode,tenant_id,hostel_id,booking_status) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            // Updated bind types:
            // roomno(i), seater(i), feespm(i), stayfrom(s), duration(i) -> wait, duration comes from select value="1", "2" etc. It is int. feespm is int? feespm comes from input type text. better safe as s or d? db says int usually.
            // Let's look at the schema again or safer assumption.
            // Old Types: iiisssssssisissississiii (24 chars)
            // Fields:
            // 1. roomno (i)
            // 2. seater (i)
            // 3. feespm (i)
            // 4. stayfrom (s)
            // 5. duration (i) -> treated as string in old 's'? No, old was 'iiis...' -> 5th char is 's'. So duration was string?
            // Let's re-map carefully.
            // 1. roomno -> i
            // 2. seater -> i
            // 3. feespm -> i
            // 4. stayfrom -> s
            // 5. duration -> s (it was s in old one 'iiis...')
            // 6. firstName -> s
            // 7. middleName -> s
            // 8. lastName -> s
            // 9. gender -> s
            // 10. contactno -> i (OLD) -> CHANGE TO s
            // 11. emailid -> s
            // 12. egycontactno -> i (OLD) -> CHANGE TO s
            // 13. guardianName -> s
            // 14. guardianRelation -> s
            // 15. guardianContactno -> i (OLD) -> CHANGE TO s
            // 16. corresAddress -> s
            // 17. corresCIty -> s
            // 18. corresPincode -> i (OLD) -> CHANGE TO s
            // 19. pmntAddress -> s
            // 20. pmntCity -> s
            // 21. pmntPincode -> i (OLD) -> CHANGE TO s
            // 22. tenant_id -> i
            // 23. hostel_id -> i
            // 24. booking_status -> i (OLD) -> CHANGE TO s (It is 'pending')

            // New String Construction:
            // i i i s s s s s s s s s s s s s s s s s s i i s
            // Count: 24 chars.
            $rc=$stmt->bind_param('iiissssssssssssssssssiis',$roomno,$seater,$feespm,$stayfrom,$duration,$fname,$mname,$lname,$gender,$contactno,$emailid,$emcntno,$gurname,$gurrelation,$gurcntno,$caddress,$ccity,$cpincode,$paddress,$pcity,$ppincode,$tenantId,$hostel_id,$booking_status);
            if($stmt->execute()){
                $booking_id = $mysqli->insert_id;
                
                // Notify Landlord
                include_once('../includes/notification-helper.php');
                $l_q = "SELECT id FROM users WHERE tenant_id=? AND role='landlord' LIMIT 1";
                $l_st = $mysqli->prepare($l_q);
                $l_st->bind_param('i', $tenantId);
                $l_st->execute();
                $l_res = $l_st->get_result();
                if($l_row = $l_res->fetch_object()){
                    sendNotification($l_row->id, 'New Booking Request', "Client $fname $lname has requested room $roomno.", $_SESSION['id']);
                }

                // Mark room as booked in real-time
                $updateRoomSql = "UPDATE rooms SET status = 'booked' WHERE room_no = ? AND hostel_id = ?";
                $updateRoomStmt = $mysqli->prepare($updateRoomSql);
                $updateRoomStmt->bind_param('si', $roomno, $hostel_id);
                $updateRoomStmt->execute();
                $updateRoomStmt->close();

                include_once('../includes/log-helper.php');
                include_once('../includes/toast-helper.php');
                logActivity($_SESSION['id'], $_SESSION['login'], 'Client', 'Book Room', "Client booked room $roomno (Booking ID: $booking_id)");
                setToast('success', 'Hostel Booking Successful! Redirecting to payment...');
                header("Location: make-payment.php?booking_id=$booking_id");
            } else {
                include_once('../includes/toast-helper.php');
                setToast('error', 'Error processing booking.');
                header("Location: book-hostel.php");
            }
            exit();
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<!-- By Brown Tom -->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png">
    <title>Hostel Management System</title>
    <!-- Custom CSS -->
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">

    <!-- Script moved to footer -->
    <!-- By Brown Tom -->
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin6">
            <?php include '../includes/client-navigation.php'?>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include '../includes/client-sidebar.php'?>
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <div class="col-12 align-self-center">
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1"><?php echo $selected_hostel ? 'Book Room' : 'Choose a Hostel'; ?></h4>
                </div>
                
                <?php if(!$selected_hostel): ?>
                <div class="row">
                    <div class="col-12 mt-4">
                        <div class="row">
                            <?php 
                            // Fetch approved hostels
                            $query = "SELECT * FROM hostels WHERE status='approved' ORDER BY name ASC";
                            $stmt = $mysqli->prepare($query);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            while($row = $res->fetch_object()):
                                $lowest_price = getHostelLowestPrice($mysqli, $row->id);
                                $available_rooms = getAvailableRoomsCount($mysqli, $row->id);
                                $feat_img = getHostelFeaturedImage($mysqli, $row->id);
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm border-0 rounded-lg overflow-hidden card-hover transition-3d">
                                    <div class="position-relative">
                                        <img src="../<?php echo $feat_img; ?>" class="card-img-top" alt="<?php echo htmlentities($row->name); ?>" style="height: 200px; object-fit: cover;">
                                        <div class="card-img-overlay d-flex flex-column justify-content-end p-0">
                                            <div class="bg-gradient-dark-transparent p-3 text-white">
                                                <h5 class="mb-0 font-weight-bold text-white"><?php echo htmlentities($row->name); ?></h5>
                                                <small class="opacity-8"><i data-feather="map-pin" class="feather-xs mr-1 text-white"></i><?php echo htmlentities($row->city); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div>
                                                <small class="text-brand d-block text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Starting from</small>
                                                <span class="h4 mb-0 text-brand font-weight-bold">KSh <?php echo number_format($lowest_price, 0); ?></span>
                                            </div>
                                            <div class="text-right">
                                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Availability</small>
                                                <span class="badge badge-light-success text-success px-3 py-1 rounded-pill">
                                                    <?php echo $available_rooms; ?> Vacant
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="hostel-amenities-sm mb-4">
                                            <?php 
                                            include_once('../includes/hostel-helper.php');
                                            $amenities = getHostelAmenities($mysqli, $row->id);
                                            $showing = 0;
                                            foreach($amenities as $a): 
                                                if($showing >= 4) break;
                                            ?>
                                            <i class="fas <?php echo $a->icon_class; ?> text-muted mr-3" title="<?php echo $a->amenity_name; ?>"></i>
                                            <?php $showing++; endforeach; ?>
                                        </div>

                                        <a href="book-hostel.php?hostel_id=<?php echo $row->id; ?>" class="btn btn-brand btn-block rounded-pill font-weight-bold shadow-sm py-2">
                                            Select & Continue <i data-feather="arrow-right" class="feather-sm ml-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm border-0 border-left-brand-lg">
                            <div class="card-body py-3 d-flex align-items-center">
                                <div class="bg-light-brand p-3 rounded-circle mr-3">
                                    <i data-feather="home" class="text-brand"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted small text-uppercase font-weight-bold mb-1">Selected Community</h6>
                                    <select class="custom-select border-0 font-weight-bold text-dark p-0 h-auto bg-transparent" id="hostel_select" onchange="loadRoomsForHostel(this.value);" style="font-size: 1.1rem; cursor: pointer;">
                                        <option value="">-- Choose a Hostel --</option>
                                        <?php 
                                        $hostelQuery = "SELECT * FROM hostels WHERE status='approved' ORDER BY name ASC";
                                        $hostelStmt = $mysqli->prepare($hostelQuery);
                                        $hostelStmt->execute();
                                        $hostelRes = $hostelStmt->get_result();
                                        while($hostelRow = $hostelRes->fetch_object()):
                                        ?>
                                        <option value="<?php echo $hostelRow->id; ?>" <?php echo ($selected_hostel == $hostelRow->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlentities($hostelRow->name); ?> (<?php echo htmlentities($hostelRow->city); ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <a href="book-hostel.php" class="btn btn-light rounded-pill btn-sm px-3 ml-2 font-weight-bold">
                                    <i data-feather="search" class="feather-xs mr-1"></i> Change
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                    $uid=$_SESSION['login'];
                    $stmt=$mysqli->prepare("SELECT emailid FROM bookings WHERE emailid=? ");
                    $stmt->bind_param('s',$uid);
                    $stmt->execute();
                    $stmt -> bind_result($email);
                    $rs=$stmt->fetch();
                    $stmt->close();

                    if($rs){ ?>
                    <div class="alert alert-primary alert-dismissible bg-danger text-white border-0 fade show"
                        role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                        </button>
                                <strong>Info: </strong> You have already booked a hostel!
                    </div>
                    <?php }
                    else{
						echo "";
					}			
				?>	

                
                <form name="bookings" id="bookingForm" onSubmit="return valid();" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="hostel_id" value="<?php echo $selected_hostel; ?>">
                    
                    <div class="row">
                        <!-- SECTION 1: Booking Information -->
                        <div class="col-12 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-gradient-brand py-3">
                                    <h5 class="mb-0 text-white font-weight-bold text-white"><i data-feather="calendar" class="feather-sm mr-2 text-white"></i>Lease Configuration</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="text-dark font-weight-medium">Room Selection <span class="text-danger">*</span></label>
                                                <select class="custom-select border-focus-primary" name="room" id="room" onChange="getSeater(this.value);" onBlur="checkAvailability()" required>
                                                    <option value="">Choose your space...</option>
                                                    <?php 
                                                    if($selected_hostel && isset($hostel_info)){
                                                        $hostelTenantId = $hostel_info->tenant_id;
                                                        $query ="SELECT * FROM rooms WHERE tenant_id=? AND hostel_id=? AND status='available' AND room_no NOT IN (SELECT roomno FROM bookings WHERE hostel_id=? AND (booking_status != 'cancelled' AND booking_status != 'rejected'))";
                                                        $stmt2 = $mysqli->prepare($query);
                                                        $stmt2->bind_param('iii',$hostelTenantId,$selected_hostel,$selected_hostel);
                                                        $stmt2->execute();
                                                        $res=$stmt2->get_result();
                                                        while($row=$res->fetch_object()){
                                                            echo "<option value='".$row->room_no."'>Unit ".$row->room_no."</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <div id="room-availability-status" class="small mt-1 font-weight-medium"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="text-dark font-weight-medium">Move-in Date <span class="text-danger">*</span></label>
                                                <input type="date" name="stayf" id="stayf" class="form-control border-focus-primary" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="text-dark font-weight-medium">Booking Period <span class="text-danger">*</span></label>
                                                <select class="custom-select border-focus-primary" id="duration" name="duration" required>
                                                    <option value="">Duration...</option>
                                                    <?php for($i=1; $i<=12; $i++) echo "<option value='$i'>$i Month".($i>1?'s':'')."</option>"; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="text-dark font-weight-medium">Room Type</label>
                                                <input type="text" id="seater" name="seater" class="form-control bg-light border-0 font-weight-bold" readonly placeholder="Waiting Selection">
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-6 mt-3">
                                            <div class="bg-light p-3 rounded-lg border-focus-primary d-flex align-items-center">
                                                <div class="text-left flex-grow-1">
                                                    <h6 class="text-brand small text-uppercase font-weight-bold mb-1">Base Monthly Fee</h6>
                                                    <h4 class="mb-0 text-dark font-weight-bold">KSh <span id="fpm_display">0.00</span></h4>
                                                    <input type="hidden" name="fpm" id="fpm">
                                                </div>
                                                <div class="stat-icon bg-white text-muted">
                                                    <i data-feather="tag" class="feather-sm"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mt-3">
                                            <div class="bg-light-brand-subtle p-3 rounded-lg border-brand d-flex align-items-center shadow-sm">
                                                <div class="text-left flex-grow-1">
                                                    <h6 class="text-brand small text-uppercase font-weight-bold mb-1">Total Lease Commitment</h6>
                                                    <h4 class="mb-0 text-brand font-weight-bold">KSh <input type="text" name="ta" id="ta" class="bg-transparent border-0 font-weight-bold text-brand p-0" style="width: 150px;" value="0.00" readonly></h4>
                                                </div>
                                                <div class="stat-icon bg-brand text-white">
                                                    <i data-feather="credit-card" class="feather-sm"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: Identity & Guardians -->
                        <div class="col-lg-7 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white py-3 border-bottom">
                                    <h5 class="mb-0 text-dark font-weight-bold border-left-brand-lg pl-3">Identity & Verification</h5>
                                </div>
                                <div class="card-body">
                                    <?php	
                                    $aid=$_SESSION['id'];
                                    $ret="select * from users where id=?";
                                    $stmt= $mysqli->prepare($ret) ;
                                    $stmt->bind_param('i',$aid);
                                    $stmt->execute();
                                    $res=$stmt->get_result();
                                    if($row=$res->fetch_object()):
                                    ?>
                                    <div class="row align-items-center mb-4">
                                        <div class="col-auto">
                                            <img src="<?php echo !empty($row->profile_pic) ? '../'.$row->profile_pic : '../assets/images/users/default.png'; ?>" class="rounded-circle shadow-sm p-1 border" width="70" height="70">
                                        </div>
                                        <div class="col border-left ml-3">
                                            <h5 class="text-dark font-weight-bold mb-1"><?php echo htmlentities($row->first_name . ' ' . $row->last_name); ?></h5>
                                            <p class="text-muted small mb-0"><i data-feather="mail" class="feather-xs mr-1"></i><?php echo htmlentities($row->email); ?></p>
                                            <p class="text-muted small mb-0"><i data-feather="phone" class="feather-xs mr-1"></i><?php echo htmlentities($row->contact_no); ?></p>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label class="text-dark font-weight-medium">Emergency Contact <span class="text-danger">*</span></label>
                                                <input type="number" name="econtact" id="econtact" class="form-control border-focus-primary" placeholder="Primary phone" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label class="text-dark font-weight-medium">Guardian Name <span class="text-danger">*</span></label>
                                                <input type="text" name="gname" id="gname" class="form-control border-focus-primary" placeholder="Full name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label class="text-dark font-weight-medium">Guardian Relation <span class="text-danger">*</span></label>
                                                <input type="text" name="grelation" id="grelation" class="form-control border-focus-primary" placeholder="e.g. Next of Kin" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label class="text-dark font-weight-medium">Guardian Phone <span class="text-danger">*</span></label>
                                                <input type="text" name="gcontact" id="gcontact" class="form-control border-focus-primary" placeholder="Direct line" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden inputs for form consistency -->
                                    <input type="hidden" name="fname" value="<?php echo $row->first_name; ?>">
                                    <input type="hidden" name="mname" value="<?php echo $row->middle_name; ?>">
                                    <input type="hidden" name="lname" value="<?php echo $row->last_name; ?>">
                                    <input type="hidden" name="email" value="<?php echo $row->email; ?>">
                                    <input type="hidden" name="gender" value="<?php echo $row->gender; ?>">
                                    <input type="hidden" name="contact" value="<?php echo $row->contact_no; ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: Residential History -->
                        <div class="col-lg-5 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-dark font-weight-bold border-left-brand-lg pl-3">Residence History</h5>
                                    <div class="custom-control custom-checkbox ml-2">
                                        <input type="checkbox" class="custom-control-input" id="adcheck" name="adcheck" value="1">
                                        <label class="custom-control-label small text-muted font-weight-bold" for="adcheck" style="cursor: pointer;">Permanent is same</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4 bg-light p-3 rounded-lg">
                                        <h6 class="text-brand small text-uppercase font-weight-bold mb-3">Current Physical Address</h6>
                                        <div class="form-group">
                                            <input type="text" name="address" id="address" class="form-control border-focus-primary bg-white mb-2" placeholder="Street / Estate / House No." required>
                                            <div class="row no-gutters">
                                                <div class="col-7 pr-1">
                                                    <input type="text" name="city" id="city" class="form-control border-focus-primary bg-white" placeholder="City" required>
                                                </div>
                                                <div class="col-5 pl-1">
                                                    <input type="text" name="pincode" id="pincode" class="form-control border-focus-primary bg-white" placeholder="P/C" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-light-info-subtle p-3 rounded-lg" id="permanent_address_container">
                                        <h6 class="text-info small text-uppercase font-weight-bold mb-3">Permanent / Home Address</h6>
                                        <div class="form-group">
                                            <input type="text" name="paddress" id="paddress" class="form-control border-focus-primary bg-white mb-2" placeholder="Home Street / Village" required>
                                            <div class="row no-gutters">
                                                <div class="col-7 pr-1">
                                                    <input type="text" name="pcity" id="pcity" class="form-control border-focus-primary bg-white" placeholder="County/City" required>
                                                </div>
                                                <div class="col-5 pl-1">
                                                    <input type="text" name="ppincode" id="ppincode" class="form-control border-focus-primary bg-white" placeholder="P/C" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Center -->
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body text-center py-4">
                                    <button type="submit" name="submit" class="btn btn-brand px-5 py-3 rounded-pill shadow-lg font-weight-bold mr-2">
                                        <i data-feather="send" class="mr-2"></i> Submit Booking Request
                                    </button>
                                    <p class="mt-3 text-muted small"><i data-feather="info" class="feather-xs mr-1 text-muted"></i> Your request will be sent to the property manager for review.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <?php endif; ?>

            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <?php include '../includes/footer.php' ?>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- apps -->
    <!-- apps -->
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="../dist/js/custom.min.js"></script>
    <!--This page JavaScript -->
    <script src="../assets/extra-libs/c3/d3.min.js"></script>
    <script src="../assets/extra-libs/c3/c3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartist@0.11.4/dist/chartist.min.js"></script>
    <script src="../dist/js/pages/chartist/chartist-plugin-tooltip-v2.min.js"></script>
    <script src="../dist/js/pages/dashboards/dashboard1.min.js"></script>


</body>

<!-- Custom Ft. Script Lines -->
<script>
    function calculateTotal() {
        var fpm = parseFloat($('#fpm').val()) || 0;
        var duration = parseInt($('#duration').val()) || 0;
        var totalAmount = fpm * duration;
        
        $('#ta').val(totalAmount.toFixed(2));
        $('#fpm_display').text(fpm.toLocaleString('en-US', {minimumFractionDigits: 2}));
    }

    function getSeater(val) {
        var hostelId = $('input[name="hostel_id"]').val();
        $.ajax({
            type: "POST",
            url: "get-seater.php",
            data: { room_no: val, hostel_id: hostelId },
            success: function(data){
                $('#seater').val(data.trim());
            }
        });

        $.ajax({
            type: "POST",
            url: "get-seater.php",
            data: { rid: val, hostel_id: hostelId },
            success: function(data){
                $('#fpm').val(data.trim());
                calculateTotal();
            }
        });
    }

    $(document).ready(function() {
        $('#duration').on('change', function(){
            calculateTotal();
        });
    });
</script>

    <script>
    function loadRoomsForHostel(hostelId) {
        if (!hostelId) return;
        
        // Update info text
        var selectedOption = $('#hostel_select option:selected');
        var address = selectedOption.data('address');
        $('#hostel-info-display').html('<i class="fas fa-map-marker-alt"></i> ' + address);

        // Clear current rooms
        $('#room').html('<option selected>Loading...</option>');
        
        // Fetch new rooms
        $.ajax({
            type: "POST",
            url: "get-seater.php", // We can reuse this or create a new endpoint, but get-seater.php handles room_no queries well. 
                                  // Wait, get-seater.php might not return the full list of rooms. 
                                  // Let's check get-seater.php content first. But we can use a new inline logic or modify get-seater.php.
                                  // Actually, the page itself has logic to fetch rooms. We should probably create a simpler way.
                                  // Let's use a new action on this page or a helper.
                                  // To be safe and clean, let's use a new AJAX call to a helper.
                                  // OR: We can use the existing check-availability logic? No.
                                  // Let's try to query 'get-hostel-rooms.php' which we might need to create, 
                                  // OR easier: just reload the page with ?hostel_id=... 
                                  // The user asked for a dropdown "to select hostels... and you pick rooms for that selected hostel".
                                  // Reloading the page is the most robust way to ensure all PHP state is correct (session checks etc).
                                  
            // CHANGED PLAN: Reload page to ensure full state consistency
            // url: "book-hostel.php", 
            // data: { hostel_id: hostelId },
            // ...
        });
        
        // Simpler implementation as per typical PHP app patterns:
        window.location.href = 'book-hostel.php?hostel_id=' + hostelId;
    }

    $(document).ready(function(){
        $('input[type="checkbox"]').click(function(){
            if($(this).prop("checked") == true){
                $('#paddress').val( $('#address').val() );
                $('#pcity').val( $('#city').val() );
                $('#ppincode').val( $('#pincode').val() );
            } 
            
        });
    });
    </script>
    
    <script>
        function checkAvailability() {
        $("#loaderIcon").show();
        var hostelId = $('input[name="hostel_id"]').val();
        jQuery.ajax({
        url: "check-availability.php",
        data: { roomno: $("#room").val(), hostel_id: hostelId },
        type: "POST",
        success:function(data){
            $("#room-availability-status").html(data);
            $("#loaderIcon").hide();
        },
            error:function (){}
            });
        }
    </script>


    <script type="text/javascript">
    $(document).ready(function() {
        // Any other document ready logic can go here
    });
    </script>

</html>
