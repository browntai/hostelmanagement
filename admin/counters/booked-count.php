<?php
    include_once '../includes/dbconn.php';
    include_once '../includes/tenant_manager.php';
    $tm = new TenantManager($mysqli);
    $sql = "SELECT id FROM bookings";
    $sql .= $tm->getTenantWhereClause(true);
    $query = $mysqli->query($sql);
    echo "$query->num_rows";
?>
