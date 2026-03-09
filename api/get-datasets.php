<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_config.php';

try {
    if (!$conn) throw new Exception('Database connection failed');

    // Ensure dataset_name column exists
    $isMysql = ($conn instanceof mysqli);
    $colExists = false;
    if ($isMysql) {
        $chk = $conn->query("SHOW COLUMNS FROM delivery_records LIKE 'dataset_name'");
        $colExists = ($chk && $chk->num_rows > 0);
    } else {
        $chk = $conn->query("PRAGMA table_info(delivery_records)");
        if ($chk) {
            while ($r = $chk->fetch_assoc()) {
                if (strtolower($r['name']) === 'dataset_name') { $colExists = true; break; }
            }
        }
    }
    if (!$colExists) {
        $conn->query("ALTER TABLE delivery_records ADD COLUMN dataset_name VARCHAR(50) DEFAULT NULL");
    }
    // Tag any pre-existing untagged rows as data1 (imported before this feature existed)
    $conn->query("UPDATE delivery_records SET dataset_name = 'data1' WHERE dataset_name IS NULL OR dataset_name = ''");

    // Get sorted distinct dataset names
    $result = $conn->query("SELECT DISTINCT dataset_name FROM delivery_records WHERE dataset_name IS NOT NULL AND dataset_name != '' ORDER BY dataset_name ASC");
    $datasets = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $datasets[] = $row['dataset_name'];
        }
    }

    // Determine next available dataset number
    $maxNum = 0;
    foreach ($datasets as $name) {
        if (preg_match('/^data(\d+)$/i', $name, $m)) {
            $maxNum = max($maxNum, intval($m[1]));
        }
    }
    $nextNum = $maxNum + 1;

    echo json_encode([
        'success'   => true,
        'datasets'  => $datasets,
        'next_num'  => $nextNum,
        'next_name' => 'data' . $nextNum
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'datasets' => [], 'next_num' => 1, 'next_name' => 'data1']);
}
?>
