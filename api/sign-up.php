<?php
header("Content-Type: application/json");

require_once '../config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Could not connect to the database']);
    exit;
}

$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';

if (empty($username)) {
    echo json_encode(['status' => 'error', 'message' => 'Username is required']);
    exit;
} elseif (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
    echo json_encode(['status' => 'error', 'message' => 'Username must be 4-20 characters long and contain only letters, numbers, and underscores']);
    exit;
}

if (empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password is required']);
    exit;
} elseif (strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
    exit;
} elseif (!preg_match('/[0-9]/', $password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one number']);
    exit;
} elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must contain at least one special character']);
    exit;
}

$query = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
    exit;
}

$hashedPassword = password_hash($password . HASH_PEPPER, PASSWORD_BCRYPT);

$query = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $username, $hashedPassword);

if ($stmt->execute()) {
   echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to register user']);
}

$stmt->close();
$conn->close();