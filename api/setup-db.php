<?php
/**
 * Database Setup Script
 * This script creates the database and tables if they don't exist
 */

header('Content-Type: application/json');

// Use the main database config which handles MySQL/SQLite fallback
require_once __DIR__ . '/../db_config.php';

// Check if connection is available (already established in db_config.php)
if (!$conn || $conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]));
}

// For MySQL connections, ensure the table exists
if ($conn instanceof mysqli) {
    // Create delivery_records table if it doesn't exist
    $sql_create_table = "CREATE TABLE IF NOT EXISTS `delivery_records` (
      `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `delivery_month` VARCHAR(20) NOT NULL,
      `delivery_day` INT(2) NOT NULL,
      `item_code` VARCHAR(50) NOT NULL,
      `item_name` VARCHAR(255),
      `company_name` VARCHAR(255) NOT NULL,
      `quantity` INT(11) NOT NULL DEFAULT 0,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Pending',
      `notes` TEXT,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      
      KEY `idx_delivery_month` (`delivery_month`),
      KEY `idx_delivery_day` (`delivery_day`),
      KEY `idx_item_code` (`item_code`),
      KEY `idx_company_name` (`company_name`),
      KEY `idx_status` (`status`),
      KEY `idx_created_at` (`created_at`),
      
      UNIQUE KEY `unique_delivery` (`delivery_month`, `delivery_day`, `item_code`, `company_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$conn->query($sql_create_table)) {
        die(json_encode([
            'success' => false,
            'message' => 'Error creating table: ' . $conn->error
        ]));
    }
}
// SQLite tables are already created in db_config.php

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Database and tables setup successfully'
]);
?>
