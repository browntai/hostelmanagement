<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $uid = $_SESSION['id'];
    $email = $_SESSION['login'];

    include('../includes/hostel-helper.php');
    include_once('../includes/toast-helper.php');

    // 1. Get Tenant's Hostel
    $tenant_hostel_id = null;
    $hostel_name = "";
    $stmt = $mysqli->prepare("SELECT b.hostel_id, h.name FROM bookings b JOIN hostels h ON b.hostel_id = h.id WHERE b.emailid=? AND b.booking_status IN ('confirmed', 'approved') ORDER BY b.postingDate DESC LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_object()) {
        $tenant_hostel_id = $row->hostel_id;
        $hostel_name = $row->name;
    }
    $stmt->close();

    // 2. Check if Daycare is enabled for this hostel
    if(!$tenant_hostel_id || !isServiceEnabled($mysqli, $tenant_hostel_id, 'daycare')) {
        header("Location: dashboard.php");
        exit();
    }

    // 3. Get Daycare Price
    $priceQuery = "SELECT price_per_day FROM hostel_services WHERE hostel_id=? AND service_key='daycare'";
    $pStmt = $mysqli->prepare($priceQuery);
    $pStmt->bind_param('i', $tenant_hostel_id);
    $pStmt->execute();
    $pRes = $pStmt->get_result()->fetch_object();
    $daycare_price = $pRes ? $pRes->price_per_day : 500.00; // Default if not set

    // 4. Handle Booking Submission
    if(isset($_POST['book_daycare'])) {
        $child_id = intval($_POST['child_id']);
        $booking_date = $_POST['booking_date'];
        $time_slot = $_POST['time_slot'];

        // Fetch child details for the record
        $cStmt = $mysqli->prepare("SELECT full_name, age FROM children WHERE id=? AND parent_id=?");
        $cStmt->bind_param('ii', $child_id, $uid);
        $cStmt->execute();
        $child = $cStmt->get_result()->fetch_object();

        if(!$child) {
            setToast('error', 'Invalid child selected.');
        } else {
            // CAPACITY CHECK
            $capStmt = $mysqli->prepare("SELECT max_capacity FROM hostel_services WHERE hostel_id=? AND service_key='daycare'");
            $capStmt->bind_param('i', $tenant_hostel_id);
            $capStmt->execute();
            $capRes = $capStmt->get_result()->fetch_object();
            $max_cap = $capRes ? $capRes->max_capacity : 10;
            
            $countStmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM daycare_bookings WHERE hostel_id=? AND booking_date=? AND status != 'declined'");
            $countStmt->bind_param('is', $tenant_hostel_id, $booking_date);
            $countStmt->execute();
            $countRes = $countStmt->get_result()->fetch_object();
            $current_count = $countRes->cnt;

            if($current_count >= $max_cap) {
                setToast('error', "Sorry, daycare is at full capacity ($max_cap) for $booking_date.");
            } else {
                $stmt = $mysqli->prepare("INSERT INTO daycare_bookings (hostel_id, client_id, child_id, child_name, child_age, booking_date, time_slot, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('iiiisssd', $tenant_hostel_id, $uid, $child_id, $child->full_name, $child->age, $booking_date, $time_slot, $daycare_price);
                if($stmt->execute()) {
                    setToast('success', 'Daycare request submitted successfully! Please proceed to payments if approved.');
                } else {
                    setToast('error', 'Failed to submit request.');
                }
            }
        }
        header("Location: daycare-service.php");
        exit();
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daycare Service - Hostel Management System</title>
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
            <?php include '../includes/client-navigation.php'?>
        </header>
        
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include '../includes/client-sidebar.php'?>
            </div>
        </aside>
        
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Daycare Service</h4>
                        <p class="text-muted"><i class="fas fa-home"></i> Professional childcare at <strong><?php echo htmlentities($hostel_name); ?></strong></p>
                    </div>
                    <div class="col-5 align-self-center text-right">
                        <div class="bg-light-success p-2 rounded d-inline-block border border-success">
                            <h5 class="mb-0 text-success font-weight-bold">Daily Rate: KSh <?php echo number_format($daycare_price); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <div class="row">
                    <!-- Request Form -->
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title mb-4">New Daycare Request</h4>
                                <?php
                                $childrenRes = $mysqli->query("SELECT * FROM children WHERE parent_id = $uid");
                                if($childrenRes->num_rows == 0):
                                ?>
                                <div class="text-center py-4">
                                    <p class="text-muted mb-3">You need to register your child first.</p>
                                    <a href="register-child.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Register Child</a>
                                </div>
                                <?php else: ?>
                                <form action="" method="POST">
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">Select Child</label>
                                        <select name="child_id" class="form-control border-light" required>
                                            <option value="">Choose child...</option>
                                            <?php while($c = $childrenRes->fetch_object()): ?>
                                            <option value="<?php echo $c->id; ?>"><?php echo htmlentities($c->full_name); ?> (<?php echo $c->age; ?> yrs)</option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-control-label">Preferred Date</label>
                                        <input type="date" name="booking_date" class="form-control border-light" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="form-control-label">Time Slot</label>
                                        <select name="time_slot" class="form-control border-light" required>
                                            <option value="Full Day (8 AM - 5 PM)">Full Day (8 AM - 5 PM)</option>
                                            <option value="Morning (8 AM - 12 PM)">Morning (8 AM - 12 PM)</option>
                                            <option value="Afternoon (1 PM - 5 PM)">Afternoon (1 PM - 5 PM)</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="book_daycare" class="btn btn-primary btn-block py-2 font-weight-bold">
                                        <i class="fas fa-paper-plane mr-2"></i> Submit Request
                                    </button>
                                </form>
                                <?php endif; ?>
                                <div class="mt-4 p-3 bg-light rounded small text-muted">
                                    <i class="fas fa-info-circle mr-1"></i> Our daycare service operates from 8:00 AM to 5:00 PM. Please submit requests at least 24 hours in advance.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Request History -->
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title mb-4">My Daycare Requests</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Child & Slot</th>
                                                <th>Service Date</th>
                                                <th>Payment</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $hQuery = "SELECT db.*, u.full_name as staff_name 
                                                      FROM daycare_bookings db 
                                                      LEFT JOIN users u ON db.assigned_caretaker_id = u.id 
                                                      WHERE db.client_id = ? 
                                                      ORDER BY db.created_at DESC";
                                            $hStmt = $mysqli->prepare($hQuery);
                                            $hStmt->bind_param('i', $uid);
                                            $hStmt->execute();
                                            $history = $hStmt->get_result();
                                            
                                            if($history->num_rows == 0):
                                            ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No requests found.</td>
                                            </tr>
                                            <?php
                                            else:
                                                while($row = $history->fetch_object()):
                                                    $statusLabel = str_replace('_', ' ', $row->status);
                                                    $statusColor = 'warning';
                                                    if($row->status == 'approved') $statusColor = 'info';
                                                    if($row->status == 'checked_in') $statusColor = 'primary';
                                                    if($row->status == 'checked_out') $statusColor = 'success';
                                                    if($row->status == 'declined') $statusColor = 'danger';

                                                    $pColor = $row->payment_status == 'paid' ? 'success' : 'danger';
                                            ?>
                                            <tr>
                                                <td class="font-weight-medium">
                                                    <?php echo htmlentities($row->child_name); ?>
                                                    <div class="small text-muted"><?php echo htmlentities($row->time_slot); ?></div>
                                                    <?php if($row->landlord_notes): ?>
                                                        <br><small class="text-primary"><i class="fas fa-comment"></i> <?php echo htmlentities($row->landlord_notes); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($row->booking_date)); ?>
                                                    <?php if($row->check_in_time): ?>
                                                        <div class="small text-muted">In: <?php echo date('H:i', strtotime($row->check_in_time)); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $pColor; ?> mb-1"><?php echo strtoupper($row->payment_status); ?></span>
                                                    <div class="small">KSh <?php echo number_format($row->amount, 2); ?></div>
                                                    <?php if($row->payment_status == 'pending' && $row->status == 'approved'): ?>
                                                        <a href="make-payment.php?daycare_id=<?php echo $row->id; ?>" class="btn btn-xs btn-outline-success mt-1">Pay Now</a>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $statusColor; ?> px-2 py-1">
                                                        <?php echo ucfirst($statusLabel); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            endif; 
                                            ?>
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
    <script src="../assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../dist/js/app-style-switcher.js"></script>
    <script src="../dist/js/feather.min.js"></script>
    <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../dist/js/sidebarmenu.js"></script>
    <script src="../dist/js/custom.min.js"></script>
    <script>
        $(".preloader").fadeOut();
    </script>
</body>

</html>
