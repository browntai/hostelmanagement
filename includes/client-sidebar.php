<?php
// Calculate unread messages
$unread_msg_count = 0;
if(isset($_SESSION['id'])) {
    $uid = $_SESSION['id'];
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM messages WHERE receiver_id=? AND is_read=0");
    if($stmt) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_object()) {
            $unread_msg_count = $row->cnt;
        }
        $stmt->close();
    }
}

// Check if daycare is enabled for the current tenant's hostel
$daycare_enabled = false;
$tenant_hostel_id = null;
if(isset($_SESSION['login'])) {
    $email = $_SESSION['login'];
    $stmt = $mysqli->prepare("SELECT hostel_id FROM bookings WHERE emailid=? AND booking_status IN ('confirmed', 'approved') ORDER BY postingDate DESC LIMIT 1");
    if($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_object()) {
            $tenant_hostel_id = $row->hostel_id;
            include_once(dirname(__FILE__) . '/hostel-helper.php');
            $daycare_enabled = isServiceEnabled($mysqli, $tenant_hostel_id, 'daycare');
        }
        $stmt->close();
    }
}
?>
<!-- Sidebar navigation-->
<nav class="sidebar-nav">

    <ul id="sidebarnav">
    
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="dashboard.php"
        aria-expanded="false"><i data-feather="home" class="feather-icon"></i><span
         class="hide-menu">Dashboard</span></a></li>

        <li class="list-divider"></li>

        <li class="nav-small-cap"><span class="hide-menu">Features</span></li>
                            
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="../index.php"
        aria-expanded="false"><i class="fas fa-building"></i><span
        class="hide-menu">Hostels</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="book-hostel.php"
        aria-expanded="false"><i class="fas fa-h-square"></i><span
        class="hide-menu">Book Hostel</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="room-details.php"
        aria-expanded="false"><i class="fas fa-bed"></i><span
        class="hide-menu">My Room Details</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="log-activity.php"
        aria-expanded="false"><i class="fas fa-cogs"></i><span
        class="hide-menu">Log Activities</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="javascript:void(0)" data-toggle="modal" data-target="#reviewModal"
        aria-expanded="false"><i class="fas fa-star"></i><span
        class="hide-menu">Write Review</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="maintenance-requests.php"
        aria-expanded="false"><i class="fas fa-tools"></i><span
        class="hide-menu">Maintenance</span></a></li>

        <?php if($daycare_enabled): ?>
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="register-child.php"
        aria-expanded="false"><i class="fas fa-child"></i><span
        class="hide-menu">Child Registration</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="daycare-service.php"
        aria-expanded="false"><i class="fas fa-calendar-check"></i><span
        class="hide-menu">Daycare Service</span></a></li>
        <?php endif; ?>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="messages.php" onclick="$('#sidebar-messages-count').hide();"
        aria-expanded="false"><i class="fas fa-comments"></i><span
        class="hide-menu">Messages</span>
        <span class="badge badge-primary rounded-circle ml-auto" id="sidebar-messages-count" <?php if($unread_msg_count==0) echo 'style="display:none;"'; ?>><?php echo $unread_msg_count; ?></span>
        </a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="make-payment.php"
        aria-expanded="false"><i class="fas fa-credit-card"></i><span
        class="hide-menu">Payments</span></a></li>

        <li class="list-divider"></li>
        <li class="nav-small-cap"><span class="hide-menu">System</span></li>
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="my-notifications.php" onclick="$('#sidebar-notifications-count').hide();"
        aria-expanded="false"><i class="fas fa-bell"></i><span
        class="hide-menu">Notifications</span>
        <span class="badge badge-danger rounded-circle ml-auto" id="sidebar-notifications-count" style="display:none;"></span>
        </a></li>
                           
    </ul>
</nav>
<!-- End Sidebar navigation -->
