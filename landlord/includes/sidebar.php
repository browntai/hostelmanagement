<?php
// If Super Admin and NOT impersonating, show Super Admin sidebar instead
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && !isset($_SESSION['impersonate_tenant_id'])) {
    include 'super-sidebar.php';
    return;
}
?>
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

// Check if daycare is enabled for any of the landlord's hostels
$any_daycare_enabled = false;
if(isset($_SESSION['tenant_id'])) {
    $tid = $_SESSION['tenant_id'];
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM hostel_services WHERE is_enabled=1 AND service_key='daycare' AND hostel_id IN (SELECT id FROM hostels WHERE tenant_id=?)");
    if($stmt) {
        $stmt->bind_param('i', $tid);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_object()) {
            $any_daycare_enabled = ($row->cnt > 0);
        }
        $stmt->close();
    }
}
?>
<!-- Sidebar navigation-->
<nav class="sidebar-nav">

    <ul id="sidebarnav">
    
        <?php if(isset($_SESSION['impersonate_tenant_id'])): ?>
        <li class="sidebar-item" style="background-color: #ffcccc;"> 
            <a class="sidebar-link sidebar-link" href="../admin/super_impersonate.php?action=stop" aria-expanded="false">
                <i class="fas fa-arrow-left text-danger"></i>
                <span class="hide-menu text-danger">Exit Impersonation</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="dashboard.php"
        aria-expanded="false"><i data-feather="home" class="feather-icon"></i><span
         class="hide-menu">Dashboard</span></a></li>

        <li class="list-divider"></li>

        <li class="nav-small-cap"><span class="hide-menu">Operations</span></li>
                            
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="register-caretaker.php"
        aria-expanded="false"><i data-feather="user-plus" class="feather-icon"></i><span
        class="hide-menu">Add Caretaker</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="register-client.php"
        aria-expanded="false"><i data-feather="user-plus" class="feather-icon"></i><span
        class="hide-menu">Add Client</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="manage-caretakers.php"
        aria-expanded="false"><i data-feather="shield" class="feather-icon"></i><span
        class="hide-menu">Caretakers</span></a></li>

        <?php 
        include_once '../includes/tenant_manager.php';
        $tm_sidebar = new TenantManager($mysqli);
        if($tm_sidebar->isSuperAdmin()): ?>
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="view-clients-acc.php"
        aria-expanded="false"><i data-feather="user" class="feather-icon"></i><span
        class="hide-menu">Client Acc.</span></a></li>
        <?php endif; ?>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="bookings.php"
        aria-expanded="false"><i data-feather="book" class="feather-icon"></i><span
        class="hide-menu">Book Room</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="booking-requests.php" onclick="suppressBadge('#sidebar-bookings-count');"
        aria-expanded="false"><i data-feather="mail" class="feather-icon"></i><span
        class="hide-menu">Requests</span><span class="badge badge-primary rounded-circle ml-auto" id="sidebar-bookings-count" style="display:none;">0</span></a></li>

        <li class="nav-small-cap"><span class="hide-menu">Management</span></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="manage-clients.php" onclick="suppressBadge('#sidebar-clients-count');"
        aria-expanded="false"><i data-feather="users" class="feather-icon"></i><span
        class="hide-menu">Hostel Clients</span><span class="badge badge-info rounded-circle ml-auto" id="sidebar-clients-count" style="display:none;">0</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="manage-hostels.php"
        aria-expanded="false"><i data-feather="database" class="feather-icon"></i><span
        class="hide-menu">Manage Hostels</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="manage-rooms.php"
        aria-expanded="false"><i data-feather="monitor" class="feather-icon"></i><span
        class="hide-menu">Manage Rooms</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="maintenance-requests.php"
        aria-expanded="false"><i data-feather="settings" class="feather-icon"></i><span
        class="hide-menu">Maintenance</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="manage-services.php"
        aria-expanded="false"><i data-feather="grid" class="feather-icon"></i><span
        class="hide-menu">Property Services</span></a></li>

        <?php if($any_daycare_enabled): ?>
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="daycare-attendance.php"
        aria-expanded="false"><i data-feather="calendar" class="feather-icon"></i><span
        class="hide-menu">Daycare Attendance</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="all-children.php"
        aria-expanded="false"><i data-feather="smile" class="feather-icon"></i><span
        class="hide-menu">Children Directory</span></a></li>
        <?php endif; ?>

        <li class="nav-small-cap"><span class="hide-menu">Finance</span></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="payments.php"
        aria-expanded="false"><i data-feather="dollar-sign" class="feather-icon"></i><span
        class="hide-menu">Verify Payments</span></a></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="payment-settings.php"
        aria-expanded="false"><i data-feather="settings" class="feather-icon"></i><span
        class="hide-menu">Settings</span></a></li>

        <li class="nav-small-cap"><span class="hide-menu">Communication</span></li>

        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="messages.php" onclick="suppressBadge('#sidebar-messages-count');"
        aria-expanded="false"><i data-feather="message-square" class="feather-icon"></i><span
        class="hide-menu">Messages</span><span class="badge badge-primary rounded-circle ml-auto" id="sidebar-messages-count" <?php if($unread_msg_count==0) echo 'style="display:none;"'; ?>><?php echo $unread_msg_count; ?></span></a></li>

        <li class="list-divider"></li>
        <li class="nav-small-cap"><span class="hide-menu">System</span></li>
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="activity-logs.php"
        aria-expanded="false"><i data-feather="activity" class="feather-icon"></i><span
        class="hide-menu">Activity Logs</span></a></li>
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="my-notifications.php" onclick="suppressBadge('#sidebar-notifications-count');"
        aria-expanded="false"><i data-feather="bell" class="feather-icon"></i><span
        class="hide-menu">Notifications</span><span class="badge badge-danger rounded-circle ml-auto" id="sidebar-notifications-count" style="display:none;">0</span></a></li>
        <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="sent-notifications.php"
        aria-expanded="false"><i data-feather="send" class="feather-icon"></i><span
        class="hide-menu">Sent Notifications</span></a></li>
                           
    </ul>
</nav>
<!-- End Sidebar navigation -->
