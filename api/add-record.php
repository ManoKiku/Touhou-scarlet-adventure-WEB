<?php
header("Content-Type: application/json");

require_once '../config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Could not connect to the database']);
    exit;
}

$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if (empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'Authorization token is required']);
    exit;
}

$query = "SELECT user_id FROM user_tokens WHERE access_token = ? AND access_expires_at > ?";
$stmt = $conn->prepare($query);
$currentTime = time();
$stmt->bind_param('si', $token, $currentTime);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
    exit;
}

$tokenData = $result->fetch_assoc();
$userId = $tokenData['user_id'];

$recordScore = $_GET['score'] ?? '';

if (empty($recordScore)) {
    echo json_encode(['status' => 'error', 'message' => 'Data is required']);
    exit;
}

$query = "INSERT INTO user_records (user_id, score) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('is', $userId, $recordScore);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Record added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add record']);
}

$stmt->close();
$conn->close();