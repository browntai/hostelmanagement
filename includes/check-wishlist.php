<?php
error_reporting(0);
header('Content-Type: application/json');
session_start();
include 'dbconn.php';

if(!isset($_SESSION['id']) || !isset($_GET['hostel_id'])) {
    echo json_encode(['exists' => false]);
    exit;
}

$client_id = $_SESSION['id'];
$hostel_id = intval($_GET['hostel_id']);

$query = "SELECT id FROM wishlist WHERE student_id = ? AND hostel_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param('ii', $client_id, $hostel_id);
$stmt->execute();
$res = $stmt->get_result();

echo json_encode(['exists' => $res->num_rows > 0]);
?>
