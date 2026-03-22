<?php
    include_once '../includes/dbconn.php';
    include_once '../includes/tenant_manager.php';
    $tm = new TenantManager($mysqli);
    $tenantCondition = trim($tm->getTenantWhereClause(false));
    $sql = "SELECT COUNT(DISTINCT id) as occupied FROM rooms WHERE (status = 'booked' OR (room_no, hostel_id) IN (SELECT roomno, hostel_id FROM bookings WHERE booking_status IN ('approved', 'pending')))";
    if (!empty($tenantCondition)) {
        $sql .= " AND " . $tenantCondition;
    }
    $query = $mysqli->query($sql);
    if ($query && $row = $query->fetch_assoc()) {
        echo $row['occupied'] ? $row['occupied'] : "0";
    } else {
        echo "0";
    }
?>
