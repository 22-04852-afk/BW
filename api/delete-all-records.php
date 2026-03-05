<?php
require_once '../db_config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get count before deletion
    $countQuery = $conn->query("SELECT COUNT(*) as total FROM delivery_records");
    $countResult = $countQuery->fetch_assoc();
    $deletedCount = $countResult['total'];
    
    // Delete all records
    $stmt = $conn->prepare("DELETE FROM delivery_records");
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'All records deleted successfully',
        'deleted_count' => $deletedCount
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting records: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
