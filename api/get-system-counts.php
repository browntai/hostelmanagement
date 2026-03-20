<?php
session_start();
include('../includes/dbconn.php');

if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$userId = $_SESSION['id'];
$role = $_SESSION['role'];
$tenantId = isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : null;

$response = [
    'unread_notifications' => 0,
    'unread_messages' => 0,
    'pending_bookings' => 0,
    'total_clients' => 0,
    'total_rooms' => 0,
    'occupied_rooms' => 0
];

// 1. Unread Notifications
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM notifications WHERE receiver_id = ? AND is_read = 0");
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($response['unread_notifications']);
$stmt->fetch();
$stmt->close();

// 2. Unread Messages
if ($role == 'client') {
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND receiver_role = 'client' AND is_read = 0");
    $stmt->bind_param('i', $userId);
} else {
    // For landlord or admin
    if ($role == 'admin' || $role == 'landlord') {
        if ($tenantId) {
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM messages WHERE receiver_role = 'admin' AND tenant_id = ? AND is_read = 0");
            $stmt->bind_param('i', $tenantId);
        } else {
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM messages WHERE receiver_role = 'admin' AND tenant_id IS NULL AND is_read = 0");
        }
    }
}
if (isset($stmt) && $stmt) {
    $stmt->execute();
    $stmt->bind_result($response['unread_messages']);
    $stmt->fetch();
    $stmt->close();
}

// 3. Pending Bookings & Total Clients
if ($role == 'landlord' || $role == 'admin') {
    // Pending Bookings
    if ($tenantId) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM bookings WHERE booking_status = 'pending' AND tenant_id = ?");
        $stmt->bind_param('i', $tenantId);
    } else {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM bookings WHERE booking_status = 'pending'");
    }
    $stmt->execute();
    $stmt->bind_result($response['pending_bookings']);
    $stmt->fetch();
    $stmt->close();

    // Total Clients (counting from bookings)
    if ($tenantId) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM bookings WHERE tenant_id = ?");
        $stmt->bind_param('i', $tenantId);
    } else {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM bookings");
    }
    $stmt->execute();
    $stmt->bind_result($response['total_clients']);
    $stmt->fetch();
    $stmt->close();

    // Total Rooms
    if ($tenantId) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM rooms WHERE tenant_id = ?");
        $stmt->bind_param('i', $tenantId);
    } else {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM rooms");
    }
    $stmt->execute();
    $stmt->bind_result($response['total_rooms']);
    $stmt->fetch();
    $stmt->close();

    // Occupied Rooms
    if ($tenantId) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM bookings WHERE booking_status = 'approved' AND tenant_id = ?");
        $stmt->bind_param('i', $tenantId);
    } else {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM bookings WHERE booking_status = 'approved'");
    }
    $stmt->execute();
    $stmt->bind_result($response['occupied_rooms']);
    $stmt->fetch();
    $stmt->close();
}

// 4. Latest Unread Notifications (for real-time toasts)
$response['unread_list'] = [];
$stmt = $mysqli->prepare("SELECT id, title, message, created_at FROM notifications WHERE receiver_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param('i', $userId);
$stmt->execute();
$resultArr = $stmt->get_result();
while ($row = $resultArr->fetch_assoc()) {
    $response['unread_list'][] = $row;
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($response);
?>
