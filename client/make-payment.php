<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $userId = $_SESSION['id'];
    $email = $_SESSION['login'];

    // Handle M-Pesa STK Push
    if(isset($_POST['pay_mpesa'])){
        include_once('../includes/mpesa-helper.php');
        $booking_id = intval($_POST['booking_id']);
        $amount     = floatval($_POST['amount']);
        $phone      = $_POST['mpesa_phone'];
        $tenantId   = intval($_POST['tenant_id']);
        
        $ref  = "BKG-$booking_id";
        $desc = "Rent Payment";
        
        $res = initiateStkPush($phone, $amount, $ref, $desc);
        
        if ($res['success']) {
            $checkoutId = $res['CheckoutRequestID'];
            
            // Insert pending record linked to checkout ID
            $query = "INSERT INTO payments (booking_id, client_id, tenant_id, amount, transaction_id, payment_method, status) VALUES (?, ?, ?, ?, ?, 'M-Pesa Express', 'pending')";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('iiids', $booking_id, $userId, $tenantId, $amount, $checkoutId);
            $stmt->execute();
            
            if (isset($res['simulated']) && $res['simulated']) {
                // Auto-trigger simulator for localhost testing
                header("Location: mpesa-simulator.php?checkoutRequestID=$checkoutId&amount=$amount&phone=$phone");
                exit();
            } else {
                $_SESSION['msg'] = "M-Pesa prompt sent to $phone. Please enter your PIN to complete the payment.";
                header("Location: make-payment.php");
                exit();
            }
        } else {
            $_SESSION['msg'] = "Error initiating M-Pesa: " . ($res['error'] ?? 'Unknown error');
        }
    }

    // Handle Manual Payment Submission
    if(isset($_POST['submit_payment'])){
        $booking_id = intval($_POST['booking_id']);
        $amount = floatval($_POST['amount']);
        $transaction_id = $_POST['transaction_id'];
        $payment_method_id = intval($_POST['payment_method_id']);
        
        $tQ = "SELECT tenant_id FROM bookings WHERE id=?";
        $tSt = $mysqli->prepare($tQ);
        $tSt->bind_param('i', $booking_id);
        $tSt->execute();
        $tRow = $tSt->get_result()->fetch_object();
        $b_tenantId = $tRow ? $tRow->tenant_id : 0;

        $mQ = "SELECT method_type, account_number FROM landlord_payment_methods WHERE id=?";
        $mSt = $mysqli->prepare($mQ);
        $mSt->bind_param('i', $payment_method_id);
        $mSt->execute();
        $mRow = $mSt->get_result()->fetch_object();
        $method_name = $mRow ? ($mRow->method_type . " (" . $mRow->account_number . ")") : "External Bank / Manual Cache";

        // Handle file upload
        $proof_file = NULL;
        if(isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] == 0){
            $target_dir = "../uploads/payments/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $file_ext = pathinfo($_FILES["proof_file"]["name"], PATHINFO_EXTENSION);
            $proof_file = "pay_" . time() . "_" . $userId . "." . $file_ext;
            move_uploaded_file($_FILES["proof_file"]["tmp_name"], $target_dir . $proof_file);
        }

        $query = "INSERT INTO payments (booking_id, client_id, tenant_id, amount, transaction_id, payment_method, proof_file, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iiidsss', $booking_id, $userId, $b_tenantId, $amount, $transaction_id, $method_name, $proof_file);
        
        if($stmt->execute()){
            $_SESSION['msg'] = "Payment Proof Submitted! Awaiting verification.";
            header("Location: make-payment.php");
            exit();
        } else {
            $_SESSION['msg'] = "Error: " . $mysqli->error;
        }
    }

    // Get Selected Booking Details
    $selected_booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
    $booking = null;
    $landlord_methods = [];

    if($selected_booking_id > 0){
        $bq = "SELECT b.*, h.name as hostel_name FROM bookings b JOIN hostels h ON b.hostel_id = h.id WHERE b.id=? AND b.emailid=?";
        $bst = $mysqli->prepare($bq);
        $bst->bind_param('is', $selected_booking_id, $email);
        $bst->execute();
        $bres = $bst->get_result();
        $booking = $bres->fetch_object();
        
        if($booking){
            $mq = "SELECT * FROM landlord_payment_methods WHERE tenant_id=? AND status='active'";
            $mst = $mysqli->prepare($mq);
            $mst->bind_param('i', $booking->tenant_id);
            $mst->execute();
            $landlord_methods = $mst->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Make Payment - Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
</head>

<body>
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Complete Your Booking</h4>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12 text-center text-lg-left">
                        <h2 class="page-title text-dark font-weight-black mb-1">Financial Settlement</h2>
                        <p class="text-muted">Securely manage your lease commitments and verification history.</p>
                    </div>
                </div>

                <?php if(isset($_SESSION['msg'])): ?>
                <div class="alert badge-light-brand text-brand border-0 rounded-lg p-3 mb-4 d-flex align-items-center" role="alert">
                    <i data-feather="info" class="mr-3"></i>
                    <div class="font-weight-bold"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
                </div>
                <?php endif; ?>

                <?php if(!$booking): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 mb-5 rounded-lg overflow-hidden">
                            <div class="card-header bg-white py-4 border-bottom d-flex align-items-center">
                                <div class="bg-light-brand p-2 rounded mr-3">
                                    <i data-feather="clock" class="text-brand"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-dark font-weight-black">Pending Obligations</h5>
                                    <small class="text-muted">Active reservations awaiting financial verification.</small>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <?php
                                    $q = "SELECT b.*, h.name as hostel_name FROM bookings b JOIN hostels h ON b.hostel_id = h.id WHERE b.emailid=? AND b.booking_status='pending' ORDER BY b.postingDate DESC";
                                    $s = $mysqli->prepare($q);
                                    $s->bind_param('s', $email);
                                    $s->execute();
                                    $r = $s->get_result();
                                    
                                    if($r->num_rows == 0):
                                    ?>
                                    <div class="col-12 text-center py-5">
                                        <div class="bg-light p-4 rounded-circle d-inline-block mb-3">
                                            <i data-feather="check-circle" class="text-success" style="width: 40px; height: 40px;"></i>
                                        </div>
                                        <h4 class="text-dark font-weight-black">All Clear</h4>
                                        <p class="text-muted mx-auto" style="max-width: 300px;">You have no outstanding lease payments at this moment. Everything is up to date!</p>
                                    </div>
                                    <?php
                                    else:
                                        while($row = $r->fetch_object()):
                                            $total = $row->feespm * $row->duration;
                                    ?>
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <div class="card h-100 border-0 shadow-sm rounded-lg overflow-hidden card-hover transition-3d">
                                            <div class="p-3 bg-light-brand-subtle d-flex align-items-center">
                                                <div class="bg-white p-2 rounded shadow-sm mr-3">
                                                    <i data-feather="home" class="text-brand feather-sm"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <h6 class="mb-0 text-truncate font-weight-black text-dark"><?php echo htmlentities($row->hostel_name); ?></h6>
                                                    <small class="text-brand small font-weight-bold">Unit #<?php echo htmlentities($row->roomno); ?></small>
                                                </div>
                                            </div>
                                            <div class="card-body p-4 text-center">
                                                <small class="text-muted d-block text-uppercase font-weight-black letter-spacing-1 mb-2" style="font-size: 0.6rem;">Master Total</small>
                                                <h3 class="mb-1 text-dark font-weight-black">KSh <?php echo number_format($total, 0); ?></h3>
                                                <span class="badge badge-white text-dark border px-3 py-1 rounded-pill small font-weight-bold"><?php echo $row->duration; ?> Months Lease</span>
                                            </div>
                                            <div class="card-footer bg-white border-top-0 p-3">
                                                <a href="make-payment.php?booking_id=<?php echo $row->id; ?>" class="btn btn-brand btn-block rounded-pill font-weight-black shadow-sm py-2">
                                                    Initiate Settlement <i data-feather="chevron-right" class="feather-sm ml-1"></i>
                                                </a>
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
                <?php else: ?>
                <div class="row">
                    <div class="col-lg-5 mb-4">
                        <div class="card shadow-sm border-0 rounded-lg overflow-hidden h-100">
                            <div class="card-header bg-white py-4 border-bottom">
                                <h5 class="mb-0 text-dark font-weight-black"><i data-feather="list" class="mr-2 text-brand"></i>Transfer Details</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="bg-light-brand-subtle rounded-lg p-3 mb-4 border-brand">
                                    <div class="d-flex align-items-center mb-1">
                                        <i data-feather="box" class="text-brand mr-2 feather-xs"></i>
                                        <small class="text-brand font-weight-black text-uppercase letter-spacing-1" style="font-size: 0.6rem;">Target Residence</small>
                                    </div>
                                    <h6 class="mb-0 text-dark font-weight-black"><?php echo htmlentities($booking->hostel_name); ?></h6>
                                    <small class="text-muted font-weight-bold">Room <?php echo $booking->roomno; ?></small>
                                    <hr class="my-3 border-brand-subtle">
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <small class="text-muted d-block small font-weight-bold">Settlement Amount</small>
                                            <h4 class="mb-0 text-brand font-weight-black">KSh <?php echo number_format($booking->feespm * $booking->duration, 0); ?></h4>
                                        </div>
                                        <span class="badge badge-brand text-white px-3 py-1 rounded-pill small font-weight-bold">Verified Slot</span>
                                    </div>
                                </div>
                                
                                <h6 class="text-dark font-weight-black small text-uppercase letter-spacing-1 mb-3">Approved Channels</h6>
                                <?php if(empty($landlord_methods)): ?>
                                    <div class="bg-light rounded-lg p-4 text-center border">
                                        <i data-feather="alert-circle" class="text-muted mb-2"></i>
                                        <p class="text-muted small mb-0">No automated channels detected. Please contact the residence administrator for manual settlement instructions.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach($landlord_methods as $method): 
                                            $mIcon = (strpos($method['method_type'], 'BANK') !== false) ? 'briefcase' : 'smartphone';
                                        ?>
                                            <div class="col-12 mb-3">
                                                <div class="card border-0 bg-light rounded-lg mb-0 shadow-none">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <span class="badge badge-white text-dark shadow-xs border-0 small px-3 py-1 font-weight-black rounded-pill">
                                                                <i data-feather="<?php echo $mIcon; ?>" class="feather-xs mr-1 text-brand"></i>
                                                                <?php echo str_replace('_', ' ', $method['method_type']); ?>
                                                            </span>
                                                        </div>
                                                        <div class="bg-white rounded p-3 text-center mb-2 shadow-xs">
                                                            <h5 class="mb-0 font-weight-black text-dark letter-spacing-1"><?php echo htmlentities($method['account_number']); ?></h5>
                                                            <small class="text-muted small font-weight-medium"><?php echo htmlentities($method['account_name']); ?></small>
                                                        </div>
                                                        <?php if($method['additional_info']): ?>
                                                            <div class="d-flex align-items-start mt-2">
                                                                <i data-feather="info" class="feather-xs text-brand mt-1 mr-2"></i>
                                                                <small class="text-muted font-weight-medium" style="font-size: 0.7rem; line-height: 1.2;"><?php echo htmlentities($method['additional_info']); ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-center mt-4">
                                    <a href="make-payment.php" class="btn btn-link btn-sm text-brand font-weight-black">
                                        <i data-feather="arrow-left" class="feather-xs mr-1"></i> Return to Obligations
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card shadow-sm border-0 rounded-lg overflow-hidden h-100">
                                <div class="card-header bg-gradient-brand py-3">
                                    <h5 class="mb-0 text-white font-weight-bold text-white"><i data-feather="credit-card" class="feather-sm mr-2 text-white"></i>Settlement Center</h5>
                                    <small class="text-white opacity-7">Documentation required for legal verification.</small>
                                </div>
                            <div class="card-body p-4 p-lg-5">
                                <ul class="nav nav-pills nav-fill mb-4" id="pills-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active rounded-pill font-weight-bold" id="pills-mpesa-tab" data-toggle="pill" href="#pills-mpesa" role="tab" aria-controls="pills-mpesa" aria-selected="true"><i class="fas fa-mobile-alt mr-2"></i>M-Pesa Express</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded-pill font-weight-bold" id="pills-manual-tab" data-toggle="pill" href="#pills-manual" role="tab" aria-controls="pills-manual" aria-selected="false"><i class="fas fa-file-upload mr-2"></i>Manual Upload</a>
                                    </li>
                                </ul>

                                <div class="tab-content" id="pills-tabContent">
                                    <!-- M-Pesa Express Tab -->
                                    <div class="tab-pane fade show active" id="pills-mpesa" role="tabpanel" aria-labelledby="pills-mpesa-tab">
                                        <div class="alert alert-success bg-light-success text-success border-0 mb-4 text-center">
                                            <i class="fas fa-bolt fa-2x mb-2 text-success"></i>
                                            <h6 class="font-weight-bold mb-1">Instant Verification</h6>
                                            <p class="small mb-0">Enter your M-Pesa number. You will receive a prompt to enter your PIN. Verification happens instantly.</p>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking->id; ?>">
                                            <input type="hidden" name="tenant_id" value="<?php echo $booking->tenant_id; ?>">
                                            <input type="hidden" name="amount" value="<?php echo $booking->feespm * $booking->duration; ?>">
                                            
                                            <div class="form-group mb-4">
                                                <label class="text-muted small text-uppercase font-weight-black letter-spacing-1 mb-2 d-block">M-Pesa Phone Number</label>
                                                <div class="input-group input-group-lg">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text border-0 bg-light px-3 text-success font-weight-bold">+254</span>
                                                    </div>
                                                    <input type="text" name="mpesa_phone" class="form-control border-0 bg-light font-weight-black text-dark" placeholder="712345678" required style="letter-spacing: 2px;" pattern="^7[0-9]{8}$|^1[0-9]{8}$" title="Enter 9 digits starting with 7 or 1">
                                                </div>
                                            </div>
                                            <div class="pt-3">
                                                <button type="submit" name="pay_mpesa" class="btn btn-success btn-lg btn-block rounded-pill shadow-lg font-weight-bold py-3 text-white">
                                                    Pay KSh <?php echo number_format($booking->feespm * $booking->duration, 0); ?> Now <i class="fas fa-arrow-right ml-2 text-white"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Manual Upload Tab -->
                                    <div class="tab-pane fade" id="pills-manual" role="tabpanel" aria-labelledby="pills-manual-tab">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking->id; ?>">
                                            <input type="hidden" name="amount" value="<?php echo $booking->feespm * $booking->duration; ?>">
                                            
                                            <div class="form-group mb-4">
                                                <label class="text-muted small text-uppercase font-weight-black letter-spacing-1 mb-2 d-block">Settlement Channel</label>
                                                <select name="payment_method_id" class="form-control form-control-lg border-0 bg-light rounded-lg font-weight-medium custom-select" required>
                                                    <option value="">-- Select Master Channel --</option>
                                                    <?php foreach($landlord_methods as $method): ?>
                                                        <option value="<?php echo $method['id']; ?>"><?php echo str_replace('_', ' ', $method['method_type']); ?> (<?php echo $method['account_number']; ?>)</option>
                                                    <?php endforeach; ?>
                                                    <option value="0">External Bank / Manual Cache</option>
                                                </select>
                                            </div>

                                            <div class="form-group mb-4">
                                                <label class="text-muted small text-uppercase font-weight-black letter-spacing-1 mb-2 d-block">Nexus Transaction ID</label>
                                                <div class="input-group input-group-lg">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text border-0 bg-light px-3"><i class="fas fa-hashtag text-muted"></i></span>
                                                    </div>
                                                    <input type="text" name="transaction_id" class="form-control border-0 bg-light font-weight-black text-dark" placeholder="e.g. MK12RT90X" required style="letter-spacing: 2px;">
                                                </div>
                                            </div>

                                            <div class="form-group mb-4">
                                                <label class="text-muted small text-uppercase font-weight-black letter-spacing-1 mb-2 d-block">Documentary Proof</label>
                                                <div class="bg-light rounded-lg p-4 border-dashed text-center position-relative">
                                                    <input type="file" name="proof_file" class="custom-file-input" id="proof_file" accept="image/*,.pdf" required style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer;" onchange="$('#file-ready-text').text(this.files[0].name); $(this).parent().addClass('bg-light-brand-subtle')">
                                                    <div id="upload-placeholder">
                                                        <i class="fas fa-image text-brand mb-2 fa-2x"></i>
                                                        <p class="mb-0 text-dark font-weight-bold" id="file-ready-text">Select Image or PDF Document</p>
                                                        <small class="text-muted">High-res capture of receipt or terminal message.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pt-3">
                                                <button type="submit" name="submit_payment" class="btn btn-brand btn-lg btn-block rounded-pill shadow-lg font-weight-black py-3">
                                                    Submit for Audit <i class="fas fa-paper-plane ml-2"></i>
                                                </button>
                                                <p class="text-center mt-3 text-muted small"><i class="fas fa-shield-alt text-brand mr-1"></i> Manual verification takes up to 24 hours.</p>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
                            <div class="card-header bg-white py-4 border-bottom">
                                <h5 class="mb-0 text-dark font-weight-black"><i data-feather="archive" class="mr-2 text-brand"></i>Financial Ledger</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1">Sync Date</th>
                                                <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1">Residence</th>
                                                <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1">Channel</th>
                                                <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1">Reference</th>
                                                <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1 text-right">Settlement</th>
                                                <th class="border-0 text-muted px-4 py-3 small font-weight-black text-uppercase letter-spacing-1 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $qp = "SELECT p.*, h.name as hostel_name FROM payments p LEFT JOIN bookings b ON p.booking_id=b.id LEFT JOIN hostels h ON b.hostel_id=h.id WHERE p.client_id=? ORDER BY p.created_at DESC";
                                            $sp = $mysqli->prepare($qp);
                                            $sp->bind_param('i', $userId);
                                            $sp->execute();
                                            $rp = $sp->get_result();
                                            
                                            if($rp->num_rows == 0):
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <i data-feather="inbox" class="text-muted mb-2 opacity-5" style="width: 40px; height: 40px;"></i>
                                                    <p class="text-muted font-weight-medium">Financial archives are currently empty.</p>
                                                </td>
                                            </tr>
                                            <?php
                                            else:
                                                while($p = $rp->fetch_object()):
                                                    $p_status = ($p->status == 'verified') ? 'success' : (($p->status == 'rejected') ? 'danger' : 'warning');
                                                    $p_bg = ($p->status == 'verified') ? 'badge-light-success' : (($p->status == 'rejected') ? 'badge-light-danger' : 'badge-light-warning');
                                            ?>
                                            <tr class="transition-all">
                                                <td class="px-4 py-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-light p-2 rounded mr-3 text-center" style="min-width: 50px;">
                                                            <small class="text-muted d-block font-weight-black letter-spacing-1" style="font-size: 10px;"><?php echo strtoupper(date('M', strtotime($p->created_at))); ?></small>
                                                            <span class="text-dark font-weight-black"><?php echo date('d', strtotime($p->created_at)); ?></span>
                                                        </div>
                                                        <small class="text-muted font-weight-medium"><?php echo date('H:i A', strtotime($p->created_at)); ?></small>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <h6 class="mb-0 text-dark font-weight-black"><?php echo htmlentities($p->hostel_name ?? 'Main Terminal'); ?></h6>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <span class="text-muted font-weight-medium small"><?php echo htmlentities($p->payment_method); ?></span>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <code class="text-brand font-weight-black small px-2 py-1 bg-light-brand-subtle rounded"><?php echo htmlentities($p->transaction_id); ?></code>
                                                </td>
                                                <td class="px-4 py-4 text-right">
                                                    <span class="text-dark font-weight-black">KSh <?php echo number_format($p->amount, 0); ?></span>
                                                </td>
                                                <td class="px-4 py-4 text-center">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <span class="badge <?php echo $p_bg; ?> text-<?php echo $p_status; ?> px-3 py-1 rounded-pill small font-weight-black">
                                                            <?php echo strtoupper($p->status); ?>
                                                        </span>
                                                        <?php if($p->proof_file): ?>
                                                            <a href="../uploads/payments/<?php echo $p->proof_file; ?>" target="_blank" class="btn btn-xs btn-light-brand ml-2 rounded-circle" style="width: 24px; height: 24px; padding: 0; line-height: 24px;" title="Review Proof">
                                                                <i data-feather="eye" style="width: 12px; height: 12px;"></i>
                                                            </a>
                                                            <?php if($p->status != 'verified'): ?>
                                                                <a href="delete-proof.php?id=<?php echo $p->id; ?>" class="btn btn-xs btn-light-danger ml-2 rounded-circle" style="width: 24px; height: 24px; padding: 0; line-height: 24px;" title="Delete Proof" onclick="return confirm('Are you sure you want to delete this payment proof? This will also remove the payment record.');">
                                                                    <i data-feather="trash-2" style="width: 12px; height: 12px;"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
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
        $(document).ready(function() {
            $(".custom-file-input").on("change", function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            });
        });
    </script>
</body>
</html>
