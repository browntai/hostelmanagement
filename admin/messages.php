<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $adminId = $_SESSION['id'];
    $role = $_SESSION['role'];
    $tenantId = $_SESSION['tenant_id']; // NULL for Super Admin

    if(isset($_POST['reply_message'])){
        $msg_id = $_POST['msg_id'];
        $reply_text = $_POST['reply_text'];
        
        // Fetch original message to get client info and subject
        $q = "SELECT * FROM messages WHERE id=?";
        $stmt = $mysqli->prepare($q);
        $stmt->bind_param('i', $msg_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $orig = $res->fetch_object();

        if($orig){
            $subject = "Re: " . $orig->subject;
            $target_recipient_id = ($orig->sender_id == $adminId) ? $orig->receiver_id : $orig->sender_id;
            $t_id = $orig->tenant_id;

            // Fetch recipient role
            $r_q = "SELECT role FROM users WHERE id=?";
            $r_stmt = $mysqli->prepare($r_q);
            $r_stmt->bind_param('i', $target_recipient_id);
            $r_stmt->execute();
            $target_role = $r_stmt->get_result()->fetch_object()->role;

            $query = "INSERT INTO messages (sender_id, sender_role, receiver_id, receiver_role, tenant_id, subject, message) VALUES (?, 'admin', ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('iiisss', $adminId, $target_recipient_id, $target_role, $t_id, $subject, $reply_text);
            if($stmt->execute()){
                $_SESSION['msg'] = "Reply sent successfully.";
            } else {
                $_SESSION['msg'] = "Error sending reply.";
            }
        }
    }
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Messages - Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/chat-style.css" rel="stylesheet">
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
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Admin Messages</h4>
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
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h4 class="card-title mb-0">Inbox</h4>
                                    <a href="conversation.php?new=true" class="btn btn-primary shadow-sm">
                                        <i class="fas fa-plus mr-1"></i> Compose Message
                                    </a>
                                </div>
                                <div class="list-group list-group-flush">
                                    <?php
                                    if($tenantId === null){
                                        // Super Admin: Get latest messages with anyone where tenant_id is NULL
                                        $query = "SELECT m.*, u.full_name, u.id as other_id, u.role as other_role
                                                  FROM messages m 
                                                  JOIN users u ON (
                                                      (m.sender_id = u.id AND m.receiver_id = ?) OR 
                                                      (m.receiver_id = u.id AND m.sender_id = ?)
                                                  )
                                                  WHERE m.tenant_id IS NULL 
                                                  AND m.id IN (
                                                      SELECT MAX(id) 
                                                      FROM messages 
                                                      WHERE tenant_id IS NULL 
                                                      GROUP BY IF(sender_id=?, receiver_id, sender_id)
                                                  )
                                                  ORDER BY m.created_at DESC";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->bind_param('iii', $adminId, $adminId, $adminId);
                                    } else {
                                        // Tenant Admin: Get latest messages for this tenant
                                        $query = "SELECT m.*, u.full_name, u.id as other_id, u.role as other_role
                                                  FROM messages m 
                                                  JOIN users u ON (
                                                      (m.sender_id = u.id AND m.receiver_id = ?) OR 
                                                      (m.receiver_id = u.id AND m.sender_id = ?)
                                                  )
                                                  WHERE m.tenant_id=?
                                                  AND m.id IN (
                                                      SELECT MAX(id) 
                                                      FROM messages 
                                                      WHERE tenant_id=?
                                                      GROUP BY IF(sender_id=?, receiver_id, sender_id)
                                                  )
                                                  ORDER BY m.created_at DESC";
                                        $stmt = $mysqli->prepare($query);
                                        $stmt->bind_param('iiiii', $adminId, $adminId, $tenantId, $tenantId, $adminId);
                                    }
                                    
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    
                                    if($res->num_rows > 0):
                                        while($row = $res->fetch_object()):
                                            // Determine display name (other party)
                                            $displayName = $row->full_name;
                                            $otherId = $row->other_id;
                                            $otherRole = ucfirst($row->other_role);
                                    ?>
                                    <a href="conversation.php?client_id=<?php echo $otherId; ?>" class="list-group-item list-group-item-action chat-list-item">
                                        <div class="d-flex align-items-center">
                                            <div class="chat-avatar bg-primary text-white"><?php echo substr($displayName, 0, 1); ?></div>
                                            <div class="ml-3 w-100">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h5 class="mb-0 font-weight-bold"><?php echo htmlentities($displayName); ?> <small class="text-muted">[<?php echo $otherRole; ?>]</small></h5>
                                                    <small class="chat-time"><?php echo date('M d, H:i', strtotime($row->created_at)); ?></small>
                                                </div>
                                                <div class="chat-preview">
                                                    <?php echo ($row->sender_role == 'admin') ? '<span class="text-muted">You: </span>' : ''; ?>
                                                    <?php echo htmlentities($row->message); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="icon-bubble display-4 mb-3 d-block"></i>
                                        No messages found.
                                    </div>
                                    <?php endif; ?>
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
</body>
</html>
