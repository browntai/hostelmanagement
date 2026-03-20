<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $userId = $_SESSION['id'];
    $userEmail = $_SESSION['login'];
    // $tenantId = $_SESSION['tenant_id']; // This is the default, but we should use the specific one for the landlord we are chatting with.

    $recipient = isset($_GET['recipient']) ? $_GET['recipient'] : '';
    $landlord_tid = isset($_GET['landlord_tid']) ? intval($_GET['landlord_tid']) : 0;
    $isNew = isset($_GET['new']) && $_GET['new'] == 'true';
    
    // Handle Message Sending
    if(isset($_POST['send_message'])){
        $msg_recipient = $_POST['recipient'];
        $message = $_POST['message'];
        $subject = isset($_POST['subject']) ? $_POST['subject'] : 'Conversation'; // Default subject for chat
        
        $target_tenant_id = null;
        $receiver_id = null;
        if($msg_recipient == 'landlord'){
            $target_tenant_id = isset($_POST['landlord_tid']) ? intval($_POST['landlord_tid']) : $landlord_tid;
            // Fetch Landlord User ID
            $l_q = "SELECT id FROM users WHERE role='landlord' AND tenant_id=? LIMIT 1";
            $l_st = $mysqli->prepare($l_q);
            $l_st->bind_param('i', $target_tenant_id);
            $l_st->execute();
            $l_res = $l_st->get_result();
            if($l_row = $l_res->fetch_object()) $receiver_id = $l_row->id;
        } else {
            // Admin
            $a_q = "SELECT id FROM users WHERE role='admin' AND tenant_id IS NULL LIMIT 1";
            $a_res = $mysqli->query($a_q);
            if($a_row = $a_res->fetch_object()) $receiver_id = $a_row->id;
        }

        $query = "INSERT INTO messages (sender_id, sender_role, receiver_id, receiver_role, tenant_id, subject, message) VALUES (?, 'client', ?, 'admin', ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        if($stmt){
            $stmt->bind_param('iiiss', $userId, $receiver_id, $target_tenant_id, $subject, $message);
            if($stmt->execute()){
                // Notify Recipient
                include_once('../includes/notification-helper.php');
                $notifTitle = ($msg_recipient == 'admin') ? "New Admin Message" : "New Landlord Message";
                if($receiver_id) {
                    sendNotification($receiver_id, $notifTitle, "New message from client ($userEmail)", $userId);
                }

                // If it was a new chat, redirect to the conversation view
                if($isNew){
                    header("Location: conversation.php?recipient=" . $msg_recipient . ($target_tenant_id ? "&landlord_tid=" . $target_tenant_id : ""));
                    exit();
                }
            } else {
                echo "Execute Error: " . $stmt->error;
            }
        } else {
            echo "Prepare Error: " . $mysqli->error;
        }
    }

    // Mark incoming messages as read
    if($recipient == 'admin'){
        $update_q = "UPDATE messages SET is_read=1 WHERE receiver_id=? AND sender_role='admin' AND tenant_id IS NULL AND is_read=0";
    } else {
        $update_q = "UPDATE messages SET is_read=1 WHERE receiver_id=? AND sender_role='admin' AND tenant_id=? AND is_read=0";
    }
    $stmt_up = $mysqli->prepare($update_q);
    if($stmt_up) {
        if($recipient == 'admin') $stmt_up->bind_param('i', $userId);
        else $stmt_up->bind_param('ii', $userId, $landlord_tid);
        $stmt_up->execute();
    }

    // Determine target name
    $targetName = "New Chat";
    if($recipient == 'landlord') {
        if($landlord_tid) {
            $q_l = "SELECT name FROM tenants WHERE id=?";
            $stmt_l = $mysqli->prepare($q_l);
            $stmt_l->bind_param('i', $landlord_tid);
            $stmt_l->execute();
            $res_l = $stmt_l->get_result();
            if($l_data = $res_l->fetch_object()){
                $targetName = "Landlord: " . $l_data->name;
            } else {
                $targetName = "Landlord";
            }
        } else {
            $targetName = "Landlord";
        }
    }
    if($recipient == 'admin') $targetName = "Super Admin";

?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat with <?php echo $targetName; ?> - Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/chat-style.css" rel="stylesheet">
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
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
                
                <?php if($isNew): ?>
                <!-- New Chat Form -->
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Start New Conversation</h4>
                                <?php
                                // Fetch ALL landlords (tenants) so clients can message any landlord
                                $landlordQuery = "SELECT id, name as landlord_name FROM tenants ORDER BY name ASC";
                                $resL = $mysqli->query($landlordQuery);
                                
                                $landlords = [];
                                while($lRow = $resL->fetch_assoc()){
                                    $landlords[] = $lRow;
                                }
                                ?>
                                <form method="POST">
                                    <div class="form-group">
                                        <label for="msg-recipient">To:</label>
                                        <select name="recipient" id="msg-recipient" class="form-control" required onchange="toggleLandlordSelect(this.value)">
                                            <option value="landlord">Landlord</option>
                                            <option value="admin">Super Admin</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="landlord-select-group">
                                        <label for="landlord_tid">Select Landlord:</label>
                                        <select name="landlord_tid" id="landlord_tid" class="form-control">
                                            <?php foreach($landlords as $l): ?>
                                            <option value="<?php echo $l['id']; ?>"><?php echo htmlentities($l['landlord_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="msg-subject">Subject:</label>
                                        <input type="text" name="subject" id="msg-subject" class="form-control" required placeholder="Brief topic...">
                                    </div>
                                    <div class="form-group">
                                        <label for="msg-content">Message:</label>
                                        <textarea name="message" id="msg-content" class="form-control" rows="5" required placeholder="Type your message here..."></textarea>
                                    </div>
                                    <button type="submit" name="send_message" class="btn btn-primary">Start Chat</button>
                                    <a href="messages.php" class="btn btn-secondary">Cancel</a>
                                </form>
                                <script>
                                    function toggleLandlordSelect(val) {
                                        document.getElementById('landlord-select-group').style.display = (val === 'landlord') ? 'block' : 'none';
                                    }
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Chat View -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card chat-container">
                            <div class="chat-header">
                                <a href="messages.php" class="btn btn-light btn-circle mr-2"><i class="fas fa-arrow-left"></i></a>
                                <div class="chat-avatar <?php echo ($recipient=='landlord'?'bg-success':'bg-info'); ?> text-white mr-3">
                                    <?php echo ($recipient=='landlord'?'L':'A'); ?>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold"><?php echo $targetName; ?></h5>
                                </div>
                            </div>
                            
                            <div class="chat-messages" id="chatbox">
                                <?php
                                $target_tid_sql = ($recipient == 'landlord') ? "tenant_id=?" : "tenant_id IS NULL";
                                
                                $query = "SELECT * FROM messages WHERE 
                                          ((sender_id=? AND sender_role='client') OR (receiver_id=? AND receiver_role='client'))
                                          AND ($target_tid_sql)
                                          ORDER BY created_at ASC";
                                          
                                $stmt = $mysqli->prepare($query);
                                if($recipient == 'landlord'){
                                    $stmt->bind_param('iii', $userId, $userId, $landlord_tid);
                                } else {
                                    $stmt->bind_param('ii', $userId, $userId);
                                }
                                $stmt->execute();
                                $res = $stmt->get_result();
                                
                                if($res->num_rows > 0):
                                    while($row = $res->fetch_object()):
                                        $isMe = ($row->sender_id == $userId && $row->sender_role == 'client');
                                        $bubbleClass = $isMe ? 'message-sent' : 'message-received';
                                ?>
                                <div class="message-bubble <?php echo $bubbleClass; ?>">
                                    <?php if(!$isMe): ?>
                                    <strong class="d-block text-muted small mb-1"><?php echo $targetName; ?></strong>
                                    <?php endif; ?>
                                    <?php echo nl2br(htmlentities($row->message)); ?>
                                    <span class="message-time"><?php echo date('H:i', strtotime($row->created_at)); ?></span>
                                </div>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <div class="text-center text-muted mt-5">No messages in this conversion yet.</div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" class="chat-input-area">
                                <input type="hidden" name="recipient" value="<?php echo $recipient; ?>">
                                <?php if($recipient == 'landlord'): ?>
                                <input type="hidden" name="landlord_tid" value="<?php echo $landlord_tid; ?>">
                                <?php endif; ?>
                                <textarea name="message" class="form-control" placeholder="Type a message..." required></textarea>
                                <button type="submit" name="send_message" class="btn btn-send shadow">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
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
            // Scroll to bottom of chat
            var chatbox = document.getElementById("chatbox");
            if(chatbox){
                chatbox.scrollTop = chatbox.scrollHeight;
            }
        });
    </script>
</body>
</html>
