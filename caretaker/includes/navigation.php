<nav class="navbar top-navbar navbar-expand-md">
    <div class="navbar-header" data-logobg="skin6">
        <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
        <div class="navbar-brand">
            <a href="dashboard.php">
                <b class="logo-icon">
                    <img src="../assets/images/logo-icon-nav.png" alt="homepage" class="dark-logo" />
                </b>
                <span class="logo-text">
                    <img src="../assets/images/logo-text-nav.png" alt="homepage" class="dark-logo" />
                </span>
            </a>
        </div>
        <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
            data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
    </div>
    <div class="navbar-collapse collapse" id="navbarSupportedContent">
        <ul class="navbar-nav float-left mr-auto ml-3 pl-1"></ul>
        <ul class="navbar-nav float-right">
            <!-- Notification Bell -->
            <?php
                if (isset($_SESSION['id'])) {
                    $uid = $_SESSION['id'];
                    include_once('../includes/notification-helper.php');
                    $unread = getUnreadNotifications($uid);
                    $nounread = is_array($unread) ? count($unread) : 0;
                    
                    // Fetch real user data
                    $u_stmt = $mysqli->prepare("SELECT full_name, profile_pic FROM users WHERE id = ?");
                    $u_stmt->bind_param('i', $uid);
                    $u_stmt->execute();
                    $u_data = $u_stmt->get_result()->fetch_assoc();
                    $navName = $u_data['full_name'] ?? 'Caretaker';
                    $navPic = ($u_data['profile_pic']) ? "../uploads/profiles/" . $u_data['profile_pic'] : "../assets/images/users/user-icn.png";
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
                                    $count = 0;
                                    foreach ($unread as $notif) {
                                        if($count >= 5) break; 
                                        echo '<a href="my-notifications.php" class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                            <span class="btn btn-primary rounded-circle btn-circle"><i data-feather="info" class="text-white"></i></span>
                                            <div class="w-75 d-inline-block v-middle pl-2">
                                                <h6 class="message-title mb-0 mt-1">'.htmlentities($notif['title']).'</h6>
                                                <span class="font-12 text-nowrap d-block text-muted">'.htmlentities($notif['message']).'</span>
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
                    </ul>
                </div>
            </li>

            <!-- User profile -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <img src="<?php echo $navPic; ?>" alt="user" class="rounded-circle shadow-sm" width="35" height="35" style="object-fit: cover; border: 2px solid #fff;">
                    <span class="ml-2 d-none d-lg-inline-block font-weight-ExtraBold text-dark"><?php echo htmlentities($navName); ?> <i data-feather="chevron-down" class="svg-icon"></i></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                    <a class="dropdown-item" href="profile.php"><i data-feather="user" class="svg-icon mr-2 ml-1"></i> My Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="../landlord/logout.php"><i data-feather="power" class="svg-icon mr-2 ml-1"></i> Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
