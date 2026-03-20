<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $role = $_SESSION['role'];
    $tenantId = $_SESSION['tenant_id'];

    if(isset($_POST['verify_payment'])){
        $pay_id = $_POST['pay_id'];
        $action = $_POST['action']; // 'verified' or 'rejected'
        
        $query = "UPDATE payments SET status=? WHERE id=? AND tenant_id=?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sii', $action, $pay_id, $tenantId);
        if($stmt->execute()){
            $_SESSION['msg'] = "Payment marked as " . $action;
        } else {
            $_SESSION['msg'] = "Error updating payment status.";
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Payments - Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
</head>

<body>
    <div id="main-wrapper" data-theme="light" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed" data-boxed-layout="full">
        <header class="topbar" data-navbarbg="skin6">
             <?php include 'includes/navigation.php'?>
        </header>
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar" data-sidebarbg="skin6">
                <?php include 'includes/sidebar.php'?>
            </div>
        </aside>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Verify Client Payments</h4>
                    </div>
                </div>

                <?php if(isset($_SESSION['msg'])): ?>
                <div class="alert alert-info alert-dismissible bg-info text-white border-0 fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Info: </strong> <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-md-flex align-items-center mb-4">
                                    <div>
                                        <h4 class="card-title">Pending Payment Verifications</h4>
                                        <h6 class="card-subtitle">Review and verify client payment submissions</h6>
                                    </div>
                                    <div class="ml-auto mt-2 mt-md-0">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i data-feather="search" class="feather-sm"></i></span>
                                            </div>
                                            <input type="text" id="paymentSearch" class="form-control border-left-0" placeholder="Search client or transaction ID...">
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="paymentGrid">
                                    <?php
                                    $query = "SELECT p.*, u.first_name, u.last_name, u.email FROM payments p LEFT JOIN users u ON p.client_id = u.id WHERE p.tenant_id=? ORDER BY p.created_at DESC";
                                    $stmt = $mysqli->prepare($query);
                                    $stmt->bind_param('i', $tenantId);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    
                                    if($res->num_rows == 0):
                                    ?>
                                    <div class="col-12 text-center py-5">
                                        <i data-feather="credit-card" class="text-muted mb-3" style="width: 50px; height: 50px;"></i>
                                        <h4 class="text-muted">No payment records found</h4>
                                        <p class="text-muted">New payment submissions will appear here for verification.</p>
                                    </div>
                                    <?php
                                    else:
                                        while($row = $res->fetch_object()):
                                            $clientName = $row->first_name ? htmlentities($row->first_name . ' ' . $row->last_name) : "Unknown Client";
                                            $clientInitial = $row->first_name ? strtoupper($row->first_name[0]) : "?";
                                            $statusClass = ($row->status == 'verified') ? 'success' : (($row->status == 'rejected') ? 'danger' : 'warning');
                                            $statusIcon = ($row->status == 'verified') ? 'check-circle' : (($row->status == 'rejected') ? 'x-circle' : 'clock');
                                    ?>
                                    <div class="col-lg-4 col-md-6 mb-4 payment-card" data-client="<?php echo strtolower($clientName); ?>" data-tid="<?php echo strtolower($row->transaction_id); ?>">
                                        <div class="card h-100 border shadow-none hover-shadow transition-all">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                                                        <span class="text-primary font-weight-bold"><?php echo $clientInitial; ?></span>
                                                    </div>
                                                    <div class="overflow-hidden">
                                                        <h5 class="mb-0 text-truncate font-weight-bold text-dark"><?php echo $clientName; ?></h5>
                                                        <small class="text-muted d-block text-truncate"><?php echo htmlentities($row->email ?? 'No email'); ?></small>
                                                    </div>
                                                    <div class="ml-auto">
                                                        <span class="badge badge-light-<?php echo $statusClass; ?> text-<?php echo $statusClass; ?> px-2 py-1">
                                                            <i data-feather="<?php echo $statusIcon; ?>" class="feather-xs mr-1"></i><?php echo ucfirst($row->status); ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="bg-light rounded p-3 mb-3">
                                                    <div class="row">
                                                        <div class="col-6 border-right">
                                                            <small class="text-muted d-block text-uppercase small font-weight-medium">Amount</small>
                                                            <h5 class="mb-0 text-primary">KSh <?php echo number_format($row->amount, 0); ?></h5>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block text-uppercase small font-weight-medium">Method</small>
                                                            <h6 class="mb-0"><?php echo htmlentities($row->payment_method); ?></h6>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <small class="text-muted d-block text-uppercase small font-weight-medium mb-1">Transaction ID</small>
                                                    <div class="d-flex align-items-center justify-content-between bg-white border rounded p-2">
                                                        <code class="text-dark"><?php echo htmlentities($row->transaction_id); ?></code>
                                                        <?php if($row->proof_file): ?>
                                                            <a href="../uploads/payments/<?php echo $row->proof_file; ?>" target="_blank" class="btn btn-xs btn-outline-info" title="View Proof">
                                                                <i data-feather="file-text" class="feather-xs"></i> Proof
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                                                    <small class="text-muted"><i data-feather="calendar" class="feather-xs mr-1"></i> <?php echo date('d M Y', strtotime($row->created_at)); ?></small>
                                                    
                                                    <?php if($row->status == 'pending'): ?>
                                                    <div class="d-flex">
                                                        <form method="POST" class="mr-2">
                                                            <input type="hidden" name="pay_id" value="<?php echo $row->id; ?>">
                                                            <input type="hidden" name="action" value="verified">
                                                            <button type="submit" name="verify_payment" class="btn btn-success btn-sm px-3 shadow-none">Approve</button>
                                                        </form>
                                                        <form method="POST">
                                                            <input type="hidden" name="pay_id" value="<?php echo $row->id; ?>">
                                                            <input type="hidden" name="action" value="rejected">
                                                            <button type="submit" name="verify_payment" class="btn btn-outline-danger btn-sm px-3 shadow-none">Reject</button>
                                                        </form>
                                                    </div>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Processed</span>
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
        // Real-time search/filter
        $("#paymentSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var visibleCount = 0;
            
            $(".payment-card").filter(function() {
                var match = $(this).data("client").indexOf(value) > -1 || 
                           $(this).data("tid").indexOf(value) > -1;
                $(this).toggle(match);
                if(match) visibleCount++;
            });

            // Handle "No Results" state visually if needed
            if(visibleCount === 0 && $(".payment-card").length > 0) {
                if(!$("#noResultsMsg").length) {
                    $("#paymentGrid").append('<div id="noResultsMsg" class="col-12 text-center py-4 text-muted">No payments match your search.</div>');
                }
            } else {
                $("#noResultsMsg").remove();
            }
        });
    });
    </script>
</body>
</html>
