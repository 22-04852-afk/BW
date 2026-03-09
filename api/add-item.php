<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Include database config
require_once '../db_config.php';

// Get POST data
$item_code = isset($_POST['item_code']) ? trim($_POST['item_code']) : '';
$item_name = isset($_POST['item_name']) ? trim($_POST['item_name']) : '';
$initial_qty = isset($_POST['initial_qty']) ? intval($_POST['initial_qty']) : 0;
$company_name = $_SESSION['company_name'] ?? '';
$dataset = $_SESSION['dataset'] ?? 'default';

// Validate inputs
if (empty($item_code)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Item code is required']);
    exit();
}

if (empty($item_name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Item name/description is required']);
    exit();
}

if ($initial_qty < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Initial quantity cannot be negative']);
    exit();
}

try {
    // Check if item_code already exists in this dataset
    $check_sql = "SELECT item_code FROM delivery_records WHERE item_code = ? AND dataset = ? LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $item_code, $dataset);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Item code "' . htmlspecialchars($item_code) . '" already exists']);
        exit();
    }
    
    $check_stmt->close();

    // Insert new item record
    $today = date('Y-m-d');
    $insert_sql = "INSERT INTO delivery_records (item_code, item_name, company_name, qty_added, qty_delivered, quantity, date_added, dataset) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $insert_stmt = $conn->prepare($insert_sql);
    if (!$insert_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // If initial_qty is provided, set it as qty_added
    $qty_added = $initial_qty;
    $qty_delivered = 0;
    $quantity = $initial_qty;
    
    $insert_stmt->bind_param("sssiiii" . "s", $item_code, $item_name, $company_name, $qty_added, $qty_delivered, $quantity, $today, $dataset);
    
    if (!$insert_stmt->execute()) {
        throw new Exception("Execute failed: " . $insert_stmt->error);
    }
    
    $insert_stmt->close();

    // Log the action
    error_log("[" . date('Y-m-d H:i:s') . "] New item created: {$item_code} - {$item_name} (Initial Stock: {$initial_qty}) by user {$_SESSION['user_id']}");

    echo json_encode([
        'success' => true,
        'message' => 'Item created successfully',
        'item_code' => $item_code,
        'item_name' => $item_name,
        'initial_qty' => $initial_qty
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in add-item.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error creating item: ' . $e->getMessage()]);
}

$conn->close();
?>
