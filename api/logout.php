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

$query = "DELETE FROM user_tokens WHERE access_token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
}

$stmt->close();
$conn->close();