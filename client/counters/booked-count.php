<?php
    include '../includes/dbconn.php';

    $tenantId = $_SESSION['tenant_id'];
    $sql = "SELECT id FROM bookings WHERE tenant_id = '$tenantId'";
    $query = $mysqli->query($sql);
    echo "$query->num_rows";
?>
