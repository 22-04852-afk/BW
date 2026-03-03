<?php
/**
 * Database Setup Script
 * This script creates the database and tables if they don't exist
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'bw_gas_detector';

// Create connection without specifying database
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error
    ]));
}

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS `$db_name` 
                  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if (!$conn->query($sql_create_db)) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Error creating database: ' . $conn->error
    ]));
}

// Select the database
if (!$conn->select_db($db_name)) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Error selecting database: ' . $conn->error
    ]));
}

// Create delivery_records table
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
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Error creating table: ' . $conn->error
    ]));
}

$conn->close();

// Return success
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Database and tables setup successfully'
]);
?>
