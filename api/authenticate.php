<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

// Load DB config
require_once __DIR__ . '/../db_config.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Prepare statement to avoid SQL injection
$stmt = $conn->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    error_log('DB prepare failed: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

$user = $result->fetch_assoc();

$stored = $user['password'];
$verified = false;

// Support both hashed and plain-text passwords (legacy)
if (password_verify($password, $stored)) {
    $verified = true;
} elseif ($password === $stored) {
    $verified = true;
}

if (!$verified) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// Successful login: set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['name'] ?? $user['email'];

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => 'index.php'
]);

exit;

?>
