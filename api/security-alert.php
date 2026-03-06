<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db_config.php';
require_once __DIR__ . '/email-config.php';

/**
 * Send security alert for failed login attempt
 */
function sendSecurityAlert($conn, $userId, $userEmail, $userName) {
    // Ensure security_alerts table exists
    if ($conn instanceof mysqli) {
        $conn->query("CREATE TABLE IF NOT EXISTS security_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            ip_address VARCHAR(45),
            user_agent TEXT,
            status ENUM('pending', 'confirmed', 'denied') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            responded_at TIMESTAMP NULL,
            INDEX idx_token (token),
            INDEX idx_user_id (user_id)
        )");
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS security_alerts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            ip_address VARCHAR(45),
            user_agent TEXT,
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            responded_at TIMESTAMP NULL
        )");
    }
    
    // Generate unique token
    $token = generateToken();
    $ipAddress = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $timestamp = date('Y-m-d H:i:s');
    
    // Save alert to database
    $stmt = $conn->prepare('INSERT INTO security_alerts (user_id, token, ip_address, user_agent) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $userId, $token, $ipAddress, $userAgent);
    $stmt->execute();
    $stmt->close();
    
    // Generate email content
    $baseUrl = getBaseUrl();
    $emailHtml = generateSecurityAlertEmail($userName, $ipAddress, $userAgent, $timestamp, $token, $baseUrl);
    
    // Send email
    $subject = '⚠️ Security Alert: Failed Login Attempt - BW Dashboard';
    $sent = sendEmail($userEmail, $subject, $emailHtml);
    
    return [
        'sent' => $sent,
        'token' => $token
    ];
}

/**
 * Check if user has email alerts enabled
 */
function isEmailAlertEnabled($conn, $userId) {
    // Check user_settings for email alert preference
    $stmt = $conn->prepare('SELECT settings FROM user_settings WHERE user_id = ?');
    if (!$stmt) return true; // Default to enabled if can't check
    
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($row = $result->fetch_assoc()) {
        $settings = json_decode($row['settings'], true);
        return $settings['emailAlerts'] ?? true;
    }
    
    return true; // Default to enabled
}

// Handle direct API calls
if (basename($_SERVER['SCRIPT_NAME']) === 'security-alert.php') {
    // Check if user is logged in for status checks
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get-alerts':
            if (empty($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }
            
            // Ensure security_alerts table exists
            if ($conn instanceof mysqli) {
                $conn->query("CREATE TABLE IF NOT EXISTS security_alerts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    status ENUM('pending', 'confirmed', 'denied') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    responded_at TIMESTAMP NULL,
                    INDEX idx_token (token),
                    INDEX idx_user_id (user_id)
                )");
            } else {
                $conn->query("CREATE TABLE IF NOT EXISTS security_alerts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    status VARCHAR(20) DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    responded_at TIMESTAMP NULL
                )");
            }
            
            // Get recent alerts for the logged-in user
            $stmt = $conn->prepare('SELECT id, ip_address, user_agent, status, created_at, responded_at FROM security_alerts WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $alerts = [];
            while ($row = $result->fetch_assoc()) {
                $alerts[] = $row;
            }
            $stmt->close();
            
            echo json_encode(['success' => true, 'alerts' => $alerts]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
