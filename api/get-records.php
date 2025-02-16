<?php
error_reporting(E_ALL ^ E_NOTICE);  

header("Content-Type: application/json");
require_once '../config.php' ;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Could not connect to the database']);
    exit;
}

$token = $headers['Authorization'] ?? '';

if (empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'Token is required']);
    exit;
}

// Validate the token
$query = "SELECT access_expires_at FROM user_tokens WHERE access_token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
    exit;
}

$tokenData = $result->fetch_assoc();

// Check if the token has expired
if (time() > $tokenData['access_expires_at']) {
    echo json_encode(['status' => 'error', 'message' => 'Token has expired']);
    exit;
}

// Fetch the user's records
$query = "SELECT * FROM user_records";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$records = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'status' => 'success',
    'message' => 'Records fetched successfully',
    'records' => $records
]);

$stmt->close();
$conn->close();