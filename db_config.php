<?php
// ── Database Configuration ────────────────────────────────────────────────────
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'bw_gas_detector';

// Try MySQL first; fall back to SQLite when MySQL is unavailable
$conn = null;

// Enable MySQLi exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Try MySQL connection with proper exception handling
try {
    $mysql = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $mysql->set_charset('utf8mb4');
    $conn = $mysql;
} catch (Exception $e) {
    // Fall back to SQLite (no MySQL required)
    require_once __DIR__ . '/db_sqlite_compat.php';
    $sqlite_file = __DIR__ . '/bw_gas_detector.sqlite';
    $conn = new SqliteConn($sqlite_file);

    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode([
            'success'  => false,
            'message'  => 'Database connection failed: ' . $conn->connect_error
        ]));
    }

    // Bootstrap core tables the first time
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        name     VARCHAR(255) NOT NULL,
        email    VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS delivery_records (
        id             INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_no     VARCHAR(50),
        serial_no      VARCHAR(100),
        delivery_month VARCHAR(20),
        delivery_day   INTEGER,
        delivery_date  DATE,
        item_code      VARCHAR(50)  NOT NULL,
        item_name      VARCHAR(255),
        company_name   VARCHAR(255),
        quantity       INTEGER      NOT NULL DEFAULT 0,
        status         VARCHAR(50)  NOT NULL DEFAULT 'Delivered',
        notes          TEXT,
        created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
        updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    )");
}
?>
