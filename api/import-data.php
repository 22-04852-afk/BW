<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include database configuration
require_once('../db_config.php');

// Get JSON data from request
$json = file_get_contents('php://input');
$request = json_decode($json, true);

if (!$request || !isset($request['data'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

// Validate required fields
$required_fields = ['Delivery_Month', 'Delivery_Day', 'Item_Code', 'Company_Name', 'Quantity', 'Status'];
$data = $request['data'];

if (empty($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'No data to import'
    ]);
    exit;
}

// Check first record has required fields
$first_record = $data[0];
foreach ($required_fields as $field) {
    if (!isset($first_record[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: $field"
        ]);
        exit;
    }
}

try {
    // Connect to database
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $imported_count = 0;
    $failed_count = 0;
    $errors = [];

    // Start transaction
    $conn->begin_transaction();

    foreach ($data as $index => $record) {
        try {
            // Validate data
            $delivery_month = isset($record['Delivery_Month']) ? trim($record['Delivery_Month']) : '';
            $delivery_day = isset($record['Delivery_Day']) ? trim($record['Delivery_Day']) : '';
            $item_code = isset($record['Item_Code']) ? trim($record['Item_Code']) : '';
            $item_name = isset($record['Item_Name']) ? trim($record['Item_Name']) : '';
            $company_name = isset($record['Company_Name']) ? trim($record['Company_Name']) : '';
            $quantity = isset($record['Quantity']) ? intval($record['Quantity']) : 0;
            $status = isset($record['Status']) ? trim($record['Status']) : '';
            $notes = isset($record['Notes']) ? trim($record['Notes']) : '';

            // Validate required fields
            if (empty($delivery_month) || empty($delivery_day) || empty($item_code) || empty($company_name)) {
                $errors[] = "Row " . ($index + 2) . ": Missing required fields";
                $failed_count++;
                continue;
            }

            // Validate numeric fields
            if (!is_numeric($delivery_day) || $delivery_day < 1 || $delivery_day > 31) {
                $errors[] = "Row " . ($index + 2) . ": Invalid delivery day";
                $failed_count++;
                continue;
            }

            if ($quantity < 0) {
                $errors[] = "Row " . ($index + 2) . ": Invalid quantity";
                $failed_count++;
                continue;
            }

            // Prepare SQL statement
            $sql = "INSERT INTO delivery_records 
                    (delivery_month, delivery_day, item_code, item_name, company_name, quantity, status, notes, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                    quantity = VALUES(quantity),
                    item_name = VALUES(item_name),
                    status = VALUES(status),
                    notes = VALUES(notes),
                    updated_at = NOW()";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                'sisssiss',
                $delivery_month,
                $delivery_day,
                $item_code,
                $item_name,
                $company_name,
                $quantity,
                $status,
                $notes
            );

            if (!$stmt->execute()) {
                $errors[] = "Row " . ($index + 2) . ": " . $stmt->error;
                $failed_count++;
            } else {
                $imported_count++;
            }

            $stmt->close();

        } catch (Exception $e) {
            $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            $failed_count++;
        }
    }

    // Commit transaction
    $conn->commit();
    $conn->close();

    // Prepare response
    $response = [
        'success' => true,
        'imported' => $imported_count,
        'failed' => $failed_count,
        'total' => count($data),
        'message' => "Successfully imported $imported_count records"
    ];

    if (!empty($errors) && $failed_count <= 5) {
        $response['errors'] = $errors;
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Import error: ' . $e->getMessage()
    ]);
}
?>
