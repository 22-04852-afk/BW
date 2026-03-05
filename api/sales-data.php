<?php
session_start();
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
require_once '../db_config.php';

$allMonths = ['January','February','March','April','May','June','July','August','September','October','November','December'];
$isMysql   = ($conn instanceof mysqli);
$yearExpr  = $isMysql
    ? "CASE WHEN delivery_year > 0 THEN delivery_year WHEN delivery_date IS NOT NULL THEN YEAR(delivery_date) ELSE YEAR(created_at) END"
    : "CASE WHEN delivery_year > 0 THEN delivery_year WHEN delivery_date IS NOT NULL THEN CAST(strftime('%Y', delivery_date) AS INTEGER) ELSE CAST(strftime('%Y', created_at) AS INTEGER) END";

$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Monthly data for selected year
$monthlySales = array_fill_keys($allMonths, ['units' => 0, 'orders' => 0]);
$result = $conn->query("
    SELECT delivery_month,
           COUNT(*) as order_count,
           COALESCE(SUM(CASE WHEN company_name IS NOT NULL AND company_name != '' THEN quantity ELSE 0 END), 0) as total_units
    FROM delivery_records
    WHERE ({$yearExpr}) = {$selectedYear}
    GROUP BY delivery_month
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $m = $row['delivery_month'];
        if (array_key_exists($m, $monthlySales)) {
            $monthlySales[$m] = [
                'units'  => intval($row['total_units']),
                'orders' => intval($row['order_count'])
            ];
        }
    }
}

$yearlyTotal = ['units' => 0, 'orders' => 0];
foreach ($monthlySales as $d) {
    $yearlyTotal['units']  += $d['units'];
    $yearlyTotal['orders'] += $d['orders'];
}

echo json_encode([
    'year'          => $selectedYear,
    'yearlyUnits'   => $yearlyTotal['units'],
    'yearlyOrders'  => $yearlyTotal['orders'],
    'monthUnits'    => array_values(array_map(fn($m) => $monthlySales[$m]['units'],  $allMonths)),
    'monthOrders'   => array_values(array_map(fn($m) => $monthlySales[$m]['orders'], $allMonths)),
    'monthData'     => array_map(fn($m) => [
        'month'  => $m,
        'units'  => $monthlySales[$m]['units'],
        'orders' => $monthlySales[$m]['orders'],
    ], $allMonths),
]);
