<?php
header("Content-Type: application/json");
require_once '../config.php' ;

// Connect to MySQL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Could not connect to the database']);
    exit;
}

// Get the refresh token from the request
$refreshToken = $_GET['refresh_token'] ?? '';

if (empty($refreshToken)) {
    echo json_encode(['status' => 'error', 'message' => 'Refresh token is required']);
    exit;
}

// Validate the refresh token
$query = "SELECT user_id, refresh_expires_at FROM user_tokens WHERE refresh_token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $refreshToken);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid refresh token']);
    exit;
}

$tokenData = $result->fetch_assoc();

// Check if the refresh token has expired
if (time() > $tokenData['refresh_expires_at']) {
    echo json_encode(['status' => 'error', 'message' => 'Refresh token has expired']);

    $query = "DELETE FROM user_tokens WHERE refresh_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $refreshToken);
    $stmt->execute();
    exit;
}

// Generate a new access token
$newAccessToken = bin2hex(random_bytes(32));
$newAccessExpiration = time() + 3600; // 1 hour

// Update the access token in the database
$query = "UPDATE user_tokens SET access_token = ?, access_expires_at = ? WHERE refresh_token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('sis', $newAccessToken, $newAccessExpiration, $refreshToken);
$stmt->execute();

// Return the new access token
echo json_encode([
    'status' => 'success',
    'message' => 'Access token refreshed',
    'access_token' => $newAccessToken,
    'expires_in' => 3600
]);

$stmt->close();
$conn->close();