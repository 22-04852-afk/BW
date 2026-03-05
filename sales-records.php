<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

// Include database configuration
require_once 'db_config.php';

// Get current year
$currentYear = date('Y');

// All months
$allMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

// Detect DB type for compatible year expressions
$isMysql = ($conn instanceof mysqli);

// Year expression: prefer stored delivery_year; fall back to extracting from delivery_date/created_at
$yearExpr = $isMysql
    ? "CASE WHEN delivery_year > 0 THEN delivery_year WHEN delivery_date IS NOT NULL THEN YEAR(delivery_date) ELSE YEAR(created_at) END"
    : "CASE WHEN delivery_year > 0 THEN delivery_year WHEN delivery_date IS NOT NULL THEN CAST(strftime('%Y', delivery_date) AS INTEGER) ELSE CAST(strftime('%Y', created_at) AS INTEGER) END";

// Get available years from data
$availableYears = [];
$result = $conn->query("SELECT DISTINCT ({$yearExpr}) as year FROM delivery_records WHERE ({$yearExpr}) > 0 ORDER BY year DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (intval($row['year']) > 0) {
            $availableYears[] = intval($row['year']);
        }
    }
}

// Default to the most recent year that has data; fall back to current year if no data
$defaultYear = !empty($availableYears) ? $availableYears[0] : $currentYear;
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $defaultYear;

// Always include current year in the dropdown (even if no data yet)
if (!in_array($currentYear, $availableYears)) {
    $availableYears[] = $currentYear;
    sort($availableYears);
    $availableYears = array_reverse($availableYears);
}

// Monthly Sales Data for Selected Year
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
        $month = $row['delivery_month'];
        if (array_key_exists($month, $monthlySales)) {
            $monthlySales[$month] = [
                'units'  => intval($row['total_units']),
                'orders' => intval($row['order_count'])
            ];
        }
    }
}

// Yearly Sales Data (All Years)
$yearlySales = [];
$result = $conn->query("
    SELECT ({$yearExpr}) as year,
           COUNT(*) as order_count,
           COALESCE(SUM(CASE WHEN company_name IS NOT NULL AND company_name != '' THEN quantity ELSE 0 END), 0) as total_units
    FROM delivery_records
    GROUP BY ({$yearExpr})
    ORDER BY year DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $yearlySales[] = [
            'year'   => intval($row['year']),
            'units'  => intval($row['total_units']),
            'orders' => intval($row['order_count'])
        ];
    }
}

// Calculate totals for selected year
$yearlyTotal = ['units' => 0, 'orders' => 0];
foreach ($monthlySales as $data) {
    $yearlyTotal['units'] += $data['units'];
    $yearlyTotal['orders'] += $data['orders'];
}

// Overall totals (all time)
$allTimeTotal = ['units' => 0, 'orders' => 0];
foreach ($yearlySales as $data) {
    $allTimeTotal['units'] += $data['units'];
    $allTimeTotal['orders'] += $data['orders'];
}

// Prepare data for JavaScript
$monthLabels = json_encode($allMonths);
$monthUnits = json_encode(array_values(array_map(function($m) use ($monthlySales) { return $monthlySales[$m]['units']; }, $allMonths)));
$monthOrders = json_encode(array_values(array_map(function($m) use ($monthlySales) { return $monthlySales[$m]['orders']; }, $allMonths)));

$yearLabels = json_encode(array_column($yearlySales, 'year'));
$yearUnits = json_encode(array_column($yearlySales, 'units'));
$yearOrders = json_encode(array_column($yearlySales, 'orders'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')==='light'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Records - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title i {
            color: #f4d03f;
        }

        .year-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .year-selector label {
            color: #a0a0a0;
            font-size: 14px;
            font-weight: 500;
        }

        .year-selector select {
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            min-width: 120px;
        }

        .year-selector select:focus {
            outline: none;
            border-color: #f4d03f;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            border-color: #f4d03f;
        }

        .summary-card.highlight {
            background: linear-gradient(135deg, #2f5fa7 0%, #00d9ff 100%);
        }

        .summary-card .icon {
            font-size: 36px;
            margin-bottom: 12px;
            color: #f4d03f;
        }

        .summary-card.highlight .icon {
            color: #fff;
        }

        .summary-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .summary-card .label {
            font-size: 13px;
            color: #a0a0a0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card.highlight .label {
            color: rgba(255, 255, 255, 0.8);
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f4d03f;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #f4d03f;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
        }

        .chart-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-card h3 i {
            color: #f4d03f;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .sales-table thead th {
            background: rgba(47, 95, 167, 0.3);
            padding: 14px 18px;
            text-align: left;
            font-weight: 600;
            color: #fff;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f4d03f;
        }

        .sales-table tbody td {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
            font-size: 14px;
        }

        .sales-table tbody tr:hover {
            background: rgba(47, 95, 167, 0.15);
        }

        .sales-table tbody tr.total-row {
            background: rgba(244, 208, 63, 0.1);
            font-weight: 700;
        }

        .sales-table tbody tr.total-row td {
            color: #f4d03f;
            border-top: 2px solid #f4d03f;
        }

        .table-container {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            overflow-x: auto;
            margin-bottom: 30px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .table-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-header h3 i {
            color: #f4d03f;
        }

        .badge-month {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-current {
            background: rgba(244, 208, 63, 0.2);
            color: #f4d03f;
        }

        .badge-high {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }

        /* Light Mode Styles */
        [data-theme="light"] .page-title,
        [data-theme="light"] .section-title,
        [data-theme="light"] .chart-card h3,
        [data-theme="light"] .table-header h3 {
            color: #1a3a5c;
        }

        [data-theme="light"] .summary-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid #c5ddf0;
        }

        [data-theme="light"] .summary-card .value {
            color: #1a3a5c;
        }

        [data-theme="light"] .summary-card .label {
            color: #5a6a7a;
        }

        [data-theme="light"] .chart-card,
        [data-theme="light"] .table-container {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid #c5ddf0;
        }

        [data-theme="light"] .sales-table thead th {
            background: rgba(30, 136, 229, 0.1);
            color: #1a3a5c;
            border-bottom: 2px solid #1e88e5;
        }

        [data-theme="light"] .sales-table tbody td {
            color: #333;
            border-bottom: 1px solid #e0e0e0;
        }

        [data-theme="light"] .sales-table tbody tr:hover {
            background: rgba(30, 136, 229, 0.05);
        }

        [data-theme="light"] .sales-table tbody tr.total-row {
            background: rgba(30, 136, 229, 0.1);
        }

        [data-theme="light"] .sales-table tbody tr.total-row td {
            color: #1e88e5;
            border-top: 2px solid #1e88e5;
        }

        [data-theme="light"] .year-selector select {
            background: #fff;
            border: 1px solid #c5ddf0;
            color: #1a3a5c;
        }

        [data-theme="light"] .year-selector label {
            color: #5a6a7a;
        }

        [data-theme="light"] .section-title {
            border-bottom: 2px solid #1e88e5;
        }

        [data-theme="light"] .section-title i,
        [data-theme="light"] .chart-card h3 i,
        [data-theme="light"] .table-header h3 i {
            color: #1e88e5;
        }

        [data-theme="light"] .summary-card .icon {
            color: #1e88e5;
        }

        [data-theme="light"] .badge-current {
            background: rgba(30, 136, 229, 0.15);
            color: #1e88e5;
        }
    </style>
</head>
<body>
    <!-- TOP NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Hamburger Toggle & Logo -->
            <div class="navbar-start">
                <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle sidebar">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="logo">
                    <img src="assets/logo.png" alt="Andison" style="height:38px;width:auto;object-fit:contain;">
                </div>
            </div>

            <!-- Right Profile Section -->
            <div class="navbar-end">
                <div class="notification" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="profile-dropdown">
                    <button type="button" class="profile-btn" id="profileBtn" aria-label="Profile menu">
                        <span class="profile-name"><?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="profileMenu">
                        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <!-- Dashboard -->
                    <li class="menu-item">
                        <a href="index.php" class="menu-link">
                            <i class="fas fa-chart-line"></i>
                            <span class="menu-label">Dashboard</span>
                        </a>
                    </li>

                    <!-- Sales Overview -->
                    <li class="menu-item">
                        <a href="sales-overview.php" class="menu-link">
                            <i class="fas fa-chart-pie"></i>
                            <span class="menu-label">Sales Overview</span>
                        </a>
                    </li>

                    <!-- Sales Records - Active -->
                    <li class="menu-item active">
                        <a href="sales-records.php" class="menu-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="menu-label">Sales Records</span>
                        </a>
                    </li>

                    <!-- Delivery Records -->
                    <li class="menu-item">
                        <a href="delivery-records.php" class="menu-link">
                            <i class="fas fa-truck"></i>
                            <span class="menu-label">Delivery Records</span>
                        </a>
                    </li>

                    <!-- Client Companies -->
                    <li class="menu-item">
                        <a href="client-companies.php" class="menu-link">
                            <i class="fas fa-building"></i>
                            <span class="menu-label">Client Companies</span>
                        </a>
                    </li>

                    <!-- Models (Dropdown) -->
                    <li class="menu-item has-submenu">
                        <a href="models.php" class="menu-link submenu-toggle" data-submenu="models-submenu">
                            <i class="fas fa-cube"></i>
                            <span class="menu-label">Models</span>
                            <i class="fas fa-chevron-right submenu-icon"></i>
                        </a>
                        <ul class="submenu" id="models-submenu">
                            <li>
                                <a href="models.php#group-a" class="submenu-link">
                                    <span>Group A</span>
                                </a>
                            </li>
                            <li>
                                <a href="models.php#group-b" class="submenu-link">
                                    <span>Group B</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Analytics -->
                    <li class="menu-item">
                        <a href="analytics.php" class="menu-link">
                            <i class="fas fa-chart-bar"></i>
                            <span class="menu-label">Analytics</span>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li class="menu-item">
                        <a href="reports.php" class="menu-link">
                            <i class="fas fa-file-alt"></i>
                            <span class="menu-label">Reports</span>
                        </a>
                    </li>

                    <!-- Upload Data -->
                    <li class="menu-item">
                        <a href="upload-data.php" class="menu-link">
                            <i class="fas fa-upload"></i>
                            <span class="menu-label">Upload Data</span>
                        </a>
                    </li>

                    <!-- Settings -->
                    <li class="menu-item">
                        <a href="settings.php" class="menu-link">
                            <i class="fas fa-cog"></i>
                            <span class="menu-label">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Sidebar Footer -->
            <div class="sidebar-footer">
                <p class="company-info">Andison Industrial</p>
                <p class="company-year">© 2025</p>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-calendar-alt"></i>
                    Sales Records
                </h1>
                <div class="year-selector">
                    <label for="yearSelect">Select Year:</label>
                    <select id="yearSelect" onchange="changeYear(this.value)">
                        <?php foreach ($availableYears as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo $year == $selectedYear ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="value"><?php echo number_format($yearlyTotal['units']); ?></div>
                    <div class="label">Units in <?php echo $selectedYear; ?></div>
                </div>
                <div class="summary-card">
                    <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="value"><?php echo number_format($yearlyTotal['orders']); ?></div>
                    <div class="label">Orders in <?php echo $selectedYear; ?></div>
                </div>
                <div class="summary-card highlight">
                    <div class="icon"><i class="fas fa-boxes"></i></div>
                    <div class="value"><?php echo number_format($allTimeTotal['units']); ?></div>
                    <div class="label">All-Time Units</div>
                </div>
                <div class="summary-card highlight">
                    <div class="icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="value"><?php echo number_format($allTimeTotal['orders']); ?></div>
                    <div class="label">All-Time Orders</div>
                </div>
            </div>

            <!-- Monthly Sales Section -->
            <h2 class="section-title">
                <i class="fas fa-calendar"></i>
                Monthly Sales - <?php echo $selectedYear; ?>
            </h2>

            <div class="charts-grid">
                <!-- Monthly Units Chart -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Units Delivered per Month</h3>
                    <div class="chart-container">
                        <canvas id="monthlyUnitsChart"></canvas>
                    </div>
                </div>

                <!-- Monthly Orders Chart -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-line"></i> Orders per Month</h3>
                    <div class="chart-container">
                        <canvas id="monthlyOrdersChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Monthly Sales Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-table"></i> Monthly Sales Breakdown - <?php echo $selectedYear; ?></h3>
                </div>
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Units Delivered</th>
                            <th>Orders</th>
                            <th>Avg per Order</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $currentMonth = date('F');
                        $maxUnits = max(array_column($monthlySales, 'units'));
                        foreach ($allMonths as $month): 
                            $data = $monthlySales[$month];
                            $avg = $data['orders'] > 0 ? round($data['units'] / $data['orders'], 1) : 0;
                            $isCurrent = ($month === $currentMonth && $selectedYear == $currentYear);
                            $isHigh = ($data['units'] === $maxUnits && $maxUnits > 0);
                        ?>
                        <tr>
                            <td><strong><?php echo $month; ?></strong></td>
                            <td><?php echo number_format($data['units']); ?></td>
                            <td><?php echo number_format($data['orders']); ?></td>
                            <td><?php echo $avg; ?></td>
                            <td>
                                <?php if ($isCurrent): ?>
                                    <span class="badge-month badge-current"><i class="fas fa-clock"></i> Current</span>
                                <?php elseif ($isHigh): ?>
                                    <span class="badge-month badge-high"><i class="fas fa-star"></i> Highest</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td><strong>TOTAL</strong></td>
                            <td><?php echo number_format($yearlyTotal['units']); ?></td>
                            <td><?php echo number_format($yearlyTotal['orders']); ?></td>
                            <td><?php echo $yearlyTotal['orders'] > 0 ? round($yearlyTotal['units'] / $yearlyTotal['orders'], 1) : 0; ?></td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Yearly Sales Section -->
            <h2 class="section-title">
                <i class="fas fa-chart-pie"></i>
                Yearly Sales Summary
            </h2>

            <div class="charts-grid">
                <!-- Yearly Chart -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Yearly Comparison</h3>
                    <div class="chart-container">
                        <canvas id="yearlyChart"></canvas>
                    </div>
                </div>

                <!-- Yearly Table in Card -->
                <div class="chart-card">
                    <h3><i class="fas fa-list-alt"></i> Year-by-Year Data</h3>
                    <table class="sales-table" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Units</th>
                                <th>Orders</th>
                                <th>Avg/Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($yearlySales as $data): 
                                $avg = $data['orders'] > 0 ? round($data['units'] / $data['orders'], 1) : 0;
                            ?>
                            <tr <?php echo $data['year'] == $selectedYear ? 'style="background: rgba(244, 208, 63, 0.1);"' : ''; ?>>
                                <td><strong><?php echo $data['year']; ?></strong></td>
                                <td><?php echo number_format($data['units']); ?></td>
                                <td><?php echo number_format($data['orders']); ?></td>
                                <td><?php echo $avg; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($yearlySales) > 1): ?>
                            <tr class="total-row">
                                <td><strong>ALL TIME</strong></td>
                                <td><?php echo number_format($allTimeTotal['units']); ?></td>
                                <td><?php echo number_format($allTimeTotal['orders']); ?></td>
                                <td><?php echo $allTimeTotal['orders'] > 0 ? round($allTimeTotal['units'] / $allTimeTotal['orders'], 1) : 0; ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="js/app.js" defer></script>
    <script>
        // Chart colors
        const isDarkMode = !document.body.classList.contains('light-mode');
        const chartColors = {
            primary: '#f4d03f',
            secondary: '#2f5fa7',
            success: '#2ecc71',
            info: '#00d9ff',
            gridColor: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
            textColor: isDarkMode ? '#e0e0e0' : '#333'
        };

        // Monthly Units Chart
        const monthlyUnitsCtx = document.getElementById('monthlyUnitsChart').getContext('2d');
        new Chart(monthlyUnitsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $monthLabels; ?>,
                datasets: [{
                    label: 'Units Delivered',
                    data: <?php echo $monthUnits; ?>,
                    backgroundColor: 'rgba(244, 208, 63, 0.7)',
                    borderColor: '#f4d03f',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: chartColors.gridColor },
                        ticks: { color: chartColors.textColor }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: chartColors.textColor, maxRotation: 45 }
                    }
                }
            }
        });

        // Monthly Orders Chart
        const monthlyOrdersCtx = document.getElementById('monthlyOrdersChart').getContext('2d');
        new Chart(monthlyOrdersCtx, {
            type: 'line',
            data: {
                labels: <?php echo $monthLabels; ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo $monthOrders; ?>,
                    borderColor: '#00d9ff',
                    backgroundColor: 'rgba(0, 217, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#00d9ff',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: chartColors.gridColor },
                        ticks: { color: chartColors.textColor }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: chartColors.textColor, maxRotation: 45 }
                    }
                }
            }
        });

        // Yearly Chart
        const yearlyCtx = document.getElementById('yearlyChart').getContext('2d');
        new Chart(yearlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo $yearLabels; ?>,
                datasets: [{
                    label: 'Units',
                    data: <?php echo $yearUnits; ?>,
                    backgroundColor: 'rgba(47, 95, 167, 0.8)',
                    borderColor: '#2f5fa7',
                    borderWidth: 2,
                    borderRadius: 6
                }, {
                    label: 'Orders',
                    data: <?php echo $yearOrders; ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.8)',
                    borderColor: '#2ecc71',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: chartColors.textColor }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: chartColors.gridColor },
                        ticks: { color: chartColors.textColor }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: chartColors.textColor }
                    }
                }
            }
        });

        // Year selector
        function changeYear(year) {
            window.location.href = 'sales-records.php?year=' + year;
        }
    </script>
</body>
</html>
