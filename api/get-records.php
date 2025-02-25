<?php
error_reporting(E_ALL ^ E_NOTICE);

header("Content-Type: application/json");
require_once '../config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$unique = isset($_GET['unique']) ? filter_var($_GET['unique'], FILTER_VALIDATE_BOOLEAN) : true;
$sort = isset($_GET['sort']) && in_array(strtoupper($_GET['sort']), ['ASC', 'DESC']) ? strtoupper($_GET['sort']) : 'DESC';
$username = isset($_GET['username']) ? trim($_GET['username']) : null;

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Could not connect to the database']);
    exit;
}

if ($unique) {
    $query = "SELECT users.username, MAX(user_records.score) as score 
              FROM user_records 
              JOIN users ON user_records.user_id = users.id
              GROUP BY users.id";
} else {
    $query = "SELECT users.username, user_records.score 
              FROM user_records 
              JOIN users ON user_records.user_id = users.id";
}

if ($username !== null) {
    $query .= $unique ? " WHERE users.username = ?" : " WHERE users.username = ?";
}

$query .= " ORDER BY score $sort";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL query']);
    exit;
}

if ($username !== null) {
    $stmt->bind_param('s', $username);
}

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to execute the SQL query']);
    exit;
}

$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'status' => 'success',
    'message' => 'Records fetched successfully',
    'records' => $records
]);

$stmt->close();
$conn->close();