<?php
header("Content-Type: application/json");
require_once '../config.php' ;

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

if (!password_verify($password . HASH_PEPPER, $user['password_hash'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    exit;
}

try {
  $accessToken = bin2hex(random_bytes(32));
  $accessExpiration = time() + 604800; // 7 days 

  $query = "INSERT INTO user_tokens (user_id, access_token, access_expires_at) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('isi', $user['id'], $accessToken, $accessExpiration);
  $stmt->execute();

  echo json_encode([
    'status' => 'success',
    'message' => 'Login successful',
    'access_token' => $accessToken,
    'expires_in' => 604800// Token expiration time in seconds
]);
}
catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'There were problems get from the database. Try again later!']);
}

$stmt->close();
$conn->close();