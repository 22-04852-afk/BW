<?php
require_once 'db_config.php';
$result = $conn->query('SELECT * FROM delivery_records LIMIT 5');
if ($result) {
    echo "Records found: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Query failed\n";
}
?>
