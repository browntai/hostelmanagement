<?php
    include_once '../includes/dbconn.php';
    include_once '../includes/tenant_manager.php';
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();
    $sql = "SELECT id FROM bookings";
    // Check if we need to filter
    if ($tenantId !== null) {
         $sql .= " WHERE tenant_id = '$tenantId'";
    }
    
    $query = $mysqli->query($sql);
    echo "$query->num_rows";
?>
