<?php
// Restricted Sidebar for Caretakers
$caretaker_daycare_enabled = false;
if(isset($_SESSION['id'])) {
    $uid = $_SESSION['id'];
    $stmt = $mysqli->prepare("SELECT is_enabled FROM hostel_services hs 
                              JOIN users u ON hs.hostel_id = u.assigned_hostel_id 
                              WHERE u.id = ? AND hs.service_key = 'daycare'");
    if($stmt) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if($row = $res->fetch_object()) {
            $caretaker_daycare_enabled = (bool)$row->is_enabled;
        }
        $stmt->close();
    }
}
?>
<nav class="sidebar-nav">
    <ul id="sidebarnav">
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="dashboard.php" aria-expanded="false">
                <i data-feather="home" class="feather-icon"></i><span class="hide-menu">Dashboard</span>
            </a>
        </li>

        <li class="list-divider"></li>

        <li class="nav-small-cap"><span class="hide-menu">Hostel Operations</span></li>
                            
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="manage-bookings.php" aria-expanded="false">
                <i data-feather="book" class="feather-icon"></i><span class="hide-menu">Bookings</span>
            </a>
        </li>

        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="maintenance.php" aria-expanded="false">
                <i data-feather="settings" class="feather-icon"></i><span class="hide-menu">Maintenance</span>
            </a>
        </li>

        <?php if($caretaker_daycare_enabled): ?>
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="daycare-attendance.php" aria-expanded="false">
                <i data-feather="calendar" class="feather-icon"></i><span class="hide-menu">Daycare Attendance</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="messages.php" aria-expanded="false">
                <i data-feather="message-square" class="feather-icon"></i><span class="hide-menu">Messages</span>
            </a>
        </li>

        <li class="list-divider"></li>
        
        <li class="sidebar-item"> 
            <a class="sidebar-link sidebar-link" href="../landlord/logout.php" aria-expanded="false">
                <i data-feather="log-out" class="feather-icon"></i><span class="hide-menu">Logout</span>
            </a>
        </li>
    </ul>
</nav>
