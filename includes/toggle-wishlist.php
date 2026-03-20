<?php
error_reporting(0);
header('Content-Type: application/json');
session_start();
include 'dbconn.php';

if(!isset($_SESSION['id']) || !isset($_POST['hostel_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$client_id = $_SESSION['id'];
$hostel_id = intval($_POST['hostel_id']);

// Check if exists
$query = "SELECT id FROM wishlist WHERE student_id = ? AND hostel_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ii', $client_id, $hostel_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows > 0) {
    // Remove
    $query = "DELETE FROM wishlist WHERE student_id = ? AND hostel_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ii', $client_id, $hostel_id);
    $stmt->execute();
    echo json_encode(['status' => 'removed']);
} else {
    // Add
    $query = "INSERT INTO wishlist (student_id, hostel_id) VALUES (?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ii', $client_id, $hostel_id);
    $stmt->execute();
    echo json_encode(['status' => 'added']);
}
?>
