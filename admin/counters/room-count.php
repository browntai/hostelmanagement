<?php
    include_once '../includes/dbconn.php';
    include_once '../includes/tenant_manager.php';
    $tm = new TenantManager($mysqli);
    $sql = "SELECT id FROM rooms";
    $sql .= $tm->getTenantWhereClause(true); // Appends " WHERE tenant_id = '...'" or ""
    $query = $mysqli->query($sql);
    echo "$query->num_rows";
?>
