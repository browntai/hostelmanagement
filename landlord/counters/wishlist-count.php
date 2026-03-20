<?php
    include '../includes/dbconn.php';
    include_once '../includes/tenant_manager.php';
    $tm = new TenantManager($mysqli);
    $tenantId = $tm->getCurrentTenantId();

    $query = "SELECT count(*) 
              FROM wishlist 
              INNER JOIN hostels ON wishlist.hostel_id = hostels.id 
              WHERE hostels.tenant_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $tenantId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_row();
    echo $row[0];
?>
