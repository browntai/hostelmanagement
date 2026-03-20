<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $tenantId = $_SESSION['tenant_id'];

    if(isset($_POST['add_method'])){
        $type = $_POST['method_type'];
        $name = $_POST['account_name'];
        $number = $_POST['account_number'];
        $info = $_POST['additional_info'];
        
        $query = "INSERT INTO landlord_payment_methods (tenant_id, method_type, account_name, account_number, additional_info) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('issss', $tenantId, $type, $name, $number, $info);
        if($stmt->execute()){
            echo "<script>alert('Payment method added successfully');</script>";
        } else {
            echo "<script>alert('Error adding payment method');</script>";
        }
    }

    if(isset($_GET['del'])){
        $id = intval($_GET['del']);
        $query = "DELETE FROM landlord_payment_methods WHERE id=? AND tenant_id=?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ii', $id, $tenantId);
        $stmt->execute();
        header("Location: payment-settings.php");
        exit();
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Settings - Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
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
                    <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Payment Settings</h4>
                        <div class="d-flex align-items-center">
                            <h6 class="card-subtitle">Manage your payment methods (M-Pesa, Bank, etc.)</h6> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-4 col-md-5">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-gradient-primary py-3">
                                <h5 class="mb-0 text-white"><i data-feather="plus-circle" class="feather-sm mr-2"></i>Add Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-group mb-4">
                                        <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Method Type</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-0"><i data-feather="credit-card" class="feather-xs"></i></span>
                                            </div>
                                            <select name="method_type" class="form-control custom-select border-focus-primary" required id="method_type_select">
                                                <option value="MPESA_PAYBILL">M-Pesa Paybill</option>
                                                <option value="MPESA_TILL">M-Pesa Till No</option>
                                                <option value="SEND_MONEY">Send Money (M-Pesa/Airtel)</option>
                                                <option value="BANK_TRANSFER">Bank Transfer</option>
                                                <option value="OTHER">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label id="name_label" class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Business Name</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-0"><i data-feather="user" class="feather-xs"></i></span>
                                            </div>
                                            <input type="text" name="account_name" class="form-control border-focus-primary" required placeholder="e.g. My Hostel Ltd">
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label id="number_label" class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Paybill Number</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-0"><i data-feather="hash" class="feather-xs"></i></span>
                                            </div>
                                            <input type="text" name="account_number" class="form-control border-focus-primary" required placeholder="e.g. 174379">
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="text-muted small text-uppercase font-weight-bold mb-2 d-block">Instructions</label>
                                        <textarea name="additional_info" class="form-control border-focus-primary" rows="3" placeholder="e.g. Use Room No as Account No"></textarea>
                                    </div>
                                    <button type="submit" name="add_method" class="btn btn-primary btn-block shadow-sm py-2 mt-2">
                                        <i data-feather="save" class="feather-sm mr-2"></i>Save Method
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-7">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <h4 class="card-title mb-0">Registered Methods</h4>
                                    <div class="ml-auto">
                                        <span class="badge badge-light-primary text-primary px-3 py-2">Total: <?php 
                                            $cQ = "SELECT count(*) as total FROM landlord_payment_methods WHERE tenant_id=?";
                                            $cSt = $mysqli->prepare($cQ);
                                            $cSt->bind_param('i', $tenantId);
                                            $cSt->execute();
                                            $cRes = $cSt->get_result();
                                            echo $cRes->fetch_object()->total;
                                        ?></span>
                                    </div>
                                </div>

                                <div class="row">
                                    <?php
                                    $query = "SELECT * FROM landlord_payment_methods WHERE tenant_id=? ORDER BY id DESC";
                                    $stmt = $mysqli->prepare($query);
                                    $stmt->bind_param('i', $tenantId);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    
                                    if($res->num_rows == 0):
                                    ?>
                                    <div class="col-12 text-center py-5">
                                        <i data-feather="info" class="text-muted mb-2" style="width: 40px; height: 40px;"></i>
                                        <h5 class="text-muted">No methods configured yet</h5>
                                        <p class="small text-muted">Add a method on the left to start receiving payments.</p>
                                    </div>
                                    <?php
                                    else:
                                        while($row = $res->fetch_object()):
                                            $icon = (strpos($row->method_type, 'BANK') !== false) ? 'briefcase' : 'smartphone';
                                            $badgeClass = (strpos($row->method_type, 'MPESA') !== false) ? 'success' : 'primary';
                                    ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100 border shadow-none hover-shadow transition-all">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px;">
                                                        <i data-feather="<?php echo $icon; ?>" class="text-<?php echo $badgeClass; ?> feather-sm"></i>
                                                    </div>
                                                    <div>
                                                        <span class="badge badge-light-<?php echo $badgeClass; ?> text-<?php echo $badgeClass; ?> small px-2 py-1 mb-1">
                                                            <?php echo str_replace('_', ' ', $row->method_type); ?>
                                                        </span>
                                                        <h6 class="mb-0 font-weight-bold text-dark"><?php echo htmlentities($row->account_name); ?></h6>
                                                    </div>
                                                </div>

                                                <div class="bg-light rounded p-2 mb-3 text-center">
                                                    <small class="text-muted d-block small mb-1">Account / Number</small>
                                                    <h5 class="mb-0 font-weight-bold letter-spacing-1"><?php echo htmlentities($row->account_number); ?></h5>
                                                </div>

                                                <?php if($row->additional_info): ?>
                                                <div class="mb-3">
                                                    <p class="small text-muted mb-0">
                                                        <i data-feather="info" class="feather-xs mr-1"></i>
                                                        <?php echo htmlentities($row->additional_info); ?>
                                                    </p>
                                                </div>
                                                <?php endif; ?>

                                                <div class="text-right border-top pt-2">
                                                    <a href="payment-settings.php?del=<?php echo $row->id; ?>" class="btn btn-outline-danger btn-sm border-0" onclick="return confirm('Delete this method?')">
                                                        <i data-feather="trash-2" class="feather-xs mr-1"></i> Delete
                                                    </a>
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
        $('#method_type_select').on('change', function() {
            var val = $(this).val();
            if(val == 'MPESA_PAYBILL') {
                $('#name_label').text('Business Name');
                $('#number_label').text('Paybill Number');
            } else if(val == 'MPESA_TILL') {
                $('#name_label').text('Store Name');
                $('#number_label').text('Till Number');
            } else if(val == 'SEND_MONEY') {
                $('#name_label').text('Recipient Name');
                $('#number_label').text('Phone Number');
            } else if(val == 'BANK_TRANSFER') {
                $('#name_label').text('Bank Name & Branch');
                $('#number_label').text('Account Number');
            } else {
                $('#name_label').text('Account Name');
                $('#number_label').text('Number');
            }
        });
    </script>
</body>
</html>
