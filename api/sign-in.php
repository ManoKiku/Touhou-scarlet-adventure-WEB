<?php
header("Content-Type: application/json");
require_once '../config.php' ;

// Connect to MySQL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Could not connect to the database']);
    exit;
}

$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
    exit;
}

// Fetch user from the database
$query = "SELECT id, username, password_hash FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}


$user = $result->fetch_assoc();

// Verify the password
if (!password_verify($password . HASH_PEPPER, $user['password_hash'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    exit;
}

try {
// Generate tokens
$accessToken = bin2hex(random_bytes(32)); // 64-character random string
$refreshToken = bin2hex(random_bytes(32)); // 64-character random string
$accessExpiration = time() + 3600; // 1 hour
$refreshExpiration = time() + 604800; // 7 days

// Store tokens in the database
$query = "INSERT INTO user_tokens (user_id, access_token, refresh_token, access_expires_at, refresh_expires_at) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('isssi', $user['id'], $accessToken, $refreshToken, $accessExpiration, $refreshExpiration);
$stmt->execute();

// Return tokens to the user
echo json_encode([
    'status' => 'success',
    'message' => 'Login successful',
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken,
    'expires_in' => 3600 // Token expiration time in seconds
]);
}
catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'There were problems get from the database. Try again later!']);
}

$stmt->close();
$conn->close();