<?php
session_start();
include('../includes/dbconn.php');

// Security Check: Only Super Admin can access this
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("location:index.php");
    exit();
}

if (isset($_GET['tenant_id'])) {
    $tenantId = intval($_GET['tenant_id']);
    
    // verify tenant exists
    $sql = "SELECT * FROM tenants WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $tenantId);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION['impersonate_tenant_id'] = $tenantId;
        // Redirect to landlord dashboard
        header("location:../landlord/dashboard.php");
        exit();
    } else {
        echo "Tenant not found.";
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'stop') {
    // Stop impersonation
    unset($_SESSION['impersonate_tenant_id']);
    header("location:super_dashboard.php");
    exit();
}

?>
