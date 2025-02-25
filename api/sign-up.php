<?php
header("Content-Type: application/json");
require_once '../config.php' ;

// Connect to MySQL
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Could not connect to the database']);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Username and password are required']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
    echo json_encode(['status' => 'error', 'message' => 'Username must be 4-20 characters long and contain only letters, numbers, and underscores']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long']);
    exit;
}

// Check if the username already exists
$query = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password . HASH_PEPPER, PASSWORD_BCRYPT);

// Insert the new user into the database
try {
    $query = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $username, $hashedPassword);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'There were problems adding to the database. Try again later!']);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to register user']);
}

$stmt->close();
$conn->close();