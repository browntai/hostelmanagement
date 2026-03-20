<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $userId = $_SESSION['id'];
    $tenantId = $_SESSION['tenant_id'];

    $recipient_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
    $isNew = isset($_GET['new']) && $_GET['new'] == 'true';
    
    // Fetch current user name for notifications if missing
    if(!isset($_SESSION['full_name'])){
        $curr_q = "SELECT full_name FROM users WHERE id=?";
        $curr_stmt = $mysqli->prepare($curr_q);
        $curr_stmt->bind_param('i', $userId);
        $curr_stmt->execute();
        $c_res = $curr_stmt->get_result();
        if($c_row = $c_res->fetch_object()){
            $_SESSION['full_name'] = $c_row->full_name;
        }
    }
    
    // Fetch client name and role for existing conversation
    $clientName = "New Chat";
    $recipientRole = '';
    if($recipient_id){
        $q_user = "SELECT full_name, role FROM users WHERE id=?";
        $stmt_user = $mysqli->prepare($q_user);
        $stmt_user->bind_param('i', $recipient_id);
        $stmt_user->execute();
        $res_user = $stmt_user->get_result();
        if($clientData = $res_user->fetch_object()){
            $clientName = $clientData->full_name;
            $recipientRole = $clientData->role;
        }
    }

    // Mark as read
    if($recipient_id){
        $update_q = "UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0";
        $stmt_up = $mysqli->prepare($update_q);
        $stmt_up->bind_param('ii', $recipient_id, $userId);
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
        $target_role = 'client';
        if($r_row = $r_res->fetch_object()){
            $target_role = $r_row->role;
        }

        $tenant_id_val = !empty($tenantId) ? $tenantId : null;
        $query = "INSERT INTO messages (sender_id, sender_role, receiver_id, receiver_role, tenant_id, subject, message) VALUES (?, 'caretaker', ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iissss', $userId, $target_recipient_id, $target_role, $tenant_id_val, $subject, $message);
        if($stmt->execute()){
            // Notify Recipient
            include_once('../includes/notification-helper.php');
            $senderName = $_SESSION['full_name'] ?? 'Caretaker';
            sendNotification($target_recipient_id, 'New Message from Caretaker', "You have a new message from " . $senderName, $userId);

            if($isNew){
                header("Location: conversation.php?client_id=" . $target_recipient_id);
                exit();
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
    <title>Chat with <?php echo htmlentities($clientName); ?> - Caretaker Portal</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/chat-style.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
    <link href="../assets/css/custom-gradients.css" rel="stylesheet">
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
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card pub-card shadow-md border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                                    <a href="messages.php" class="btn btn-light btn-circle mr-3"><i class="fas fa-arrow-left"></i></a>
                                    <h4 class="card-title mb-0 font-weight-ExtraBold" style="font-family: var(--theme-highlight-font);">Start New Conversation</h4>
                                </div>
                                <form method="POST">
                                    <div class="form-group mb-4">
                                        <label class="font-weight-ExtraBold text-dark small text-uppercase">Select Recipient</label>
                                        <select name="client_id" class="form-control custom-select" required>
                                            <option value="">-- Choose Person --</option>
                                            <?php
                                            // 1. Fetch Landlord (Owner)
                                            $l_q = "SELECT id, full_name, email FROM users 
                                                   WHERE role='landlord' AND tenant_id = (
                                                       SELECT tenant_id FROM hostels 
                                                       WHERE id = (SELECT assigned_hostel_id FROM users WHERE id = ?)
                                                   ) LIMIT 1";
                                            $l_stmt = $mysqli->prepare($l_q);
                                            if($l_stmt) {
                                                $l_stmt->bind_param('i', $userId);
                                                $l_stmt->execute();
                                                $l_res = $l_stmt->get_result();
                                                if($l_row = $l_res->fetch_object()){
                                                    echo "<option value='{$l_row->id}'>{$l_row->full_name} ({$l_row->email}) - [Landlord]</option>";
                                                }
                                            }

                                            // 2. Fetch clients in the assigned hostel
                                            $c_q = "SELECT DISTINCT u.id, u.full_name, u.email 
                                                   FROM users u 
                                                   JOIN bookings b ON u.email = b.emailid 
                                                   WHERE b.hostel_id = (SELECT assigned_hostel_id FROM users WHERE id = ?)
                                                   ORDER BY u.full_name ASC";
                                            $c_stmt = $mysqli->prepare($c_q);
                                            if($c_stmt) {
                                                $c_stmt->bind_param('i', $userId);
                                                $c_stmt->execute();
                                                $c_res = $c_stmt->get_result();
                                                while($c_row = $c_res->fetch_object()){
                                                    echo "<option value='{$c_row->id}'>{$c_row->full_name} ({$c_row->email}) - [Client]</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="font-weight-ExtraBold text-dark small text-uppercase">Subject / Topic</label>
                                        <input type="text" name="subject" class="form-control" required placeholder="What is this about?">
                                    </div>
                                    <div class="form-group mb-5">
                                        <label class="font-weight-ExtraBold text-dark small text-uppercase">Message</label>
                                        <textarea name="message" class="form-control" rows="5" required placeholder="Write your message here..."></textarea>
                                    </div>
                                    <div class="text-right">
                                        <a href="messages.php" class="btn btn-pub-outline px-4 mr-2">Cancel</a>
                                        <button type="submit" name="send_message" class="btn btn-pub-solid px-5">Send Message <i class="fas fa-paper-plane ml-2"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card pub-card chat-container shadow-md border-0 overflow-hidden">
                            <div class="chat-header d-flex align-items-center p-4 border-bottom bg-white">
                                <a href="messages.php" class="btn btn-light btn-circle mr-3 shadow-sm border"><i class="fas fa-arrow-left"></i></a>
                                <div class="chat-avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle mr-3 shadow-sm" style="width: 55px; height: 55px; font-weight: 800; border: 3px solid #f8f9fa;">
                                    <?php echo substr($clientName, 0, 1); ?>
                                </div>
                                <div class="overflow-hidden">
                                    <h5 class="mb-0 font-weight-ExtraBold text-dark text-truncate" style="font-family: var(--theme-highlight-font);"><?php echo htmlentities($clientName); ?></h5>
                                    <span class="badge badge-light-primary px-2 py-0 small"><?php echo ucfirst($recipientRole); ?></span>
                                </div>
                            </div>
                            
                            <div class="chat-messages p-4" id="chatbox" style="height: 450px; overflow-y: auto;">
                                <?php
                                $query = "SELECT * FROM messages WHERE 
                                          ((sender_id=? AND receiver_id=?) OR 
                                           (sender_id=? AND receiver_id=?))
                                          ORDER BY created_at ASC";
                                $stmt = $mysqli->prepare($query);
                                $stmt->bind_param('iiii', $recipient_id, $userId, $userId, $recipient_id);
                                $stmt->execute();
                                $res = $stmt->get_result();
                                
                                if($res->num_rows > 0):
                                    while($row = $res->fetch_object()):
                                        $isMe = ($row->sender_id == $userId);
                                        $bubbleClass = $isMe ? 'message-sent' : 'message-received';
                                ?>
                                <div class="message-bubble <?php echo $bubbleClass; ?> mb-4 p-3 rounded-xl shadow-sm" style="max-width: 70%; <?php echo $isMe ? 'margin-left: auto; background: var(--pub-gradient); color: #fff; border-bottom-right-radius: 4px;' : 'margin-right: auto; background: #fff; color: #343a40; border-bottom-left-radius: 4px; border: 1px solid rgba(0,0,0,0.05);'; ?>">
                                    <div class="message-text" style="font-size: 0.95rem; line-height: 1.5; font-weight: 500;">
                                        <?php echo nl2br(htmlentities($row->message)); ?>
                                    </div>
                                    <div class="text-right mt-2" style="font-size: 0.7rem; opacity: 0.8; font-weight: 600;">
                                        <?php echo ($isMe ? '<i class="fas fa-check-double mr-1"></i>' : ''); ?>
                                        <?php echo date('h:i A', strtotime($row->created_at)); ?>
                                    </div>
                                </div>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <div class="text-center text-muted mt-5 py-5">
                                    <div class="display-4 text-light mb-3"><i class="fas fa-comments"></i></div>
                                    <p class="font-weight-medium">No messages yet. Send a friendly hello!</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" class="chat-input-area p-4 border-top" style="background: #fdfdfd;">
                                <div class="input-group shadow-sm border rounded-pill overflow-hidden bg-white px-3 py-1">
                                    <textarea name="message" class="form-control border-0 bg-white py-2" rows="1" placeholder="Type your message here..." required style="resize: none; outline: none; box-shadow: none; font-size: 0.95rem;"></textarea>
                                    <div class="input-group-append">
                                        <button type="submit" name="send_message" class="btn btn-link text-primary d-flex align-items-center px-3" style="font-size: 1.25rem;">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
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
            var chatbox = document.getElementById("chatbox");
            if(chatbox){
                chatbox.scrollTop = chatbox.scrollHeight;
            }
        });
    </script>
</body>
</html>
