<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include database configuration (handles MySQL/SQLite fallback)
require_once __DIR__ . '/../db_config.php';

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

$data = $request['data'];

if (empty($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'No data to import'
    ]);
    exit;
}

// Function to convert Excel serial date to actual date
function excelDateToDate($excelDate) {
    if (empty($excelDate) || $excelDate == '-') return null;
    
    // If it's already a date string, return as is
    if (!is_numeric($excelDate)) {
        return $excelDate;
    }
    
    // Excel serial date conversion (Excel epoch is 1899-12-30)
    $unix = ($excelDate - 25569) * 86400;
    return date('Y-m-d', $unix);
}

// Function to get month name from date
function getMonthFromDate($dateStr) {
    if (empty($dateStr)) return '';
    $timestamp = strtotime($dateStr);
    if ($timestamp === false) return '';
    return date('F', $timestamp); // Returns full month name
}

// Function to get day from date
function getDayFromDate($dateStr) {
    if (empty($dateStr)) return 0;
    $timestamp = strtotime($dateStr);
    if ($timestamp === false) return 0;
    return intval(date('j', $timestamp)); // Returns day without leading zeros
}

// Column mapping - maps various Excel column names to database fields
$column_mappings = [
    // Invoice number variations
    'Invoice No.' => 'invoice_no',
    'Invoice No' => 'invoice_no',
    'InvoiceNo' => 'invoice_no',
    'Invoice_No' => 'invoice_no',
    'invoice_no' => 'invoice_no',
    
    // Item code variations
    'Item' => 'item_code',
    'Item_Code' => 'item_code',
    'ItemCode' => 'item_code',
    'item_code' => 'item_code',
    'Product' => 'item_code',
    'Product Code' => 'item_code',
    
    // Description/Item name variations
    'Description' => 'item_name',
    'Item_Name' => 'item_name',
    'ItemName' => 'item_name',
    'item_name' => 'item_name',
    'Product Name' => 'item_name',
    
    // Quantity variations
    'Qty.' => 'quantity',
    'Qty' => 'quantity',
    'Quantity' => 'quantity',
    'quantity' => 'quantity',
    'QTY' => 'quantity',
    
    // Serial number variations
    'Serial No.' => 'serial_no',
    'Serial No' => 'serial_no',
    'SerialNo' => 'serial_no',
    'Serial_No' => 'serial_no',
    'serial_no' => 'serial_no',
    
    // Date variations
    'Date' => 'date',
    'date' => 'date',
    'Order Date' => 'date',
    
    // Date delivered variations
    'Date Delivered' => 'date_delivered',
    'DateDelivered' => 'date_delivered',
    'Date_Delivered' => 'date_delivered',
    'Delivery Date' => 'date_delivered',
    'Delivered Date' => 'date_delivered',
    
    // Remarks/Notes variations
    'Remarks' => 'notes',
    'remarks' => 'notes',
    'Notes' => 'notes',
    'notes' => 'notes',
    'Note' => 'notes',
    
    // Company name variations
    'Company_Name' => 'company_name',
    'Company' => 'company_name',
    'Client' => 'company_name',
    'Customer' => 'company_name',
    
    // Status variations
    'Status' => 'status',
    'status' => 'status',
    
    // Legacy format support
    'Delivery_Month' => 'delivery_month',
    'Delivery_Day' => 'delivery_day',
];

try {
    // Use the connection from db_config.php
    if (!$conn || $conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    $imported_count = 0;
    $failed_count = 0;
    $errors = [];

    // Check if we're using SQLite (SqliteConn class) or MySQL
    $is_sqlite = !($conn instanceof mysqli);

    // Start transaction
    if ($conn instanceof mysqli) {
        $conn->begin_transaction();
    } else {
        $conn->query('BEGIN TRANSACTION');
    }

    foreach ($data as $index => $record) {
        try {
            // Map columns to database fields
            $mapped = [];
            foreach ($record as $col => $value) {
                $col_trimmed = trim($col);
                if (isset($column_mappings[$col_trimmed])) {
                    $db_field = $column_mappings[$col_trimmed];
                    $mapped[$db_field] = $value;
                }
            }
            
            // Extract and process values
            $invoice_no = isset($mapped['invoice_no']) ? trim(strval($mapped['invoice_no'])) : '';
            $item_code = isset($mapped['item_code']) ? trim(strval($mapped['item_code'])) : '';
            $item_name = isset($mapped['item_name']) ? trim(strval($mapped['item_name'])) : '';
            $quantity = isset($mapped['quantity']) ? intval($mapped['quantity']) : 0;
            $serial_no = isset($mapped['serial_no']) ? trim(strval($mapped['serial_no'])) : '';
            $notes = isset($mapped['notes']) ? trim(strval($mapped['notes'])) : '';
            $company_name = isset($mapped['company_name']) ? trim(strval($mapped['company_name'])) : 'Andison Industrial';
            $status = isset($mapped['status']) ? trim(strval($mapped['status'])) : 'Delivered';
            
            // Handle dates
            $delivery_date = null;
            $delivery_month = '';
            $delivery_day = 0;
            
            // Try date_delivered first, then date
            if (!empty($mapped['date_delivered'])) {
                $delivery_date = excelDateToDate($mapped['date_delivered']);
            } elseif (!empty($mapped['date'])) {
                $delivery_date = excelDateToDate($mapped['date']);
            }
            
            // Extract month and day from delivery_date
            if ($delivery_date) {
                $delivery_month = getMonthFromDate($delivery_date);
                $delivery_day = getDayFromDate($delivery_date);
            }
            
            // Support legacy format
            if (empty($delivery_month) && !empty($mapped['delivery_month'])) {
                $delivery_month = $mapped['delivery_month'];
            }
            if ($delivery_day == 0 && !empty($mapped['delivery_day'])) {
                $delivery_day = intval($mapped['delivery_day']);
            }
            
            // Skip empty rows (check if essential fields are empty)
            if (empty($item_code) && empty($item_name) && $quantity == 0) {
                continue; // Skip this row silently
            }
            
            // Skip rows with placeholder or header data
            if ($item_code == '-' || $item_code == 'Item' || $notes == 'DESCRIPTION') {
                continue;
            }
            
            // Handle "-" as empty
            if ($notes == '-') $notes = '';
            if ($serial_no == '-') $serial_no = '';
            if ($company_name == '-') $company_name = 'Andison Industrial';
            
            // Default status if empty
            if (empty($status) || $status == '-') {
                $status = 'Delivered';
            }
            
            // Default delivery month if empty
            if (empty($delivery_month)) {
                $delivery_month = date('F'); // Current month
            }
            
            // Default delivery day if empty
            if ($delivery_day == 0) {
                $delivery_day = intval(date('j'));
            }

            // Insert into database
            $sql = "INSERT INTO delivery_records 
                    (invoice_no, serial_no, delivery_month, delivery_day, delivery_date, item_code, item_name, company_name, quantity, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param(
                'sssississss',
                $invoice_no,
                $serial_no,
                $delivery_month,
                $delivery_day,
                $delivery_date,
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
    if ($conn instanceof mysqli) {
        $conn->commit();
    } else {
        $conn->query('COMMIT');
    }

    // Prepare response
    $response = [
        'success' => true,
        'imported' => $imported_count,
        'failed' => $failed_count,
        'total' => count($data),
        'message' => "Successfully imported $imported_count records"
    ];

    if (!empty($errors) && $failed_count <= 10) {
        $response['errors'] = $errors;
    }

    echo json_encode($response);

} catch (Exception $e) {
    // Rollback on error
    if (isset($conn)) {
        if ($conn instanceof mysqli) {
            $conn->rollback();
        } else {
            $conn->query('ROLLBACK');
        }
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Import error: ' . $e->getMessage()
    ]);
}
?>
