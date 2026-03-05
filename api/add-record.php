<?php
header('Content-Type: application/json');

// Include database configuration
require_once __DIR__ . '/../db_config.php';

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

// Validate required fields
if (empty($data['item_code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Item code is required'
    ]);
    exit;
}

if (empty($data['quantity']) || $data['quantity'] < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Quantity must be at least 1'
    ]);
    exit;
}

try {
    // Extract and sanitize data
    $serial_no = trim($data['serial_no'] ?? '');
    $invoice_no = trim($data['invoice_no'] ?? '');
    $item_code = trim($data['item_code']);
    $item_name = trim($data['item_name'] ?? '');
    $company_name = trim($data['company_name'] ?? 'Andison Industrial');
    $quantity = intval($data['quantity']);
    $delivery_date = !empty($data['delivery_date']) ? $data['delivery_date'] : null;
    $status = trim($data['status'] ?? 'Delivered');
    $notes = trim($data['notes'] ?? '');
    
    // Extract delivery month and day from date
    $delivery_month = '';
    $delivery_day = 0;
    $delivery_year = null;
    if ($delivery_date) {
        $timestamp = strtotime($delivery_date);
        $delivery_month = date('F', $timestamp); // Full month name
        $delivery_day = intval(date('j', $timestamp)); // Day without leading zeros
        $delivery_year = intval(date('Y', $timestamp)); // Year
    }
    
    // Insert into database
    $sql = "INSERT INTO delivery_records 
            (invoice_no, serial_no, delivery_month, delivery_day, delivery_year, delivery_date, item_code, item_name, company_name, quantity, status, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        'sssiissssisss',
        $invoice_no,
        $serial_no,
        $delivery_month,
        $delivery_day,
        $delivery_year,
        $delivery_date,
        $item_code,
        $item_name,
        $company_name,
        $quantity,
        $status,
        $notes
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $new_id = $conn->insert_id ?? $stmt->insert_id ?? 0;
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Record added successfully',
        'id' => $new_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
