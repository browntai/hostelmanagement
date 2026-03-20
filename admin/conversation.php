<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $adminId = $_SESSION['id'];
    $role = $_SESSION['role'];
    $tenantId = $_SESSION['tenant_id'];

    $recipient_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
    $isNew = isset($_GET['new']) && $_GET['new'] == 'true';
    
    // Fetch client name for existing coversation
    $clientName = "New Chat";
    if($recipient_id){
        $q_user = "SELECT full_name FROM users WHERE id=?";
        $stmt_user = $mysqli->prepare($q_user);
        $stmt_user->bind_param('i', $recipient_id);
        $stmt_user->execute();
        $res_user = $stmt_user->get_result();
        if($clientData = $res_user->fetch_object()){
            $clientName = $clientData->full_name;
        }
    }

    // Mark as read
    if($recipient_id){
        $update_q = "UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0";
        $stmt_up = $mysqli->prepare($update_q);
        $stmt_up->bind_param('ii', $recipient_id, $adminId);
        $stmt_up->execute();
    }

    // Handle Sending Message
    if(isset($_POST['send_message'])){
        $message = $_POST['message'];
        $subject = isset($_POST['subject']) ? $_POST['subject'] : "Reply";
        $target_recipient_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : $recipient_id;
        
        // Fetch recipient role dynamically
        $r_q = "SELECT role FROM users WHERE id=?";
        $r_stmt = $mysqli->prepare($r_q);
        $r_stmt->bind_param('i', $target_recipient_id);
        $r_stmt->execute();
        $r_res = $r_stmt->get_result();
        $target_role = 'client'; // fallback
        if($r_row = $r_res->fetch_object()){
            $target_role = $r_row->role;
        }

        $target_tenant_id = ($tenantId === null) ? NULL : $tenantId;

        $query = "INSERT INTO messages (sender_id, sender_role, receiver_id, receiver_role, tenant_id, subject, message) VALUES (?, 'admin', ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iiisss', $adminId, $target_recipient_id, $target_role, $target_tenant_id, $subject, $message);
        if($stmt->execute()){
            // Notify Recipient
            include_once('../includes/notification-helper.php');
            sendNotification($target_recipient_id, 'New Message from Admin', "You have a new message from " . ($_SESSION['full_name']), $adminId);

            if($isNew){
                header("Location: conversation.php?client_id=" . $target_recipient_id);
                exit();
            }

            include_once('../includes/toast-helper.php');
            setToast('success', 'Message sent!');
        }
    }

?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat with <?php echo htmlentities($clientName); ?> - Hostel Management System</title>
    <link href="../assets/extra-libs/c3/c3.min.css" rel="stylesheet">
    <link href="../dist/css/style.min.css" rel="stylesheet">`n    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
    <link href="../assets/css/chat-style.css" rel="stylesheet">
    <link href="../dist/css/icons/font-awesome/css/fontawesome-all.min.css" rel="stylesheet">
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
                
                <?php if($isNew): ?>
                <!-- New Chat Form -->
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Start New Conversation</h4>
                                <form method="POST">
                                    <div class="form-group">
                                        <label for="client_id">Select Client:</label>
                                        <select name="client_id" id="client_id" class="form-control" required>
                                            <option value="">-- Select Recipient --</option>
                                            <?php
                                            // Super Admin sees ALL users except self
                                            $c_q = "SELECT id, full_name, email, role FROM users WHERE id != ? ORDER BY role ASC, full_name ASC";
                                            $c_stmt = $mysqli->prepare($c_q);
                                            $c_stmt->bind_param('i', $adminId);
                                            $c_stmt->execute();
                                            $c_res = $c_stmt->get_result();
                                            while($c_row = $c_res->fetch_object()){
                                                $role_label = ucfirst($c_row->role);
                                                echo "<option value='{$c_row->id}'>{$c_row->full_name} ({$c_row->email}) - [{$role_label}]</option>";
                                            }
                                            ?>
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
                                    <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                                    <a href="messages.php" class="btn btn-secondary">Cancel</a>
                                </form>
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
                                <div class="chat-avatar bg-primary text-white mr-3">
                                    <?php echo substr($clientName, 0, 1); ?>
                                </div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold"><?php echo htmlentities($clientName); ?></h5>
                                    <span class="text-muted small">Client</span>
                                </div>
                            </div>
                            
                            <div class="chat-messages" id="chatbox">
                                <?php
                                // Super Admin: show all messages between self and this recipient
                                $query = "SELECT * FROM messages WHERE 
                                          ((sender_id=? AND receiver_id=?) OR 
                                           (sender_id=? AND receiver_id=?))
                                          ORDER BY created_at ASC";
                                $stmt = $mysqli->prepare($query);
                                $stmt->bind_param('iiii', $recipient_id, $adminId, $adminId, $recipient_id);
                                
                                $stmt->execute();
                                $res = $stmt->get_result();
                                
                                if($res->num_rows > 0):
                                    while($row = $res->fetch_object()):
                                        $isMe = ($row->sender_id == $adminId);
                                        $bubbleClass = $isMe ? 'message-sent' : 'message-received';
                                ?>
                                <div class="message-bubble <?php echo $bubbleClass; ?>">
                                    <?php echo nl2br(htmlentities($row->message)); ?>
                                    <span class="message-time"><?php echo date('H:i', strtotime($row->created_at)); ?></span>
                                </div>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <div class="text-center text-muted mt-5">No messages in this conversation yet.</div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" class="chat-input-area">
                                <input type="hidden" name="client_id" value="<?php echo $recipient_id; ?>">
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
            // Scroll to bottom of chat
            var chatbox = document.getElementById("chatbox");
            if(chatbox){
                chatbox.scrollTop = chatbox.scrollHeight;
            }
        });
    </script>
</body>
</html>
