<?php
session_start();
header('Content-Type: application/json');
include('../includes/dbconn.php');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;

if ($image_id <= 0) {
    echo json_encode(['error' => 'Invalid image ID']);
    exit;
}

// Verify image exists and belongs to an approved hostel
$check = $mysqli->prepare("SELECT hi.id FROM hostel_images hi 
                           JOIN hostels h ON hi.hostel_id = h.id 
                           WHERE hi.id = ? AND h.status = 'approved'");
$check->bind_param('i', $image_id);
$check->execute();
if (!$check->get_result()->fetch_object()) {
    echo json_encode(['error' => 'Image not found']);
    exit;
}

$sess_id = session_id();
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Check if already liked
$stmt = $mysqli->prepare("SELECT id FROM image_likes WHERE image_id = ? AND session_id = ?");
$stmt->bind_param('is', $image_id, $sess_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_object();

if ($existing) {
    // Unlike
    $del = $mysqli->prepare("DELETE FROM image_likes WHERE id = ?");
    $del->bind_param('i', $existing->id);
    $del->execute();
    $liked = false;
} else {
    // Like
    $ins = $mysqli->prepare("INSERT INTO image_likes (image_id, session_id, ip_address) VALUES (?, ?, ?)");
    $ins->bind_param('iss', $image_id, $sess_id, $ip);
    $ins->execute();
    $liked = true;
}

// Get updated count
$cnt = $mysqli->prepare("SELECT COUNT(*) as c FROM image_likes WHERE image_id = ?");
$cnt->bind_param('i', $image_id);
$cnt->execute();
$count = $cnt->get_result()->fetch_object()->c;

echo json_encode(['liked' => $liked, 'count' => (int)$count]);
?>
