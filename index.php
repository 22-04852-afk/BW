<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once 'db_config.php';

// Get logged-in user information
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? 'User';
$user_name = $_SESSION['user_name'] ?? 'User';

// Users table is created by db_config.php (MySQL) or the SQLite bootstrap.
// No duplicate CREATE TABLE needed here.

// Get dashboard statistics
$stats = [
    'total_delivered' => 0,
    'total_sold' => 0,
    'total_companies' => 0,
    'active_models' => 0,
    'monthly_average' => 0,
    'yearly_total' => 0
];

// Count total delivered
$result = $conn->query("SELECT COALESCE(SUM(quantity), 0) as total FROM delivery_records WHERE status = 'Delivered'");
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_delivered'] = intval($row['total']);
}

// Count total sold (different from delivered - could be from sales data)
$result = $conn->query("SELECT COUNT(*) as total FROM delivery_records WHERE status IN ('Delivered', 'In Transit')");
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_sold'] = intval($row['total']);
}

// Count unique companies
$result = $conn->query("SELECT COUNT(DISTINCT company_name) as total FROM delivery_records");
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_companies'] = intval($row['total']);
}

// Count unique item codes (models)
$result = $conn->query("SELECT COUNT(DISTINCT item_code) as total FROM delivery_records");
if ($result && $row = $result->fetch_assoc()) {
    $stats['active_models'] = intval($row['total']);
}

// Calculate monthly average
if ($stats['total_delivered'] > 0) {
    $stats['monthly_average'] = round($stats['total_delivered'] / 12);
}

// Calculate yearly total
$stats['yearly_total'] = $stats['total_delivered'] + $stats['total_sold'];

// Get top clients
$top_clients = [];
$result = $conn->query("
    SELECT company_name, COUNT(*) as delivery_count, SUM(quantity) as total_quantity
    FROM delivery_records
    GROUP BY company_name
    ORDER BY total_quantity DESC
    LIMIT 15
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $top_clients[] = $row;
    }
}

// Get monthly sales data — single query instead of 12
$months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
$monthly_sales = array_fill_keys($months, 0);
$result = $conn->query("
    SELECT delivery_month, COALESCE(SUM(quantity), 0) AS total
    FROM delivery_records
    GROUP BY delivery_month
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (array_key_exists($row['delivery_month'], $monthly_sales)) {
            $monthly_sales[$row['delivery_month']] = intval($row['total']);
        }
    }
}

// Get top products by item code
$top_products = [];
$result = $conn->query("
    SELECT item_code, item_name, SUM(quantity) as total 
    FROM delivery_records 
    WHERE item_code IS NOT NULL AND item_code != '' AND item_code != '-'
    GROUP BY item_code 
    ORDER BY total DESC 
    LIMIT 10
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $top_products[] = $row;
    }
}

// Get delivery by company for pie chart
$company_deliveries = [];
$result = $conn->query("
    SELECT company_name, SUM(quantity) as total 
    FROM delivery_records 
    WHERE company_name IS NOT NULL AND company_name != '' AND company_name != '-'
    GROUP BY company_name 
    ORDER BY total DESC 
    LIMIT 8
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $company_deliveries[] = $row;
    }
}

// Get pending count
$pending_count = 0;
$result = $conn->query("SELECT COUNT(*) as cnt FROM delivery_records WHERE status = 'Pending' OR status = 'In Transit'");
if ($result && $row = $result->fetch_assoc()) {
    $pending_count = intval($row['cnt']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')==='light'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BW Gas Detector Sales  - Andison Industrial</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Page loader: dismissed once all resources are ready -->
    <div id="pageLoader" aria-hidden="true">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <p class="loader-text">Loading Dashboard…</p>
        </div>
    </div>

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

            <!-- Center Title -->
            <div class="navbar-center">
                <h1 class="dashboard-title">BW Gas Detector Sales</h1>
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
                        <a href="#"><i class="fas fa-question-circle"></i> Help</a>
                        <hr>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <!-- Sidebar Menu -->
            <ul class="sidebar-menu">
                <!-- Dashboard -->
                <li class="menu-item active">
                    <a href="#" class="menu-link">
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

                <!-- Sales Records -->
                <li class="menu-item">
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
        </div>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <p class="company-info">Andison Industrial</p>
            <p class="company-year">© 2025</p>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="mainContent">
        <!-- Welcome Banner Section -->
        <div class="welcome-banner industrial">
            <div class="banner-overlay"></div>
            <div class="welcome-content">
                <div class="welcome-left">
                    <div class="industrial-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="welcome-text">
                        <h2>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
                        <p><?php echo $stats['monthly_average'] > 0 ? 'Operations running smoothly. Keep up the great work!' : 'Ready to track your industrial operations!'; ?></p>
                    </div>
                </div>
                <div class="welcome-center">
                    <div class="stat-cards-industrial">
                        <div class="stat-card-ind highlight">
                            <div class="stat-icon-ind green">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-info-ind">
                                <span class="stat-value-ind">₱<?php echo number_format((intval($stats['total_delivered']) * 500) / 1000, 1); ?>K</span>
                                <span class="stat-label-ind">Revenue</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="welcome-right">
                    <div class="quick-actions">
                        <button class="btn-industrial" onclick="goToReports()">
                            <i class="fas fa-chart-line"></i>
                            <span>View Reports</span>
                        </button>
                        <button class="btn-industrial secondary" onclick="window.location.href='delivery-records.php'">
                            <i class="fas fa-truck"></i>
                            <span>Deliveries</span>
                        </button>
                    </div>
                    <div class="status-indicator">
                        <span class="status-dot online"></span>
                        <span class="status-text">System Online</span>
                    </div>
                </div>
            </div>
            <div class="industrial-pattern"></div>
        </div>

        <!-- KPI METRICS SECTION -->
        <section class="kpi-metrics">
            <!-- Total Orders -->
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="metric-info">
                    <span class="metric-label">Total Delivered</span>
                    <span class="metric-value"><?php echo $stats['total_delivered']; ?></span>
                    <div class="metric-trend up">
                        <i class="fas fa-arrow-up"></i>
                        <span>24%</span>
                    </div>
                </div>
                <canvas id="sparkline1" class="sparkline-chart"></canvas>
            </div>

            <!-- Total Sold -->
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="metric-info">
                    <span class="metric-label">Total Sold</span>
                    <span class="metric-value"><?php echo $stats['total_sold']; ?></span>
                    <div class="metric-trend up">
                        <i class="fas fa-arrow-up"></i>
                        <span>14%</span>
                    </div>
                </div>
                <canvas id="sparkline2" class="sparkline-chart"></canvas>
            </div>

            <!-- Total Companies -->
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="metric-info">
                    <span class="metric-label">Client Companies</span>
                    <span class="metric-value"><?php echo $stats['total_companies']; ?></span>
                    <div class="metric-trend down">
                        <i class="fas fa-arrow-down"></i>
                        <span>35%</span>
                    </div>
                </div>
                <canvas id="sparkline3" class="sparkline-chart"></canvas>
            </div>

            <!-- Active Models -->
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="metric-info">
                    <span class="metric-label">Active Models</span>
                    <span class="metric-value"><?php echo $stats['active_models']; ?></span>
                    <div class="metric-trend up">
                        <i class="fas fa-arrow-up"></i>
                        <span>18%</span>
                    </div>
                </div>
                <canvas id="sparkline4" class="sparkline-chart"></canvas>
            </div>
        </section>

        <!-- Monthly/Yearly Stats -->
        <section class="stats-summary">
            <div class="summary-card primary">
                <div class="summary-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="summary-info">
                    <span class="summary-label">Monthly Average</span>
                    <span class="summary-value"><?php echo $stats['monthly_average']; ?></span>
                    <span class="summary-subtitle">Units per month</span>
                </div>
            </div>
            <div class="summary-card secondary">
                <div class="summary-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="summary-info">
                    <span class="summary-label">Yearly Total</span>
                    <span class="summary-value"><?php echo $stats['yearly_total']; ?></span>
                    <span class="summary-subtitle">Total deliveries + sales</span>
                </div>
            </div>
        </section>

        <!-- KPI CARDS SECTION -->
        <section class="kpi-section">
            <!-- Total Delivered Card -->
            <div class="kpi-card">
                <div class="card-header">
                    <h3>Total Delivered</h3>
                    <span class="card-icon"><i class="fas fa-check-circle"></i></span>
                </div>
                <div class="card-content">
                    <div class="chart-container">
                        <canvas id="deliveredChart"></canvas>
                    </div>
                    <div class="card-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Units</span>
                            <span class="stat-value">696</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">October</span>
                            <span class="stat-value">39</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Sold Card -->
            <div class="kpi-card">
                <div class="card-header">
                    <h3>Total Sold</h3>
                    <span class="card-icon"><i class="fas fa-dollar-sign"></i></span>
                </div>
                <div class="card-content">
                    <div class="chart-container">
                        <canvas id="soldChart"></canvas>
                    </div>
                    <div class="card-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Units</span>
                            <span class="stat-value">311</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">October</span>
                            <span class="stat-value">42</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Comparison Card -->
            <div class="kpi-card">
                <div class="card-header">
                    <h3>Monthly Comparison</h3>
                    <span class="card-icon"><i class="fas fa-balance-scale"></i></span>
                </div>
                <div class="card-content">
                    <div class="chart-container">
                        <canvas id="monthlyComparisonChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <!-- MIDDLE SECTION - TWO PANELS -->
        <section class="middle-section">
            <!-- Top 15 Client Companies -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h3>Top 15 Client Companies</h3>
                    <button class="panel-menu-btn" aria-label="Panel menu">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
                <div class="panel-content">
                    <canvas id="clientsChart"></canvas>
                </div>
            </div>

            <!-- Monthly Sales Trend -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h3>Monthly Sales Trend</h3>
                    <button class="panel-menu-btn" aria-label="Panel menu">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
                <div class="panel-content">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </section>

        <!-- BOTTOM SECTION - TWO CHARTS -->
        <section class="bottom-section">
            <!-- Quantity per Model - Group A -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h3>Quantity per Model (Group A)</h3>
                    <button class="panel-menu-btn" aria-label="Panel menu">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
                <div class="panel-content">
                    <canvas id="groupAChart"></canvas>
                </div>
            </div>

            <!-- Quantity per Model - Group B -->
            <div class="dashboard-panel">
                <div class="panel-header">
                    <h3>Quantity per Model (Group B)</h3>
                    <button class="panel-menu-btn" aria-label="Panel menu">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
                <div class="panel-content">
                    <canvas id="groupBChart"></canvas>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="dashboard-footer">
            <p>&copy; 2025 Andison Industrial. All rights reserved. | BW Gas Detector Sales Management System</p>
        </footer>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Navigation function
        function goToReports() {
            window.location.href = 'reports.php';
        }

        // Pass PHP data to JavaScript
        const dashboardData = {
            total_delivered: <?php echo $stats['total_delivered']; ?>,
            total_sold: <?php echo $stats['total_sold']; ?>,
            total_companies: <?php echo $stats['total_companies']; ?>,
            active_models: <?php echo $stats['active_models']; ?>,
            pending_count: <?php echo $pending_count; ?>,
            monthly_sales: <?php echo json_encode($monthly_sales); ?>,
            top_clients: <?php echo json_encode($top_clients); ?>,
            top_products: <?php echo json_encode($top_products); ?>,
            company_deliveries: <?php echo json_encode($company_deliveries); ?>
        };

        console.log('Dashboard data loaded:', dashboardData);

        // Dismiss loader once everything (fonts, Chart.js, images) is painted
        window.addEventListener('load', function () {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.add('loader-hidden');
                // Remove from DOM after transition
                loader.addEventListener('transitionend', () => loader.remove(), { once: true });
            }
        });

        // Safety fallback — remove after 4 s max
        setTimeout(function () {
            const loader = document.getElementById('pageLoader');
            if (loader) loader.remove();
        }, 4000);
    </script>
    <script src="js/app.js" defer></script>
</body>
</html>
