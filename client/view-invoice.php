<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    if(!isset($_GET['id'])){
        header("Location: room-details.php");
        exit();
    }

    $booking_id = $_GET['id'];
    $email = $_SESSION['login'];

    $query = "SELECT r.*, h.name as hostel_name, h.city as hostel_city FROM bookings r LEFT JOIN hostels h ON r.hostel_id = h.id WHERE r.id=? AND r.emailid=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('is', $booking_id, $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_object();

    if(!$row){
        echo "Invoice not found or unauthorized.";
        exit();
    }

    // Calculation
    $dr = $row->duration;
    $fpm = $row->feespm;
    $foodstatus = isset($row->foodstatus) ? $row->foodstatus : 0;
    $food_cost = ($foodstatus == 1) ? 211 : 0;
    $total_monthly = $fpm + $food_cost;
    $grand_total = $total_monthly * $dr;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #<?php echo $row->id; ?></title>
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #f4f7f6; 
            font-family: 'Inter', sans-serif;
            color: #2d3436;
        }
        .invoice-wrapper {
            max-width: 900px;
            margin: 50px auto;
        }
        .card-invoice {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            background: #fff;
            overflow: hidden;
        }
        .invoice-header {
            background: linear-gradient(135deg, #0f3443 0%, #34e89e 100%);
            padding: 3rem;
            color: #fff;
        }
        .invoice-body {
            padding: 3.5rem;
        }
        .table-invoice thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1px;
            border: none;
            color: #636e72;
            padding: 1.5rem 1rem;
        }
        .table-invoice tbody td {
            padding: 1.5rem 1rem;
            border-top: 1px solid #f1f2f6;
            vertical-align: center;
        }
        .total-row {
            background: #fcfcfd;
            font-weight: 800;
            font-size: 1.25rem;
            color: #0f3443;
        }
        .brand-text {
            letter-spacing: -0.5px;
            font-weight: 800;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .invoice-wrapper { margin: 0; max-width: 100%; }
            .card-invoice { box-shadow: none; border-radius: 0; }
            .invoice-header { background: #000 !important; color: #fff !important; padding: 2rem; }
            .invoice-body { padding: 2rem; }
        }
    </style>
</head>
<body>
    <div class="invoice-wrapper px-3">
        <div class="card card-invoice">
            <div class="invoice-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="brand-text mb-1 text-white">INVOICE</h1>
                    <p class="opacity-8 mb-0">Official Residence Commitment</p>
                </div>
                <div class="text-right">
                    <h4 class="mb-0 font-weight-bold text-white">#<?php echo str_pad($row->id, 6, '0', STR_PAD_LEFT); ?></h4>
                    <small class="opacity-8">Date: <?php echo date('F d, Y'); ?></small>
                </div>
            </div>

            <div class="invoice-body">
                <div class="row mb-5">
                    <div class="col-sm-6 mb-4 mb-sm-0">
                        <small class="text-muted text-uppercase font-weight-bold d-block mb-3">Service Provider</small>
                        <h5 class="font-weight-bold mb-1 text-dark"><?php echo htmlentities($row->hostel_name); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlentities($row->hostel_city); ?>, Kenya</p>
                    </div>
                    <div class="col-sm-6 text-sm-right border-left pl-sm-5">
                        <small class="text-muted text-uppercase font-weight-bold d-block mb-3">Billed To</small>
                        <h5 class="font-weight-bold mb-1 text-dark"><?php echo htmlentities($row->firstName . ' ' . $row->lastName); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlentities($row->emailid); ?></p>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-invoice mb-0">
                                <thead>
                                    <tr>
                                        <th>Detailed Description</th>
                                        <th class="text-center">Rate</th>
                                        <th class="text-center">Period</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <h6 class="font-weight-bold mb-1">Residential Space Rental</h6>
                                            <p class="text-muted small mb-0">Unit #<?php echo $row->roomno; ?> Accommodation</p>
                                        </td>
                                        <td class="text-center">KSh <?php echo number_format($fpm, 0); ?></td>
                                        <td class="text-center"><?php echo $dr; ?> Months</td>
                                        <td class="text-right font-weight-bold text-dark">KSh <?php echo number_format($fpm * $dr, 0); ?></td>
                                    </tr>
                                    <?php if(isset($foodstatus) && $foodstatus == 1 && $food_cost > 0): ?>
                                    <tr>
                                        <td>
                                            <h6 class="font-weight-bold mb-1">Catering Services</h6>
                                            <p class="text-muted small mb-0">Full board meal plan</p>
                                        </td>
                                        <td class="text-center">KSh <?php echo number_format($food_cost, 0); ?></td>
                                        <td class="text-center"><?php echo $dr; ?> Months</td>
                                        <td class="text-right font-weight-bold text-dark">KSh <?php echo number_format($food_cost * $dr, 0); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="3" class="text-right py-4 border-0">Total Commitment</td>
                                        <td class="text-right py-4 border-0">KSh <?php echo number_format($grand_total, 0); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="bg-light p-4 rounded-lg">
                            <h6 class="font-weight-bold mb-2 text-dark"><i class="fas fa-info-circle mr-2 text-muted"></i>Payment Instructions</h6>
                            <p class="text-muted small mb-0">Please use your Booking ID <strong>#<?php echo $row->id; ?></strong> as the payment reference when making transfers via M-PESA or Bank.</p>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-right mt-4 mt-md-0 no-print">
                        <button onclick="window.print();" class="btn btn-dark btn-lg px-4 rounded-pill font-weight-bold shadow-sm mr-2">
                            <i data-feather="printer" class="feather-sm mr-2"></i> Print
                        </button>
                        <a href="room-details.php" class="btn btn-outline-secondary btn-lg px-4 rounded-pill font-weight-bold">
                            Close
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="text-center mt-5 text-muted small no-print">
            &copy; <?php echo date('Y'); ?> Hostel Management System. All rights reserved.
        </p>
    </div>

    <!-- Scripting for Feather Icons -->
    <script src="../dist/js/feather.min.js"></script>
    <script>
        feather.replace();
    </script>
</body>
</html>
