<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $userId = $_SESSION['id'];
    $tenantId = $_SESSION['tenant_id'];

    if(isset($_POST['send_message'])){
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        $recipient = $_POST['recipient']; // 'landlord' or 'admin' (super admin)
        
        $receiver_role = 'admin';
        $target_tenant_id = ($recipient == 'landlord') ? $tenantId : NULL;

        $query = "INSERT INTO messages (sender_id, sender_role, receiver_role, tenant_id, subject, message) VALUES (?, 'client', 'admin', ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iiss', $userId, $target_tenant_id, $subject, $message);
        if($stmt->execute()){
            $_SESSION['msg'] = "Message sent successfully.";
        } else {
            $_SESSION['msg'] = "Error sending message.";
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages - Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/chat-style.css" rel="stylesheet">
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
            <div class="container-fluid">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">My Messages</h4>
                    </div>
                    <div class="col-5 align-self-center text-right">
                         <div class="customize-input float-right">
                             <a href="conversation.php?new=true" class="btn btn-primary rounded-circle" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;padding:0 !important;" title="Start New Chat"><i class="fas fa-plus"></i></a>
                         </div>
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
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php
                                    $userEmail = $_SESSION['login'];
                                    $hasConvos = false;

                                    // 1. Fetch conversations with Landlords (tenant_id IS NOT NULL)
                                    // Group by tenant_id to get one entry per landlord
                                    $query = "SELECT m.*, t.name as landlord_name, t.id as landlord_tid 
                                              FROM messages m 
                                              LEFT JOIN tenants t ON m.tenant_id = t.id 
                                              WHERE m.id IN (
                                                  SELECT MAX(id) 
                                                  FROM messages 
                                                  WHERE (sender_id=? OR receiver_id=?)
                                                  AND tenant_id IS NOT NULL
                                                  GROUP BY tenant_id
                                              )
                                              ORDER BY m.created_at DESC";
                                    
                                    $stmt = $mysqli->prepare($query);
                                    $stmt->bind_param('ii', $userId, $userId);
                                    $stmt->execute();
                                    $res = $stmt->get_result();

                                    while($row = $res->fetch_object()):
                                        $hasConvos = true;
                                        $lName = $row->landlord_name;
                                        $tid = $row->landlord_tid;
                                    ?>
                                    <a href="conversation.php?recipient=landlord&landlord_tid=<?php echo $tid; ?>" class="list-group-item list-group-item-action chat-list-item">
                                        <div class="d-flex align-items-center">
                                            <div class="chat-avatar bg-success text-white">L</div>
                                            <div class="ml-3 w-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h5 class="mb-0 font-weight-bold">Landlord: <?php echo htmlentities($lName); ?></h5>
                                                    <small class="chat-time"><?php echo date('M d, H:i', strtotime($row->created_at)); ?></small>
                                                </div>
                                                <div class="chat-preview">
                                                    <?php echo ($row->sender_id == $userId && $row->sender_role == 'client') ? '<span class="text-muted">You: </span>' : ''; ?>
                                                    <?php echo htmlentities($row->message); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endwhile;

                                    // 3. Check for Super Admin conversation
                                    $q2 = "SELECT m.* FROM messages m WHERE ((m.sender_id=? AND m.sender_role='client') OR (m.receiver_id=? AND m.receiver_role='client')) AND m.tenant_id IS NULL ORDER BY m.created_at DESC LIMIT 1";
                                    $stmt2 = $mysqli->prepare($q2);
                                    $stmt2->bind_param('ii', $userId, $userId);
                                    $stmt2->execute();
                                    $res2 = $stmt2->get_result();
                                    $adminMsg = $res2->fetch_object();

                                    if($adminMsg): $hasConvos = true; ?>
                                    <a href="conversation.php?recipient=admin" class="list-group-item list-group-item-action chat-list-item">
                                        <div class="d-flex align-items-center">
                                            <div class="chat-avatar bg-info text-white">A</div>
                                            <div class="ml-3 w-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h5 class="mb-0 font-weight-bold">Super Admin</h5>
                                                    <small class="chat-time"><?php echo date('M d, H:i', strtotime($adminMsg->created_at)); ?></small>
                                                </div>
                                                <div class="chat-preview">
                                                    <?php echo ($adminMsg->sender_id == $userId && $adminMsg->sender_role == 'client') ? '<span class="text-muted">You: </span>' : ''; ?>
                                                    <?php echo htmlentities($adminMsg->message); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if(!$hasConvos): ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="icon-bubble display-4 mb-3 d-block"></i>
                                        No conversations yet. Start a new one!
                                    </div>
                                    <?php endif; ?>
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
</body>
</html>
