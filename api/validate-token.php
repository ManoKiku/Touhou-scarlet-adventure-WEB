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

$query = "SELECT user_id, access_expires_at FROM user_tokens WHERE access_token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
    exit;
}

$tokenData = $result->fetch_assoc();
$userId = $tokenData['user_id'];
$expiresAt = $tokenData['access_expires_at'];

if (time() > $expiresAt) {
    echo json_encode(['status' => 'error', 'message' => 'Token has expired']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Token is valid',
    'user_id' => $userId
]);

$stmt->close();
$conn->close();