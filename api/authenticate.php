<?php
ob_start();

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

// ---- Brute-force rate limiting (max 10 attempts per 15 min per session) ----
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['login_first_attempt'] = $_SESSION['login_first_attempt'] ?? time();

$windowSeconds = 15 * 60; // 15 minutes
$maxAttempts   = 10;

if (time() - $_SESSION['login_first_attempt'] > $windowSeconds) {
    // Reset window
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_first_attempt'] = time();
}

if ($_SESSION['login_attempts'] >= $maxAttempts) {
    $waitSec  = $windowSeconds - (time() - $_SESSION['login_first_attempt']);
    $waitMins = max(1, (int) ceil($waitSec / 60));
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => "Too many login attempts. Please wait {$waitMins} minute(s) and try again."
    ]);
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';   // Do NOT trim password — leading/trailing space may be intentional

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
    $_SESSION['login_attempts']++;
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
    $_SESSION['login_attempts']++;
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// Successful login: regenerate session to prevent session fixation
session_regenerate_id(true);

// Reset rate-limit counters on success
unset($_SESSION['login_attempts'], $_SESSION['login_first_attempt']);

// Successful login: set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name'] = $user['name'] ?? $user['email'];

ob_clean();
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => 'index.php'
]);

exit;

?>
