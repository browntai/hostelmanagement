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
                        <a href="index.html">
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
                        <!-- ============================================================== -->
                        <!-- Notifications -->
                        <!-- ============================================================== -->
                        <?php 
                        include_once('../includes/notification-helper.php');
                        $unread_notifications = getUnreadNotifications($_SESSION['id']);
                        $unread_count = is_array($unread_notifications) ? count($unread_notifications) : 0;
                        ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="javascript:void(0)"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i data-feather="bell" class="svg-icon"></i>
                                <?php if($unread_count > 0): ?>
                                <span class="badge badge-primary notify-no rounded-circle"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right mailbox animated bounceInDown">
                                <ul class="list-style-none">
                                    <li>
                                        <div class="drop-title border-bottom pt-3 pb-2">
                                            Latest Notifications
                                        </div>
                                    </li>
                                    <li>
                                        <div class="message-center notifications position-relative">
                                            <?php if($unread_count > 0): ?>
                                                <?php foreach($unread_notifications as $note): ?>
                                                <a href="my-notifications.php" class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                    <span class="btn btn-primary rounded-circle btn-circle"><i data-feather="bell" class="text-white"></i></span>
                                                    <div class="w-75 d-inline-block v-middle pl-2">
                                                        <h6 class="message-title mb-0 mt-1"><?php echo htmlentities($note['title']); ?></h6>
                                                        <span class="font-12 text-nowrap d-block text-muted text-truncate"><?php echo htmlentities($note['message']); ?></span>
                                                        <span class="font-12 text-nowrap d-block text-muted"><?php echo date('M d, H:i', strtotime($note['created_at'])); ?></span>
                                                    </div>
                                                </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="text-center p-3 text-muted">No new notifications</div>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <li>
                                        <a class="nav-link pt-3 text-center text-dark" href="my-notifications.php">
                                            <strong>Check all notifications</strong>
                                            <i class="fa fa-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- End Notifications -->
                        <!-- ============================================================== -->
                        
                        <!-- ============================================================== -->
                        <!-- User profile -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                    <?php	
                                    $aid=$_SESSION['id'];
                                        $ret="select * from users where id=?";
                                        $stmt= $mysqli->prepare($ret) ;
                                        $stmt->bind_param('i',$aid);
                                        $stmt->execute();
                                        $res=$stmt->get_result();
                                        
                                        while($row=$res->fetch_object())
                                        {
                                            $userPic = $row->profile_pic ? "../uploads/profiles/" . $row->profile_pic : "../assets/images/users/user-icn.png";
                                            ?>
                                <img src="<?php echo $userPic; ?>" alt="user" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
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
