<nav class="navbar top-navbar navbar-expand-md">
                <div class="navbar-header" data-logobg="skin6">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                            class="ti-menu ti-close"></i></a>
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <div class="navbar-brand">
                        <!-- Logo icon -->
                        <?php
                            $dashLink = 'dashboard.php'; // Landlord dashboard
                            
                            // Handle Global/Broadcast Notification Submission
                            if(isset($_POST['send_broadcast'])) {
                                include_once('../includes/notification-helper.php');
                                include_once('../includes/toast-helper.php');
                                include_once('../includes/tenant_manager.php');
                                
                                $tm = new TenantManager($mysqli);
                                $currentUserId = $_SESSION['id'];
                                $currentUserRole = $_SESSION['role'];
                                $tenantId = $tm->getCurrentTenantId();
                                
                                $title = $_POST['title'];
                                $message = $_POST['message'];
                                $target = $_POST['target'];
                                
                                if($target == 'individual') {
                                    $receiverId = intval($_POST['receiver_id']);
                                    sendNotification($receiverId, $title, $message, $currentUserId);
                                    setToast('success', 'Notification sent to user.');
                                } elseif ($target == 'all_clients') {
                                    broadcastToTenant($currentUserId, $tenantId, $title, $message);
                                    setToast('success', 'Broadcast sent to all clients.');
                                } elseif ($target == 'all_landlords' && $currentUserRole == 'admin') {
                                    $query = "SELECT id FROM users WHERE role = 'landlord'";
                                    $res = $mysqli->query($query);
                                    while($row = $res->fetch_assoc()) {
                                        sendNotification($row['id'], $title, $message, $currentUserId);
                                    }
                                    setToast('success', 'Broadcast sent to all landlords.');
                                } elseif ($target == 'global' && $currentUserRole == 'admin') {
                                    broadcastGlobal($currentUserId, $title, $message);
                                    setToast('success', 'Global broadcast sent.');
                                }
                                
                                // Handle Mark All As Read
                                if(isset($_POST['mark_all_read'])) {
                                    include_once('../includes/notification-helper.php');
                                    markAllAsRead($_SESSION['id']);
                                    echo "<script>window.location.href=window.location.href;</script>";
                                    exit();
                                }
                                
                                // Redirect to refresh and show toast
                                echo "<script>window.location.href=window.location.href;</script>";
                                exit();
                            }
                        ?>
                        <a href="<?php echo $dashLink; ?>">
                            <b class="logo-icon">
                                <!-- Dark Logo icon -->
                                <img src="../assets/images/logo-icon-nav.png" alt="homepage" class="dark-logo" />
                                <!-- Light Logo icon -->
                                <img src="../assets/images/logo-icon-nav.png" alt="homepage" class="light-logo" />
                            </b>
                            <!--End Logo icon -->
                            <!-- Logo text -->
                            <span class="logo-text">
                                <!-- dark Logo text -->
                                <img src="../assets/images/logo-text-nav.png" alt="homepage" class="dark-logo" />
                                <!-- Light Logo text -->
                                <img src="../assets/images/logo-light-text.png" class="light-logo" alt="homepage" />
                            </span>
                        </a>
                    </div>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- Toggle which is visible on mobile only -->
                    <!-- ============================================================== -->
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                        data-toggle="collapse" data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                            class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-left mr-auto ml-3 pl-1">
                        
                        <!-- ============================================================== -->
                        <!-- create new IF REQUIRED-->
                        <!-- ============================================================== -->
                        
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-right">
                        
                        <?php if(isset($_SESSION['impersonate_tenant_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-danger btn-sm text-white mt-3 mr-2" href="../admin/super_impersonate.php?action=stop" style="line-height: 1;">
                                <i class="fas fa-user-secret mr-1"></i> Stop Impersonating
                            </a>
                        </li>
                        <?php endif; ?>
                        <!-- ============================================================== -->
                        <!-- Broadcast Button -->
                        <!-- ============================================================== -->
                        <?php if($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'landlord'): ?>
                        <li class="nav-item">
                            <a class="nav-link pl-md-3" href="javascript:void(0)" data-toggle="modal" data-target="#broadcastModal" title="Send Notification">
                                <span><i class="fas fa-paper-plane" style="font-size: 1.2rem;"></i></span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <!-- ============================================================== -->
                        <!-- Notification Bell -->
                        <!-- ============================================================== -->
                        <?php
                            $nounread = 0;
                            if (isset($_SESSION['id'])) {
                                include_once('../includes/notification-helper.php');
                                include_once('../includes/toast-helper.php');
                                include_once('../includes/log-helper.php');
                                
                                $unread = getUnreadNotifications($_SESSION['id']);
                                $nounread = is_array($unread) ? count($unread) : 0;
                            }
                        ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle pl-md-3 position-relative" href="javascript:void(0)"
                                id="bell" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <span><i class="fas fa-bell" style="font-size: 1.2rem;"></i></span>
                                <?php if($nounread > 0): ?>
                                <span class="badge badge-primary notify-no rounded-circle"><?php echo $nounread; ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-left mailbox animated bounceInDown">
                                <ul class="list-style-none">
                                    <li>
                                        <div class="message-center notifications-position">
                                            <?php
                                            if ($nounread > 0) {
                                                // Show latest 5
                                                $count = 0;
                                                foreach ($unread as $notif) {
                                                    if($count >= 5) break; 
                                                    echo '<a href="my-notifications.php" class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="btn btn-primary rounded-circle btn-circle"><i data-feather="info" class="text-white"></i></span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h6 class="message-title mb-0 mt-1">'.htmlentities($notif['title']).'</h6>
                                                            <span class="font-12 text-nowrap d-block text-muted">'.htmlentities($notif['message']).'</span>
                                                            <span class="font-12 text-nowrap d-block text-muted">'.date("H:i", strtotime($notif['created_at'])).'</span>
                                                        </div>
                                                    </a>';
                                                    $count++;
                                                }
                                            } else {
                                                 echo '<div class="px-3 py-2">No new notifications</div>';
                                            }
                                            ?>
                                        </div>
                                    </li>
                                    <li class="p-2 border-bottom">
                                        <form method="POST" style="display:inline;">
                                            <button type="submit" name="mark_all_read" class="btn btn-sm btn-link text-primary font-weight-bold p-0 ml-2">
                                                <i class="fas fa-check-double mr-1"></i> Mark all as read
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <a class="nav-link pt-3 text-center text-dark" href="my-notifications.php">
                                            <strong>Check all notifications</strong>
                                            <i class="feather-icon" data-feather="arrow-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- ============================================================== -->
                        <!-- User profile -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <img src="../assets/images/users/admin-icn.png" alt="user" class="rounded-circle"
                                    width="35">
                                
                                    <?php	
                                    $aid=$_SESSION['id'];
                                        $ret="SELECT full_name, email from users where id=?";
                                        $stmt= $mysqli->prepare($ret) ;
                                        $stmt->bind_param('i',$aid);
                                        $stmt->execute();
                                        $res=$stmt->get_result();
                                        
                                        while($row=$res->fetch_object())
                                        {
                                            ?>	

                                 <span class="ml-2 d-none d-lg-inline-block"><?php } ?><i data-feather="chevron-down"
                                        class="svg-icon"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                                <a class="dropdown-item" href="profile.php"><i data-feather="user"
                                        class="svg-icon mr-2 ml-1"></i>
                                    My Profile</a>
                                
                                
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="acc-setting.php"><i data-feather="settings"
                                        class="svg-icon mr-2 ml-1"></i>
                                    Account Setting</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php"><i data-feather="power"
                                        class="svg-icon mr-2 ml-1"></i>
                                    Logout</a>
                                
                                
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
