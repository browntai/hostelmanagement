<?php
// Function to create a persistent notification
function sendNotification($receiverId, $title, $message, $senderId = null) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO notifications (sender_id, receiver_id, title, message) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iiss", $senderId, $receiverId, $title, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Function to broadcast to all clients of a specific tenant
function broadcastToTenant($senderId, $tenantId, $title, $message) {
    global $mysqli;
    // Fetch all clients (clients) for this tenant
    $query = "SELECT id FROM users WHERE tenant_id = ? AND role = 'client'";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $tenantId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        sendNotification($row['id'], $title, $message, $senderId);
    }
    $stmt->close();
}

// Function for global broadcast (Super Admin only)
function broadcastGlobal($senderId, $title, $message) {
    global $mysqli;
    // Fetch all users except the sender
    $query = "SELECT id FROM users WHERE id != ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $senderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        sendNotification($row['id'], $title, $message, $senderId);
    }
    $stmt->close();
}

// Function to get unread notifications
function getUnreadNotifications($userId) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM notifications WHERE receiver_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
    return $notifications;
}

// Function to mark notification as read
function markAsRead($notificationId) {
    global $mysqli;
    $stmt = $mysqli->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();
    $stmt->close();
}
// Function to mark all notifications as read for a user
function markAllAsRead($userId) {
    global $mysqli;
    $stmt = $mysqli->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_id = ? AND is_read = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}
?>
