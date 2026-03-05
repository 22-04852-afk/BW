<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_config.php';

// Get total count
$result = $conn->query("SELECT COUNT(*) as total FROM delivery_records");
$total = $result->fetch_assoc()['total'];

// Get sample records
$samples = [];
$result = $conn->query("SELECT * FROM delivery_records ORDER BY id DESC LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $samples[] = $row;
}

// Get quantity distribution
$qty_dist = [];
$result = $conn->query("SELECT quantity, COUNT(*) as cnt FROM delivery_records GROUP BY quantity ORDER BY cnt DESC LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $qty_dist[] = $row;
}

// Get year distribution
$year_dist = [];
$result = $conn->query("SELECT delivery_year, COUNT(*) as cnt FROM delivery_records GROUP BY delivery_year");
while ($row = $result->fetch_assoc()) {
    $year_dist[] = $row;
}

echo json_encode([
    'total_records' => $total,
    'year_distribution' => $year_dist,
    'quantity_distribution' => $qty_dist,
    'sample_records' => $samples
], JSON_PRETTY_PRINT);

$conn->close();
