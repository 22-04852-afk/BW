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

// Get available years from data (ordered by record count DESC, then year DESC)
$availableYears = [];
$yearCountResult = $conn->query("SELECT ({$yearExpr}) as year, COUNT(*) as cnt FROM delivery_records WHERE ({$yearExpr}) > 0 GROUP BY ({$yearExpr}) ORDER BY cnt DESC, year DESC");
if ($yearCountResult) {
    $firstYear = null;
    while ($row = $yearCountResult->fetch_assoc()) {
        if (intval($row['year']) > 0) {
            if ($firstYear === null) $firstYear = intval($row['year']); // Year with most records
            $availableYears[] = intval($row['year']);
        }
    }
    // Sort years DESC for dropdown display
    rsort($availableYears);
}

// Default to the year with most data; fall back to current year if no data
$defaultYear = isset($firstYear) ? $firstYear : $currentYear;
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
            gap: 14px;
        }

        .year-selector label {
            color: #a0a0a0;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .year-selector .select-wrapper {
            position: relative;
            display: inline-block;
        }

        .year-selector .select-wrapper::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #f4d03f;
            font-size: 11px;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .year-selector .select-wrapper:hover::after {
            transform: translateY(-50%) rotate(180deg);
        }

        .year-selector select {
            padding: 12px 45px 12px 18px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 12px;
            border: 2px solid rgba(244, 208, 63, 0.3);
            background: linear-gradient(145deg, rgba(30, 42, 56, 0.95), rgba(20, 30, 45, 0.98));
            color: #fff;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            min-width: 130px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            box-shadow: 
                0 4px 15px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1),
                0 0 0 0 rgba(244, 208, 63, 0);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .year-selector select:hover {
            border-color: rgba(244, 208, 63, 0.6);
            box-shadow: 
                0 6px 20px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.15),
                0 0 20px rgba(244, 208, 63, 0.15);
            transform: translateY(-2px);
        }

        .year-selector select:focus {
            outline: none;
            border-color: #f4d03f;
            box-shadow: 
                0 6px 25px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.15),
                0 0 30px rgba(244, 208, 63, 0.25),
                0 0 0 3px rgba(244, 208, 63, 0.1);
        }

        .year-selector select option {
            background: #1e2a38;
            color: #fff;
            padding: 12px;
            font-weight: 500;
        }

        .year-selector select option:hover,
        .year-selector select option:checked {
            background: linear-gradient(135deg, #2a3f5f, #1e2a38);
        }

        .year-selector label i {
            color: #f4d03f;
            margin-right: 6px;
            font-size: 13px;
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
        html.light-mode .page-title,
        body.light-mode .page-title,
        html.light-mode .section-title,
        body.light-mode .section-title,
        html.light-mode .chart-card h3,
        body.light-mode .chart-card h3,
        html.light-mode .table-header h3,
        body.light-mode .table-header h3 {
            color: #1a3a5c;
        }

        html.light-mode .summary-card,
        body.light-mode .summary-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid #c5ddf0;
        }

        html.light-mode .summary-card .value,
        body.light-mode .summary-card .value {
            color: #1a3a5c;
        }

        html.light-mode .summary-card .label,
        body.light-mode .summary-card .label {
            color: #5a6a7a;
        }

        html.light-mode .chart-card,
        body.light-mode .chart-card,
        html.light-mode .table-container,
        body.light-mode .table-container {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid #c5ddf0;
        }

        html.light-mode .sales-table thead th,
        body.light-mode .sales-table thead th {
            background: rgba(30, 136, 229, 0.1);
            color: #1a3a5c;
            border-bottom: 2px solid #1e88e5;
        }

        html.light-mode .sales-table tbody td,
        body.light-mode .sales-table tbody td {
            color: #333;
            border-bottom: 1px solid #e0e0e0;
        }

        html.light-mode .sales-table tbody tr:hover,
        body.light-mode .sales-table tbody tr:hover {
            background: rgba(30, 136, 229, 0.05);
        }

        html.light-mode .sales-table tbody tr.total-row,
        body.light-mode .sales-table tbody tr.total-row {
            background: rgba(30, 136, 229, 0.1);
        }

        html.light-mode .sales-table tbody tr.total-row td,
        body.light-mode .sales-table tbody tr.total-row td {
            color: #1e88e5;
            border-top: 2px solid #1e88e5;
        }

        html.light-mode .year-selector select,
        body.light-mode .year-selector select {
            background: linear-gradient(145deg, #ffffff, #f0f7ff);
            border: 2px solid rgba(30, 136, 229, 0.3);
            color: #1a3a5c;
            box-shadow: 
                0 4px 15px rgba(30, 136, 229, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.9),
                0 0 0 0 rgba(30, 136, 229, 0);
        }

        html.light-mode .year-selector select:hover,
        body.light-mode .year-selector select:hover {
            border-color: rgba(30, 136, 229, 0.5);
            box-shadow: 
                0 6px 20px rgba(30, 136, 229, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.9),
                0 0 20px rgba(30, 136, 229, 0.1);
        }

        html.light-mode .year-selector select:focus,
        body.light-mode .year-selector select:focus {
            border-color: #1e88e5;
            box-shadow: 
                0 6px 25px rgba(30, 136, 229, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.9),
                0 0 30px rgba(30, 136, 229, 0.15),
                0 0 0 3px rgba(30, 136, 229, 0.1);
        }

        html.light-mode .year-selector .select-wrapper::after,
        body.light-mode .year-selector .select-wrapper::after {
            color: #1e88e5;
        }

        html.light-mode .year-selector select option,
        body.light-mode .year-selector select option {
            background: #ffffff;
            color: #1a3a5c;
        }

        html.light-mode .year-selector label,
        body.light-mode .year-selector label {
            color: #3a6a8a;
        }

        html.light-mode .year-selector label i,
        body.light-mode .year-selector label i {
            color: #1e88e5;
        }

        html.light-mode .section-title,
        body.light-mode .section-title {
            border-bottom: 2px solid #1e88e5;
        }

        html.light-mode .section-title i,
        body.light-mode .section-title i,
        html.light-mode .chart-card h3 i,
        body.light-mode .chart-card h3 i,
        html.light-mode .table-header h3 i,
        body.light-mode .table-header h3 i {
            color: #1e88e5;
        }

        html.light-mode .summary-card .icon,
        body.light-mode .summary-card .icon {
            color: #1e88e5;
        }

        html.light-mode .badge-current,
        body.light-mode .badge-current {
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

                    <!-- Models -->
                    <li class="menu-item">
                        <a href="models.php" class="menu-link">
                            <i class="fas fa-cube"></i>
                            <span class="menu-label">Models</span>
                        </a>
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
                    <label for="yearSelect"><i class="fas fa-calendar-alt"></i> Year:</label>
                    <div class="select-wrapper">
                        <select id="yearSelect" onchange="changeYear(this.value)">
                            <?php foreach ($availableYears as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo $year == $selectedYear ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="value" id="statYearlyUnits"><?php echo number_format($yearlyTotal['units']); ?></div>
                    <div class="label" id="labelUnitsYear">Units in <?php echo $selectedYear; ?></div>
                </div>
                <div class="summary-card">
                    <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="value" id="statYearlyOrders"><?php echo number_format($yearlyTotal['orders']); ?></div>
                    <div class="label" id="labelOrdersYear">Orders in <?php echo $selectedYear; ?></div>
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
            <h2 class="section-title" id="sectionMonthlyTitle">
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
                    <h3 id="tableMonthlyHeader"><i class="fas fa-table"></i> Monthly Sales Breakdown - <?php echo $selectedYear; ?></h3>
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
                    <tbody id="tableMonthlyBody">
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
        const PHP_CURRENT_MONTH = <?php echo json_encode(date('F')); ?>;
        const PHP_CURRENT_YEAR  = <?php echo intval($currentYear); ?>;
        const ALL_MONTHS = <?php echo $monthLabels; ?>;

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
        const monthlyUnitsChart = new Chart(monthlyUnitsCtx, {
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
        const monthlyOrdersChart = new Chart(monthlyOrdersCtx, {
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

        // Year selector — live update via AJAX
        function changeYear(year) {
            // Update URL without reload
            history.replaceState(null, '', 'sales-records.php?year=' + year);

            // Show loading shimmer on stat values
            ['statYearlyUnits', 'statYearlyOrders'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.style.opacity = '0.4'; }
            });

            fetch('api/sales-data.php?year=' + year)
                .then(r => r.json())
                .then(data => {
                    // Update stat cards
                    document.getElementById('statYearlyUnits').textContent  = data.yearlyUnits.toLocaleString();
                    document.getElementById('statYearlyOrders').textContent = data.yearlyOrders.toLocaleString();
                    document.getElementById('labelUnitsYear').textContent   = 'Units in ' + data.year;
                    document.getElementById('labelOrdersYear').textContent  = 'Orders in ' + data.year;
                    document.getElementById('sectionMonthlyTitle').innerHTML =
                        '<i class="fas fa-calendar"></i> Monthly Sales - ' + data.year;
                    document.getElementById('tableMonthlyHeader').innerHTML =
                        '<i class="fas fa-table"></i> Monthly Sales Breakdown - ' + data.year;

                    // Restore opacity
                    ['statYearlyUnits', 'statYearlyOrders'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) { el.style.opacity = '1'; }
                    });

                    // Rebuild monthly table
                    const tbody = document.getElementById('tableMonthlyBody');
                    const yr = parseInt(year);
                    let maxUnits = 0;
                    data.monthData.forEach(r => { if (r.units > maxUnits) maxUnits = r.units; });

                    let totalUnits = 0, totalOrders = 0;
                    let rows = '';
                    data.monthData.forEach(r => {
                        totalUnits  += r.units;
                        totalOrders += r.orders;
                        const avg = r.orders > 0 ? (r.units / r.orders).toFixed(1) : 0;
                        const isCurrent = (r.month === PHP_CURRENT_MONTH && yr === PHP_CURRENT_YEAR);
                        const isHigh    = (r.units === maxUnits && maxUnits > 0);
                        let badge = '-';
                        if (isCurrent) badge = '<span class="badge-month badge-current"><i class="fas fa-clock"></i> Current</span>';
                        else if (isHigh) badge = '<span class="badge-month badge-high"><i class="fas fa-star"></i> Highest</span>';
                        rows += `<tr><td><strong>${r.month}</strong></td><td>${r.units.toLocaleString()}</td><td>${r.orders.toLocaleString()}</td><td>${avg}</td><td>${badge}</td></tr>`;
                    });
                    const totalAvg = totalOrders > 0 ? (totalUnits / totalOrders).toFixed(1) : 0;
                    rows += `<tr class="total-row"><td><strong>TOTAL</strong></td><td>${totalUnits.toLocaleString()}</td><td>${totalOrders.toLocaleString()}</td><td>${totalAvg}</td><td>-</td></tr>`;
                    tbody.innerHTML = rows;

                    // Update charts
                    monthlyUnitsChart.data.datasets[0].data  = data.monthUnits;
                    monthlyUnitsChart.update();

                    monthlyOrdersChart.data.datasets[0].data = data.monthOrders;
                    monthlyOrdersChart.update();
                })
                .catch(() => {
                    // Fallback to full reload on error
                    window.location.href = 'sales-records.php?year=' + year;
                });
        }
    </script>
</body>
</html>
