<?php
/**
 * Email Configuration and Helper Functions
 * Uses SMTP to send real emails
 */

// ============================================
// EMAIL CONFIGURATION - UPDATE THESE SETTINGS
// ============================================
define('SMTP_HOST', 'smtp.gmail.com');           // Gmail SMTP server
define('SMTP_PORT', 587);                         // TLS port
define('SMTP_USER', '');                          // Your Gmail address (e.g., yourname@gmail.com)
define('SMTP_PASS', '');                          // Your Gmail App Password (NOT your regular password)
define('SMTP_FROM_EMAIL', '');                    // Same as SMTP_USER for Gmail
define('SMTP_FROM_NAME', 'BW Dashboard Security');

// ============================================
// HOW TO GET GMAIL APP PASSWORD:
// 1. Go to https://myaccount.google.com/security
// 2. Enable 2-Step Verification if not already enabled
// 3. Go to App passwords (https://myaccount.google.com/apppasswords)
// 4. Select "Mail" and "Windows Computer"
// 5. Click Generate - copy the 16-character password
// 6. Paste it in SMTP_PASS above
// ============================================

/**
 * Send email using SMTP
 */
function sendEmail($to, $subject, $htmlBody, $textBody = '') {
    // Check if SMTP is configured
    if (empty(SMTP_USER) || empty(SMTP_PASS)) {
        error_log('Email not sent: SMTP credentials not configured in api/email-config.php');
        return false;
    }
    
    return sendWithSMTP($to, $subject, $htmlBody);
}

/**
 * Send email using direct SMTP connection
 */
function sendWithSMTP($to, $subject, $htmlBody) {
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $username = SMTP_USER;
    $password = SMTP_PASS;
    $from = SMTP_FROM_EMAIL ?: SMTP_USER;
    $fromName = SMTP_FROM_NAME;
    
    // Create socket connection
    $socket = @fsockopen($host, $port, $errno, $errstr, 30);
    if (!$socket) {
        error_log("SMTP Connection failed: $errstr ($errno)");
        return false;
    }
    
    // Set timeout
    stream_set_timeout($socket, 30);
    
    // Read greeting
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '220') {
        fclose($socket);
        error_log("SMTP Error: $response");
        return false;
    }
    
    // Send EHLO
    fputs($socket, "EHLO localhost\r\n");
    $response = '';
    while ($line = fgets($socket, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // Start TLS
    fputs($socket, "STARTTLS\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '220') {
        fclose($socket);
        error_log("SMTP STARTTLS failed: $response");
        return false;
    }
    
    // Enable TLS encryption
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    
    // Send EHLO again after TLS
    fputs($socket, "EHLO localhost\r\n");
    $response = '';
    while ($line = fgets($socket, 515)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '334') {
        fclose($socket);
        error_log("SMTP AUTH failed: $response");
        return false;
    }
    
    // Send username (base64 encoded)
    fputs($socket, base64_encode($username) . "\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '334') {
        fclose($socket);
        error_log("SMTP username failed: $response");
        return false;
    }
    
    // Send password (base64 encoded)
    fputs($socket, base64_encode($password) . "\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '235') {
        fclose($socket);
        error_log("SMTP password failed: $response");
        return false;
    }
    
    // MAIL FROM
    fputs($socket, "MAIL FROM:<$from>\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        error_log("SMTP MAIL FROM failed: $response");
        return false;
    }
    
    // RCPT TO
    fputs($socket, "RCPT TO:<$to>\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        error_log("SMTP RCPT TO failed: $response");
        return false;
    }
    
    // DATA
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '354') {
        fclose($socket);
        error_log("SMTP DATA failed: $response");
        return false;
    }
    
    // Build message
    $boundary = md5(uniqid(time()));
    $headers = "From: $fromName <$from>\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    
    $message = $headers . "\r\n" . $htmlBody;
    
    // Send message (escape dots at start of lines)
    $message = str_replace("\r\n.", "\r\n..", $message);
    fputs($socket, $message . "\r\n.\r\n");
    
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '250') {
        fclose($socket);
        error_log("SMTP send failed: $response");
        return false;
    }
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return true;
}

/**
 * Generate security alert email HTML
 */
function generateSecurityAlertEmail($userName, $ipAddress, $userAgent, $timestamp, $verifyToken, $baseUrl) {
    $browser = getBrowserName($userAgent);
    $dateTime = date('F j, Y \a\t g:i A', strtotime($timestamp));
    
    $yesUrl = $baseUrl . '/verify-login.php?action=confirm&token=' . $verifyToken;
    $noUrl = $baseUrl . '/verify-login.php?action=deny&token=' . $verifyToken;
    $changePasswordUrl = $baseUrl . '/profile.php?change_password=1';
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4; padding: 20px 0;">
            <tr>
                <td align="center">
                    <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #1e2a38; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #e74c3c, #c0392b); padding: 30px; text-align: center;">
                                <div style="font-size: 48px; margin-bottom: 10px;">⚠️</div>
                                <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Security Alert</h1>
                                <p style="color: rgba(255,255,255,0.8); margin: 10px 0 0 0; font-size: 14px;">Failed login attempt detected</p>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style="padding: 30px;">
                                <p style="color: #e0e0e0; font-size: 16px; margin: 0 0 20px 0;">
                                    Hi <strong style="color: #f4d03f;">' . htmlspecialchars($userName) . '</strong>,
                                </p>
                                
                                <p style="color: #a0a0a0; font-size: 14px; margin: 0 0 25px 0;">
                                    Someone tried to access your BW Dashboard account with an incorrect password. If this was you, you can ignore this message. If not, we recommend changing your password immediately.
                                </p>
                                
                                <!-- Details Box -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: rgba(255,255,255,0.05); border-radius: 8px; margin-bottom: 25px;">
                                    <tr>
                                        <td style="padding: 20px;">
                                            <h3 style="color: #fff; margin: 0 0 15px 0; font-size: 14px;">Login Attempt Details:</h3>
                                            <table role="presentation" cellspacing="0" cellpadding="5">
                                                <tr>
                                                    <td style="color: #a0a0a0; font-size: 13px; padding-right: 15px;">📅 Date & Time:</td>
                                                    <td style="color: #e0e0e0; font-size: 13px;">' . $dateTime . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #a0a0a0; font-size: 13px; padding-right: 15px;">🌐 IP Address:</td>
                                                    <td style="color: #e0e0e0; font-size: 13px;">' . htmlspecialchars($ipAddress) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="color: #a0a0a0; font-size: 13px; padding-right: 15px;">💻 Browser:</td>
                                                    <td style="color: #e0e0e0; font-size: 13px;">' . htmlspecialchars($browser) . '</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="color: #e0e0e0; font-size: 14px; margin: 0 0 20px 0; text-align: center;">
                                    <strong>Was this you?</strong>
                                </p>
                                
                                <!-- Action Buttons -->
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td align="center" style="padding-bottom: 15px;">
                                            <a href="' . $yesUrl . '" style="display: inline-block; background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; text-decoration: none; padding: 14px 40px; border-radius: 8px; font-weight: bold; font-size: 14px;">✓ Yes, it was me</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" style="padding-bottom: 15px;">
                                            <a href="' . $noUrl . '" style="display: inline-block; background: linear-gradient(135deg, #e74c3c, #c0392b); color: #fff; text-decoration: none; padding: 14px 40px; border-radius: 8px; font-weight: bold; font-size: 14px;">✗ No, secure my account</a>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Password Change Suggestion -->
                                <div style="background-color: rgba(244, 208, 63, 0.1); border: 1px solid rgba(244, 208, 63, 0.3); border-radius: 8px; padding: 15px; margin-top: 20px;">
                                    <p style="color: #f4d03f; font-size: 13px; margin: 0;">
                                        💡 <strong>Tip:</strong> If you suspect unauthorized access, we recommend <a href="' . $changePasswordUrl . '" style="color: #5bbcff;">changing your password</a> immediately.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: rgba(0,0,0,0.2); padding: 20px; text-align: center;">
                                <p style="color: #a0a0a0; font-size: 12px; margin: 0;">
                                    This is an automated security alert from BW Dashboard.<br>
                                    © ' . date('Y') . ' Andison Industrial
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

/**
 * Get browser name from user agent
 */
function getBrowserName($userAgent) {
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Edg') !== false) return 'Microsoft Edge';
    if (strpos($userAgent, 'Chrome') !== false) return 'Google Chrome';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) return 'Opera';
    if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'Internet Explorer';
    return 'Unknown Browser';
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle comma-separated IPs (X-Forwarded-For)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            return $ip;
        }
    }
    
    return 'Unknown';
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Get base URL of the application
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $path = rtrim($path, '/api');
    return $protocol . '://' . $host . $path;
}
