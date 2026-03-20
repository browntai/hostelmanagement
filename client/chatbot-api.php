<?php
session_start();
include('../includes/dbconn.php');
include('../includes/ai-helper.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $question = $input['question'] ?? '';

    if (empty(trim($question))) {
        echo json_encode(['answer' => 'Please type a question!']);
        exit;
    }

    $answer = getChatbotResponse($question);
    echo json_encode(['answer' => $answer]);
} else {
    echo json_encode(['error' => 'POST required']);
}
?>
