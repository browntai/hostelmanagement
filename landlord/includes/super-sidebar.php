<?php
// Calculate unread messages if not already calculated (since this file is included)
if(!isset($unread_msg_count) && isset($_SESSION['id'])) {
    $unread_msg_count = 0;
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
?>
<nav class="sidebar-nav">
    <ul id="sidebarnav">
        <li class="nav-small-cap"><span class="hide-menu">Main</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/super_dashboard.php" aria-expanded="false">
                <i data-feather="home" class="feather-icon"></i>
                <span class="hide-menu">Dashboard</span>
            </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Landlords</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/manage-tenants.php" aria-expanded="false">
                <i data-feather="grid" class="feather-icon"></i>
                <span class="hide-menu">Landlords</span>
            </a>
        </li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/manage-approvals.php" aria-expanded="false">
                <i data-feather="check-circle" class="feather-icon"></i>
                <span class="hide-menu">Approvals</span>
            </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Inventory</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/manage-hostels.php" aria-expanded="false">
                <i data-feather="database" class="feather-icon"></i>
                <span class="hide-menu">All Hostels</span>
            </a>
        </li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/manage-rooms.php" aria-expanded="false">
                <i data-feather="monitor" class="feather-icon"></i>
                <span class="hide-menu">All Rooms</span>
            </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Operations</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/bookings.php" aria-expanded="false">
                <i data-feather="book" class="feather-icon"></i>
                <span class="hide-menu">Manual Booking</span>
            </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Registrations</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/manage-clients.php" aria-expanded="false">
                <i data-feather="users" class="feather-icon"></i>
                <span class="hide-menu">All Bookings</span>
            </a>
        </li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/view-clients-acc.php" aria-expanded="false">
                <i data-feather="user" class="feather-icon"></i>
                <span class="hide-menu">View Client Accounts</span>
            </a>
        </li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/manage-users.php" aria-expanded="false">
                <i data-feather="user-check" class="feather-icon"></i>
                <span class="hide-menu">Manage Users</span>
            </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Finance</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/payments.php" aria-expanded="false">
                <i data-feather="dollar-sign" class="feather-icon"></i>
                <span class="hide-menu">System Payments</span>
            </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Communications</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/messages.php" aria-expanded="false" onclick="suppressBadge('#sidebar-messages-count-super');">
                <i data-feather="message-square" class="feather-icon"></i>
                <span class="hide-menu">Messages</span><span class="badge badge-primary rounded-circle ml-auto" id="sidebar-messages-count-super" <?php if($unread_msg_count==0) echo 'style="display:none;"'; ?>><?php echo $unread_msg_count; ?></span>
            </a>
        </li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/all-notifications.php" aria-expanded="false">
                <i data-feather="bell" class="feather-icon"></i>
                <span class="hide-menu">Broadcast Alert</span>
            </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">System</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/activity-logs.php" aria-expanded="false">
                <i data-feather="activity" class="feather-icon"></i>
                <span class="hide-menu">Activity Logs</span>
            </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Account</span></li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/profile.php" aria-expanded="false">
                <i data-feather="user" class="feather-icon"></i>
                <span class="hide-menu">My Profile</span>
            </a>
        </li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/acc-setting.php" aria-expanded="false">
                <i data-feather="settings" class="feather-icon"></i>
                <span class="hide-menu">Account Settings</span>
            </a>
        </li>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../admin/logout.php" aria-expanded="false">
                <i data-feather="log-out" class="feather-icon"></i>
                <span class="hide-menu">Logout</span>
            </a>
        </li>
    </ul>
</nav>
