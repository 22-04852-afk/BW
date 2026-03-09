<?php
require_once '../db_config.php';

header('Content-Type: application/json');

// Log to file for debugging
$logFile = __DIR__ . '/../delete_api.log';
$timestamp = date('Y-m-d H:i:s');

function logToFile($message) {
    global $logFile, $timestamp;
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

logToFile('=== DELETE API CALLED ===');
logToFile('Request Method: ' . $_SERVER['REQUEST_METHOD']);
logToFile('User Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A'));

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logToFile('ERROR: Invalid request method');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    if (!$conn) {
        logToFile('ERROR: No database connection');
        throw new Exception('Database connection failed');
    }

    logToFile('Database connection OK');
    
    // STEP 1: Get count of delivery records before deletion
    logToFile('STEP 1: Getting count of delivery_records...');
    $countQuery = $conn->query("SELECT COUNT(*) as total FROM delivery_records");
    
    if ($countQuery === false) {
        logToFile('ERROR: Count query failed - ' . $conn->error);
        throw new Exception('Count query failed: ' . $conn->error);
    }
    
    $countResult = $countQuery->fetch_assoc();
    $deletedCount = isset($countResult['total']) ? intval($countResult['total']) : 0;
    logToFile('Found ' . $deletedCount . ' delivery records');
    
    // STEP 2: Delete delivery_records
    logToFile('STEP 2: Executing DELETE FROM delivery_records...');
    $deleteResult = $conn->query("DELETE FROM delivery_records");
    
    if ($deleteResult === false) {
        logToFile('ERROR: Delete from delivery_records failed - ' . $conn->error);
        throw new Exception('Delete from delivery_records failed: ' . $conn->error);
    }
    logToFile('Successfully deleted ' . $deletedCount . ' records from delivery_records');
    
    // STEP 3: Delete from inventory (if exists)
    logToFile('STEP 3: Executing DELETE FROM inventory...');
    @$conn->query("DELETE FROM inventory");
    logToFile('Inventory cleared');
    
    // STEP 4: Delete from security_alerts
    logToFile('STEP 4: Executing DELETE FROM security_alerts...');
    @$conn->query("DELETE FROM security_alerts");
    logToFile('Security alerts cleared');
    
    // STEP 5: Delete from login_attempts
    logToFile('STEP 5: Executing DELETE FROM login_attempts...');
    @$conn->query("DELETE FROM login_attempts");
    logToFile('Login attempts cleared');
    
    // STEP 6: Verify all data is gone
    logToFile('STEP 6: Verifying all data deleted...');
    $verify1 = $conn->query("SELECT COUNT(*) as total FROM delivery_records");
    $verify2 = @$conn->query("SELECT COUNT(*) as total FROM inventory");
    $verify3 = @$conn->query("SELECT COUNT(*) as total FROM security_alerts");
    
    if ($verify1) {
        $v1 = $verify1->fetch_assoc();
        logToFile('Verification - delivery_records: ' . $v1['total'] . ' records remaining');
    }
    if ($verify2) {
        $v2 = $verify2->fetch_assoc();
        logToFile('Verification - inventory: ' . ($v2['total'] ?? 0) . ' records remaining');
    }
    if ($verify3) {
        $v3 = $verify3->fetch_assoc();
        logToFile('Verification - security_alerts: ' . ($v3['total'] ?? 0) . ' records remaining');
    }
    
    logToFile('SUCCESS: Deleted ' . $deletedCount . ' delivery records and cleared all related data');
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'All data deleted successfully',
        'deleted_count' => $deletedCount
    ]);
    
} catch (Exception $e) {
    logToFile('EXCEPTION: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if ($conn && method_exists($conn, 'close')) {
    $conn->close();
}

logToFile('=== DELETE API END ===\n');
?>

