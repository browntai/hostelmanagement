<?php
// Function to Log Activity
function logActivity($userId, $userEmail, $role, $action, $details) {
    global $mysqli;
    
    // Get IP Address
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    $stmt = $mysqli->prepare("INSERT INTO user_activity_logs (user_id, user_email, role, action, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isssss", $userId, $userEmail, $role, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
?>
