<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

// Include database configuration
require_once 'db_config.php';

// Get sales statistics from database
$stats = [
    'total_units' => 0,
    'total_orders' => 0,
    'unique_products' => 0,
    'avg_order_size' => 0
];

// Total units delivered
$result = $conn->query("SELECT COALESCE(SUM(quantity), 0) as total FROM delivery_records");
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_units'] = intval($row['total']);
}

// Total orders (records)
$result = $conn->query("SELECT COUNT(*) as total FROM delivery_records");
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_orders'] = intval($row['total']);
}

// Unique products
$result = $conn->query("SELECT COUNT(DISTINCT item_code) as total FROM delivery_records WHERE item_code IS NOT NULL AND item_code != ''");
if ($result && $row = $result->fetch_assoc()) {
    $stats['unique_products'] = intval($row['total']);
}

// Average order size
if ($stats['total_orders'] > 0) {
    $stats['avg_order_size'] = round($stats['total_units'] / $stats['total_orders'], 1);
}

// Monthly sales data
$months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
$monthly_sales = array_fill_keys($months, 0);
$result = $conn->query("SELECT delivery_month, COALESCE(SUM(quantity), 0) AS total FROM delivery_records GROUP BY delivery_month");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (array_key_exists($row['delivery_month'], $monthly_sales)) {
            $monthly_sales[$row['delivery_month']] = intval($row['total']);
        }
    }
}

// Top products
$top_products = [];
$result = $conn->query("
    SELECT item_code, item_name, SUM(quantity) as total_qty, COUNT(*) as order_count
    FROM delivery_records 
    WHERE item_code IS NOT NULL AND item_code != '' AND item_code != '-'
    GROUP BY item_code 
    ORDER BY total_qty DESC 
    LIMIT 10
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $top_products[] = $row;
    }
}

// Recent sales
$recent_sales = [];
$result = $conn->query("
    SELECT invoice_no, item_code, item_name, quantity, company_name, delivery_date, delivery_month, delivery_day
    FROM delivery_records 
    ORDER BY id DESC 
    LIMIT 10
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_sales[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')==='light'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Overview - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>
        .page-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-card-content h3 {
            font-size: 13px;
            color: #a0a0a0;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .stat-card-content .value {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
        }
        
        .stat-card-icon {
            font-size: 48px;
            color: #f4d03f;
            opacity: 0.7;
        }
        
        .chart-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-container-full {
            background: #13172c;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            min-height: 350px;
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        
        .table-container {
            background: #13172c;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-size: 12px;
            color: #a0a0a0;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            font-size: 14px;
        }
        
        table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge.success {
            background: rgba(81, 207, 102, 0.2);
            color: #51cf66;
        }
        
        .badge.pending {
            background: rgba(255, 214, 10, 0.2);
            color: #ffd60a;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        @media (max-width: 768px) {
            .chart-row {
                grid-template-columns: 1fr;
            }
            
            .page-section {
                grid-template-columns: 1fr;
            }
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
                        <a href="#"><i class="fas fa-question-circle"></i> Help</a>
                        <hr>
                        <a href="logout.php" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                <li class="menu-item">
                    <a href="index.php" class="menu-link">
                        <i class="fas fa-chart-line"></i>
                        <span class="menu-label">Dashboard</span>
                    </a>
                </li>

                <!-- Sales Overview -->
                <li class="menu-item active">
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
            <p class="company-info">Addison Industrial</p>
            <p class="company-year">© 2025</p>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="mainContent">
        <div class="page-title">
            <i class="fas fa-chart-bar"></i> Sales Overview
        </div>

        <!-- Key Metrics -->
        <div class="page-section">
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>Total Units</h3>
                    <div class="value"><?php echo number_format($stats['total_units']); ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>Total Orders</h3>
                    <div class="value"><?php echo number_format($stats['total_orders']); ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>Unique Products</h3>
                    <div class="value"><?php echo $stats['unique_products']; ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-cube"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>Avg Order Size</h3>
                    <div class="value"><?php echo $stats['avg_order_size']; ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-row">
            <div class="chart-container-full">
                <div class="chart-title">Monthly Sales Trend</div>
                <canvas id="salesTrendChart"></canvas>
            </div>
            <div class="chart-container-full">
                <div class="chart-title">Sales by Model Group</div>
                <canvas id="modelGroupChart"></canvas>
            </div>
        </div>

        <!-- Sales Summary Table -->
        <div class="table-container">
            <div class="chart-title">Recent Deliveries</div>
            <table>
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Item Code</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Company</th>
                        <th>Delivery Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_sales)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #a0a0a0;">
                            No sales data available. <a href="upload-data.php" style="color: #f4d03f;">Upload data</a> to get started.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recent_sales as $sale): 
                        $delivery_date = '';
                        if (!empty($sale['delivery_date'])) {
                            $delivery_date = date('M j, Y', strtotime($sale['delivery_date']));
                        } elseif (!empty($sale['delivery_month'])) {
                            $delivery_date = $sale['delivery_month'] . ' ' . ($sale['delivery_day'] ?? '');
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['invoice_no'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($sale['item_code'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars(substr($sale['item_name'] ?? '-', 0, 30)); ?></td>
                        <td><?php echo htmlspecialchars($sale['quantity'] ?? '0'); ?></td>
                        <td><?php echo htmlspecialchars(substr($sale['company_name'] ?? '-', 0, 20)); ?></td>
                        <td><?php echo htmlspecialchars($delivery_date); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/app.js" defer></script>
    <script>
        // Data from PHP
        const salesData = {
            monthly_sales: <?php echo json_encode($monthly_sales); ?>,
            top_products: <?php echo json_encode($top_products); ?>
        };

        // Initialize charts for Sales Overview
        function initializeSalesTrendChart() {
            const ctx = document.getElementById('salesTrendChart');
            if (ctx) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const fullMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                const monthlyData = fullMonths.map(m => salesData.monthly_sales[m] || 0);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: 'Units Delivered',
                                data: monthlyData,
                                borderColor: '#f4d03f',
                                backgroundColor: 'rgba(244, 208, 63, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#f4d03f'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                labels: {}
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {},
                                grid: {}
                            },
                            x: {
                                ticks: {},
                                grid: {}
                            }
                        }
                    }
                });
            }
        }

        function initializeModelGroupChart() {
            const ctx = document.getElementById('modelGroupChart');
            if (ctx) {
                // Use top products data
                let labels = ['Product 1', 'Product 2', 'Product 3', 'Product 4', 'Product 5'];
                let data = [100, 80, 60, 40, 20];
                
                if (salesData.top_products && salesData.top_products.length > 0) {
                    labels = salesData.top_products.slice(0, 5).map(p => p.item_code || 'Unknown');
                    data = salesData.top_products.slice(0, 5).map(p => parseInt(p.total_qty) || 0);
                }

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: ['#2f5fa7', '#f4d03f', '#51cf66', '#ff006e', '#00d9ff'],
                            borderColor: '#13172c',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                labels: {}
                            }
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeSalesTrendChart();
            initializeModelGroupChart();
        });
    </script>
</body>
</html>
