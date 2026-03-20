<?php
    include_once '../includes/dbconn.php';
    include_once '../includes/tenant_manager.php';
    $tm = new TenantManager($mysqli);
    $sql = "SELECT id FROM bookings WHERE booking_status = 'approved'";
    $sql .= " AND " . $tm->getTenantWhereClause(false); // Pass false because we added WHERE already
    $query = $mysqli->query($sql);
    echo "$query->num_rows";
?>
