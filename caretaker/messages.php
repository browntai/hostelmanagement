<?php
    session_start();
    include('../includes/dbconn.php');
    include('../includes/check-login.php');
    check_login();

    $userId = $_SESSION['id'];
    $tenantId = $_SESSION['tenant_id'];

    // Handle reply if needed (though conversation.php usually handles this)
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages - Caretaker Portal</title>
    <link href="../dist/css/style.min.css" rel="stylesheet">
    <link href="../assets/css/chat-style.css" rel="stylesheet">
    <link href="../assets/css/public-pages.css" rel="stylesheet">
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
                <div class="row mb-4">
                    <div class="col-7 align-self-center">
                        <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Messages</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card pub-card shadow-md border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                                    <h4 class="card-title mb-0 font-weight-ExtraBold" style="font-family: var(--theme-highlight-font);">Inbox</h4>
                                    <a href="conversation.php?new=true" class="btn btn-pub-solid shadow-sm">
                                        <i class="fas fa-plus mr-1"></i> New Chat
                                    </a>
                                </div>
                                <div class="list-group list-group-flush">
                                    <?php
                                    $hasConvos = false;

                                    // Get unique conversations
                                    $query = "SELECT m.*, u.full_name, u.id as other_id, u.role as other_role
                                              FROM messages m 
                                              JOIN users u ON (
                                                  (m.sender_id = u.id AND m.receiver_id = ?) OR 
                                                  (m.receiver_id = u.id AND m.sender_id = ?)
                                              )
                                              WHERE m.id IN (
                                                  SELECT MAX(id) 
                                                  FROM messages 
                                                  WHERE (sender_id = ? OR receiver_id = ?)
                                                  GROUP BY IF(sender_id=?, receiver_id, sender_id)
                                              )
                                              ORDER BY m.created_at DESC";
                                    $stmt = $mysqli->prepare($query);
                                    $stmt->bind_param('iiiii', $userId, $userId, $userId, $userId, $userId);
                                    $stmt->execute();
                                    $res = $stmt->get_result();
                                    
                                    while($row = $res->fetch_object()):
                                        $hasConvos = true;
                                        $displayName = $row->full_name;
                                        $otherId = $row->other_id;
                                        $otherRole = ucfirst($row->other_role);
                                        $initial = strtoupper(substr($displayName, 0, 1));
                                    ?>
                                    <a href="conversation.php?client_id=<?php echo $otherId; ?>" class="list-group-item list-group-item-action chat-list-item border-bottom-0 mb-3 px-4 py-3 rounded shadow-sm border" style="border-color: rgba(0,0,0,0.05) !important;">
                                        <div class="d-flex align-items-center">
                                            <div class="chat-avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 52px; height: 52px; flex-shrink: 0; font-weight: 800; border: 2px solid #fff;">
                                                <?php echo $initial; ?>
                                            </div>
                                            <div class="ml-3 w-100 overflow-hidden">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <h6 class="mb-0 font-weight-ExtraBold text-dark"><?php echo htmlentities($displayName); ?> 
                                                        <span class="badge badge-light-secondary small ml-1" style="font-size: 10px;"><?php echo $otherRole; ?></span>
                                                    </h6>
                                                    <small class="text-muted font-weight-medium bg-light px-2 py-1 rounded"><?php echo date('M d, H:i', strtotime($row->created_at)); ?></small>
                                                </div>
                                                <div class="text-truncate text-muted small mt-1">
                                                    <?php echo ($row->sender_id == $userId) ? '<i class="fas fa-reply mr-1 small"></i>' : ''; ?>
                                                    <?php echo htmlentities($row->message); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <?php 
                                        endwhile;

                                    if(!$hasConvos):
                                    ?>
                                    <div class="p-5 text-center text-muted">
                                        <i class="fas fa-comments display-4 mb-3 d-block"></i>
                                        <p>No messages found. Start a conversation with your landlord or tenants.</p>
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
