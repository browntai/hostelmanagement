<?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();
    //code for bookings
    include('../includes/tenant_manager.php');
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();
    
    $selected_hostel = isset($_GET['hostel_id']) ? intval($_GET['hostel_id']) : null;
    
    // If Super Admin (not impersonating), we need to determine tenant_id from the selected hostel
    // so that we can fetch rooms and insert the booking correctly.
    if ($selected_hostel && !$tenantId) {
        $stmt_t = $mysqli->prepare("SELECT tenant_id FROM hostels WHERE id=?");
        $stmt_t->bind_param('i', $selected_hostel);
        $stmt_t->execute();
        $res_t = $stmt_t->get_result();
        if($row_t = $res_t->fetch_object()){
            $tenantId = $row_t->tenant_id;
        }
    }

    if(isset($_POST['submit'])){
        $roomno=$_POST['room'];
        
        // Fetch hostel_id and seater (capacity) for this room
        // Note: For admin, we ensure tenantId is set above
        $hQuery = "SELECT hostel_id, seater FROM rooms WHERE room_no=? AND tenant_id=?";
        $hStmt = $mysqli->prepare($hQuery);
        $hStmt->bind_param('si', $roomno, $tenantId);
        $hStmt->execute();
        $hRes = $hStmt->get_result();
        $hostel_id = null;
        $capacity = 0;
        if($hRow = $hRes->fetch_assoc()) {
            $hostel_id = $hRow['hostel_id'];
            $capacity = $hRow['seater'];
        }

        // Check if room is full
        $countQuery = "SELECT count(*) FROM bookings WHERE roomno=? AND tenant_id=?";
        $countStmt = $mysqli->prepare($countQuery);
        $countStmt->bind_param('ii', $roomno, $tenantId);
        $countStmt->execute();
        $countStmt->bind_result($currentCount);
        $countStmt->fetch();
        $countStmt->close();

        if ($currentCount >= $capacity && $capacity > 0) {
            echo "<script>alert('Error: Room is already full!');</script>";
        } else {
            $seater=$_POST['seater'];
            $feespm=$_POST['fpm'];
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
            $query="INSERT into  bookings(roomno,seater,feespm,stayfrom,duration,firstName,middleName,lastName,gender,contactno,emailid,egycontactno,guardianName,guardianRelation,guardianContactno,corresAddress,corresCIty,corresPincode,pmntAddress,pmntCity,pmntPincode,tenant_id,hostel_id,booking_status) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $status = 'pending';
            $rc=$stmt->bind_param('iiissssssssssssssssssiis',$roomno,$seater,$feespm,$stayfrom,$duration,$fname,$mname,$lname,$gender,$contactno,$emailid,$emcntno,$gurname,$gurrelation,$gurcntno,$caddress,$ccity,$cpincode,$paddress,$pcity,$ppincode,$tenantId,$hostel_id,$status);
            $stmt->execute();
            echo"<script>alert('Success: Booked!');</script>";
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

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
    <!-- Custom CSS -->
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">

    <script>
    function getSeater(val) {
        var hostelId = <?php echo $selected_hostel ? $selected_hostel : 'null'; ?>;
        // Using landlord get-seater since admin doesn't seem to have one and logic is same
        $.ajax({
        type: "POST",
        url: "../landlord/get-seater.php",
        data: { roomid: val, hostel_id: hostelId },
        success: function(data){
        //alert(data);
        $('#seater').val(data);
        }
        });

        $.ajax({
        type: "POST",
        url: "../landlord/get-seater.php",
        data: { rid: val, hostel_id: hostelId },
        success: function(data){
        //alert(data);
        $('#fpm').val(data);
        }
        });
    }
    </script>
    
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- TEMPORARILY DISABLED FOR DIAGNOSTICS -->
    <!--
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
            <?php include 'includes/navigation.php'?>
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
                <?php include 'includes/sidebar.php'?>
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper" style="display: block;">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Hostel Bookings (Admin)</h4>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">

                <form method="POST">
                    <!-- SECTION 1: Hostel Selection (Main Trigger) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-primary font-weight-bold mb-3">
                                        <i data-feather="home" class="feather-sm mr-2"></i>Select Hostel to Book
                                    </h5>
                                    <div class="form-group mb-0">
                                        <select class="custom-select custom-select-lg border-focus-primary shadow-sm" onchange="window.location.href='bookings.php?hostel_id='+this.value">
                                            <option value="">-- Choose Hostel --</option>
                                            <?php 
                                            if ($tm->isSuperAdmin() && !isset($_SESSION['impersonate_tenant_id'])) {
                                                $h_query = "SELECT * FROM hostels WHERE status='approved'";
                                                $h_stmt = $mysqli->prepare($h_query);
                                            } else {
                                                $h_query = "SELECT * FROM hostels WHERE tenant_id=?";
                                                $h_stmt = $mysqli->prepare($h_query);
                                                $h_stmt->bind_param('i', $tenantId);
                                            }
                                            $h_stmt->execute();
                                            $h_res = $h_stmt->get_result();
                                            while($h_row = $h_res->fetch_object()){
                                                $selected = ($selected_hostel == $h_row->id) ? 'selected' : '';
                                                echo "<option value='".$h_row->id."' $selected>".htmlentities($h_row->name)." (".$h_row->city.")</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if($selected_hostel): ?>
                    <input type="hidden" name="hostel_id" value="<?php echo $selected_hostel; ?>">
                    
                    <div class="row">
                        <!-- SECTION 2: Booking Information -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-gradient-info py-3">
                                    <h5 class="mb-0 text-white"><i data-feather="calendar" class="feather-sm mr-2"></i>Booking Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Room Selection</label>
                                            <select class="custom-select border-focus-primary" name="room" id="room" onChange="getSeater(this.value);" onBlur="checkAvailability()" required>
                                                <option value="">Select Room...</option>
                                                <?php 
                                                if($selected_hostel && $tenantId) {
                                                    $query ="SELECT * FROM rooms WHERE tenant_id=? AND hostel_id=? AND status='available' AND room_no NOT IN (SELECT roomno FROM bookings WHERE hostel_id=? AND (booking_status != 'cancelled' AND booking_status != 'rejected'))";
                                                    $stmt2 = $mysqli->prepare($query);
                                                    if ($stmt2) {
                                                        $stmt2->bind_param('iii', $tenantId, $selected_hostel, $selected_hostel);
                                                        $stmt2->execute();
                                                        $res=$stmt2->get_result();
                                                        while($row=$res->fetch_object()){
                                                            echo '<option value="'.$row->room_no.'">Room '.$row->room_no.'</option>';
                                                        }
                                                        $stmt2->close();
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <span id="room-availability-status" class="small mt-1 d-block"></span>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Start Date</label>
                                            <input type="date" name="stayf" id="stayf" class="form-control border-focus-primary" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Capacity (Seater)</label>
                                            <input type="text" id="seater" name="seater" placeholder="Automatically fetched" readonly class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Stay Duration (Months)</label>
                                            <select class="custom-select border-focus-primary" id="duration" name="duration" required>
                                                <option value="">Choose...</option>
                                                <?php for($i=1; $i<=12; $i++) echo "<option value='$i'>$i Month(s)</option>"; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Rent Per Month</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text bg-light border-0">KSh</span></div>
                                                <input type="text" name="fpm" id="fpm" readonly class="form-control bg-light border-0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block font-weight-extra-bold">Total Amount</label>
                                            <div class="input-group shadow-sm border border-success rounded">
                                                <div class="input-group-prepend"><span class="input-group-text bg-success text-white border-0">KSh</span></div>
                                                <input type="text" name="ta" id="ta" readonly class="form-control bg-white border-0 font-weight-bold text-success">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: Client Personal Details -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-gradient-primary py-3">
                                    <h5 class="mb-0 text-white"><i data-feather="user" class="feather-sm mr-2"></i>Client Personal Info</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">First Name</label>
                                            <input type="text" name="fname" id="fname" placeholder="First Name" required class="form-control border-focus-primary">
                                        </div>
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Middle Name</label>
                                            <input type="text" name="mname" id="mname" placeholder="Middle Name" class="form-control border-focus-primary">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Last Name</label>
                                            <input type="text" name="lname" id="lname" placeholder="Last Name" required class="form-control border-focus-primary">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-7 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block"><i data-feather="mail" class="feather-xs mr-1 text-primary"></i> Registration Email</label>
                                            <input type="email" name="email" id="email" placeholder="client@example.com" required class="form-control border-focus-primary">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Gender</label>
                                            <select name="gender" class="custom-select border-focus-primary" required>
                                                <option value="">Select...</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="others">Others</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block"><i data-feather="phone" class="feather-xs mr-1 text-primary"></i> Primary Contact</label>
                                            <input type="number" name="contact" id="contact" placeholder="07..." required class="form-control border-focus-primary">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Emergency Contact</label>
                                            <input type="number" name="econtact" id="econtact" placeholder="Guardian/Kin No." required class="form-control border-focus-primary">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: Guardian Details -->
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-gradient-dark py-3">
                                    <h5 class="mb-0 text-white"><i data-feather="shield" class="feather-sm mr-2"></i>Guardian & Emergency Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Guardian Full Name</label>
                                            <input type="text" name="gname" id="gname" class="form-control border-focus-primary" placeholder="Enter Full Name" required>
                                        </div>
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Relationship</label>
                                            <input type="text" name="grelation" id="grelation" placeholder="e.g. Parent, Sibling" required class="form-control border-focus-primary">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Guardian Contact</label>
                                            <input type="text" name="gcontact" id="gcontact" placeholder="Full Phone Number" required class="form-control border-focus-primary">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 5: Address Information -->
                        <div class="col-lg-12 mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-secondary py-3">
                                    <h5 class="mb-0 text-white"><i data-feather="map-pin" class="feather-sm mr-2"></i>Residential Address Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Current Address -->
                                        <div class="col-md-6 mb-4 mb-md-0 border-right border-light">
                                            <h6 class="font-weight-bold text-muted mb-3">Current Residence</h6>
                                            <div class="form-group mb-3">
                                                <label class="small text-uppercase font-weight-bold">Street / Area Address</label>
                                                <input type="text" name="address" id="address" class="form-control border-focus-primary" required placeholder="Street address, Apartment info">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-7 mb-3 mb-md-0">
                                                    <label class="small text-uppercase font-weight-bold">City / Town</label>
                                                    <input type="text" name="city" id="city" class="form-control border-focus-primary" required placeholder="City">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="small text-uppercase font-weight-bold">Postal Code</label>
                                                    <input type="text" name="pincode" id="pincode" class="form-control border-focus-primary" required placeholder="Code">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Permanent Address -->
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="font-weight-bold text-muted mb-0">Permanent Residence</h6>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="adcheck" name="adcheck" value="1">
                                                    <label class="custom-control-label small text-info cursor-pointer font-weight-bold" for="adcheck">Same as Current</label>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="small text-uppercase font-weight-bold">Street / Area Address</label>
                                                <input type="text" name="paddress" id="paddress" class="form-control border-focus-primary" required placeholder="Permanent address">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-7 mb-3 mb-md-0">
                                                    <label class="small text-uppercase font-weight-bold">City / Town</label>
                                                    <input type="text" name="pcity" id="pcity" class="form-control border-focus-primary" required placeholder="City">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="small text-uppercase font-weight-bold">Postal Code</label>
                                                    <input type="text" name="ppincode" id="ppincode" class="form-control border-focus-primary" required placeholder="Code">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-12 text-center mt-2 mb-4">
                            <div class="card bg-light border-0 shadow-sm py-4">
                                <div class="card-body">
                                    <button type="submit" name="submit" class="btn btn-success btn-lg px-5 shadow-sm mr-3">
                                        <i data-feather="check-circle" class="mr-2 feather-sm"></i>Complete Booking
                                    </button>
                                    <button type="reset" class="btn btn-outline-dark btn-lg px-5">
                                        <i data-feather="rotate-ccw" class="mr-2 feather-sm"></i>Clear Form
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-12 text-center py-5">
                            <i data-feather="book-open" class="text-muted mb-3" style="width: 60px; height: 60px;"></i>
                            <h4 class="text-dark font-weight-bold">Ready to Book a Client?</h4>
                            <p class="text-muted">Please select a hostel from the dropdown menu above to begin the booking process.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>

            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <?php 
            include 'includes/footer.php';
            ?>
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

    <!-- Custom Ft. Script Lines -->
<script type="text/javascript">
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
        var hostelId = <?php echo $selected_hostel ? $selected_hostel : 'null'; ?>;
        jQuery.ajax({
        url: "../landlord/check-availability.php", // Point to Landlord check availability
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
        $('#duration').change(function(){
            var duration = parseInt($(this).val()) || 0;
            var fpm = parseFloat($('#fpm').val()) || 0;
            var total = duration * fpm;
            $('#ta').val(total);
        });
        
        // Also update if fpm changes (though usually fetched via ajax)
        $('#fpm').change(function(){
            var duration = parseInt($('#duration').val()) || 0;
            var fpm = parseFloat($(this).val()) || 0;
            var total = duration * fpm;
            $('#ta').val(total);
        });
    });
    </script>

    <script>
        // Emergency preloader killer to prevent forever-loading if scripts fail
        (function() {
            console.log("Bookings page loaded - starting preloader killer");
            var hidePreloader = function() {
                var preloader = document.querySelector('.preloader');
                if (preloader && preloader.style.display !== 'none') {
                    console.log("Hiding preloader via emergency script");
                    preloader.style.opacity = '0';
                    setTimeout(function() {
                        preloader.style.display = 'none';
                    }, 500);
                }
            };
            // Try immediately, on load, and after 2 seconds
            hidePreloader();
            window.addEventListener('load', hidePreloader);
            setTimeout(hidePreloader, 2000);
        })();
    </script>
</body>

</html>
