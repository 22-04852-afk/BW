<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

// Database connection
require_once 'db_config.php';

// Initialize variables
$totalUnits = 0;
$totalOrders = 0;
$activeClients = 0;
$totalRevenue = '0';
$recentDeliveries = [];

// Get total units delivered
$result = $conn->query("SELECT COUNT(*) as total_orders, COALESCE(SUM(quantity), 0) as total_units FROM delivery_records");
if ($result && $row = $result->fetch_assoc()) {
    $totalUnits = intval($row['total_units']);
    $totalOrders = intval($row['total_orders']);
}

// Get unique companies count
$result = $conn->query("SELECT COUNT(DISTINCT company_name) as company_count FROM delivery_records WHERE company_name IS NOT NULL AND company_name != ''");
if ($result && $row = $result->fetch_assoc()) {
    $activeClients = intval($row['company_count']);
}

// Calculate estimated revenue
$totalRevenue = number_format(($totalUnits * 540) / 1000, 1);

// Get recent deliveries for export
$result = $conn->query("SELECT * FROM delivery_records ORDER BY id DESC LIMIT 100");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentDeliveries[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')==='light'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .report-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            transition: all 0.3s ease;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            border-color: #2f5fa7;
            box-shadow: 0 10px 30px rgba(47, 95, 167, 0.2);
        }
        
        .report-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2f5fa7, #00d9ff);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .report-description {
            font-size: 13px;
            color: #a0a0a0;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .report-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .btn-report {
            padding: 10px 14px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        .btn-report:hover {
            background: #2f5fa7;
            border-color: #2f5fa7;
            color: #fff;
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
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 20px;
            margin-top: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f4d03f;
        }
        
        .date-range-selector {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .date-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 14px;
            color: #1a1919;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
        }
        
        .date-input:focus {
            outline: none;
            border-color: #2f5fa7;
            background: rgba(255, 255, 255, 0.08);
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
        }
        
        .stat-label {
            font-size: 11px;
            color: #a0a0a0;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #f4d03f;
        }
        
        @media (max-width: 768px) {
            .reports-grid {
                grid-template-columns: 1fr;
            }
            
            .date-range-selector {
                flex-direction: column;
            }
        }
        
        /* Report Viewer Modal */
        .report-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .report-modal.show {
            display: flex;
        }
        
        .report-modal-content {
            background: #1a2332;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 900px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .report-modal-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .report-modal-header h2 {
            color: #fff;
            margin: 0;
            font-size: 20px;
        }
        
        .report-modal-close {
            background: none;
            border: none;
            color: #a0a0a0;
            font-size: 24px;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .report-modal-close:hover {
            color: #fff;
        }
        
        .report-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            background: linear-gradient(135deg, #0f1419 0%, #1a2332 100%);
        }
        
        .report-content {
            color: #e0e0e0;
            line-height: 1.6;
        }
        
        .report-content h3 {
            color: #f4d03f;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .report-content p {
            margin: 10px 0;
            font-size: 14px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .report-table th {
            background: rgba(47, 95, 167, 0.2);
            padding: 12px;
            text-align: left;
            color: #00d9ff;
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .report-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 13px;
        }
        
        .report-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .report-modal-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-modal {
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-modal:hover {
            background: #2f5fa7;
            border-color: #2f5fa7;
            color: #fff;
        }
        
        .btn-modal.primary {
            background: linear-gradient(135deg, #2f5fa7, #00d9ff);
            border: none;
            color: #fff;
        }
        
        .btn-modal.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(47, 95, 167, 0.3);
        }

        /* Report Filter Controls */
        .filter-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-search {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 14px;
            color: #e0e0e0;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            flex: 1;
            min-width: 200px;
        }

        .filter-search::placeholder {
            color: #707070;
        }

        .filter-search:focus {
            outline: none;
            border-color: #2f5fa7;
            background: rgba(255, 255, 255, 0.08);
        }

        .filter-btn {
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #2f5fa7;
            border-color: #2f5fa7;
            color: #fff;
        }

        .report-card.hidden {
            display: none;
        }

        /* PDF-specific styles */
        @media print {
            body {
                background: white;
            }
            .report-content {
                color: #333;
            }
            .report-content h3 {
                color: #1a5490;
                page-break-after: avoid;
            }
            .report-table {
                page-break-inside: avoid;
                border: 1px solid #ddd;
            }
            .report-table th {
                background: #e8eef5;
                color: #1a5490;
            }
            .report-table td {
                border: 1px solid #ddd;
                color: #333;
            }
            .report-modal-header,
            .report-modal-footer {
                display: none;
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
                <li class="menu-item active">
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
        <div class="page-title">
            <i class="fas fa-file-pdf"></i> Reports & Analytics
        </div>

        <!-- Report Cards -->
        <div class="section-title">Available Reports</div>
        
        <!-- Filter Controls -->
        <div class="filter-controls">
            <input type="text" class="filter-search" id="reportSearch" placeholder="Search reports...">
            <button class="filter-btn active" onclick="filterReports('all')">All</button>
            <button class="filter-btn" onclick="filterReports('sales')">Sales</button>
            <button class="filter-btn" onclick="filterReports('inventory')">Inventory</button>
            <button class="filter-btn" onclick="filterReports('analytics')">Analytics</button>
            <button class="filter-btn" onclick="filterReports('delivery')">Delivery</button>
            <button class="filter-btn" onclick="filterReports('financial')">Financial</button>
        </div>

        <div class="reports-grid">
            <div class="report-card" data-category="sales">
                <div class="report-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="report-title">Sales Performance Report</div>
                <div class="report-description">Monthly sales trends, revenue breakdown, and growth analysis</div>
                <div class="report-actions">
                    <button class="btn-report">View</button>
                    <button class="btn-report">PDF</button>
                </div>
            </div>

            <div class="report-card" data-category="inventory">
                <div class="report-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="report-title">Inventory Status Report</div>
                <div class="report-description">Current stock levels, incoming shipments, and inventory forecast</div>
                <div class="report-actions">
                    <button class="btn-report">View</button>
                    <button class="btn-report">CSV</button>
                </div>
            </div>

            <div class="report-card" data-category="analytics">
                <div class="report-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="report-title">Client Analytics Report</div>
                <div class="report-description">Client acquisition, retention, and lifetime value analysis</div>
                <div class="report-actions">
                    <button class="btn-report">View</button>
                    <button class="btn-report">PDF</button>
                </div>
            </div>

            <div class="report-card" data-category="delivery">
                <div class="report-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="report-title">Delivery Summary Report</div>
                <div class="report-description">Shipping performance, delivery times, and logistics analysis</div>
                <div class="report-actions">
                    <button class="btn-report">View</button>
                    <button class="btn-report">XLSX</button>
                </div>
            </div>

            <div class="report-card" data-category="financial">
                <div class="report-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="report-title">Financial Report</div>
                <div class="report-description">Revenue, expenses, profit margins, and financial forecasts</div>
                <div class="report-actions">
                    <button class="btn-report">View</button>
                    <button class="btn-report">PDF</button>
                </div>
            </div>

            <div class="report-card" data-category="sales">
                <div class="report-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="report-title">Product Model Report</div>
                <div class="report-description">Sales by model, performance metrics, and product popularity</div>
                <div class="report-actions">
                    <button class="btn-report">View</button>
                    <button class="btn-report">CSV</button>
                </div>
            </div>
        </div>

        <!-- Report Generator -->
        <div class="section-title">Custom Report Generator</div>
        
        <div class="date-range-selector">
            <label style="color: #a0a0a0; display: flex; align-items: center; gap: 8px; font-size: 12px;">
                <i class="fas fa-calendar"></i> From:
            </label>
            <input type="date" class="date-input" value="2025-01-01">
            <label style="color: #a0a0a0; display: flex; align-items: center; gap: 8px; font-size: 12px;">
                <i class="fas fa-calendar"></i> To:
            </label>
            <input type="date" class="date-input" value="2025-02-12">
            <button class="btn-report" style="background: linear-gradient(135deg, #2f5fa7, #1e3c72); color: white; border: none;">Generate</button>
        </div>

        <!-- Quick Stats -->
        <div class="section-title">Year-to-Date Summary</div>
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-label">Est. Revenue</div>
                <div class="stat-value">₱<?php echo $totalRevenue; ?>K</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Units Delivered</div>
                <div class="stat-value"><?php echo number_format($totalUnits); ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo number_format($totalOrders); ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Active Clients</div>
                <div class="stat-value"><?php echo number_format($activeClients); ?></div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="section-title">Data Export</div>
        <div class="reports-grid">
            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-file-csv"></i>
                </div>
                <div class="report-title">Export to CSV</div>
                <div class="report-description">Download all sales and delivery data in CSV format for Excel</div>
                <button class="btn-report" style="grid-column: 1 / -1;">Export CSV</button>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-file-excel"></i>
                </div>
                <div class="report-title">Export to Excel</div>
                <div class="report-description">Download comprehensive workbook with multiple worksheets and charts</div>
                <button class="btn-report" style="grid-column: 1 / -1;">Export XLSX</button>
            </div>

            <div class="report-card">
                <div class="report-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="report-title">Export to PDF</div>
                <div class="report-description">Download professional PDF report with charts and formatting</div>
                <button class="btn-report" style="grid-column: 1 / -1;">Export PDF</button>
            </div>
        </div>
    </main>

    <!-- Report Viewer Modal -->
    <div class="report-modal" id="reportModal">
        <div class="report-modal-content">
            <div class="report-modal-header">
                <h2 id="reportModalTitle">Report</h2>
                <button class="report-modal-close" onclick="closeReportModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="report-modal-body">
                <div class="report-content" id="reportModalBody">
                    <!-- Report content loaded here -->
                </div>
            </div>
            <div class="report-modal-footer">
                <button class="btn-modal" onclick="closeReportModal()">Close</button>
                <button class="btn-modal primary" onclick="downloadCurrentReport()">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>

    <script>
        // Sample report data
        const reportData = {
            'Sales Performance Report': {
                title: 'Sales Performance Report',
                date: new Date().toLocaleDateString(),
                content: `
                    <h3>Executive Summary</h3>
                    <p>This report provides a comprehensive overview of sales performance for the first month of 2025.</p>
                    
                    <h3>Monthly Sales Trends</h3>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Week</th>
                                <th>Sales</th>
                                <th>Units Sold</th>
                                <th>Growth %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Week 1</td><td>₱38,500</td><td>75</td><td>+12%</td></tr>
                            <tr><td>Week 2</td><td>₱42,300</td><td>82</td><td>+10%</td></tr>
                            <tr><td>Week 3</td><td>₱45,200</td><td>88</td><td>+7%</td></tr>
                            <tr><td>Week 4</td><td>₱42,500</td><td>66</td><td>-6%</td></tr>
                        </tbody>
                    </table>
                    
                    <h3>Revenue Breakdown</h3>
                    <p><strong>Group A Products:</strong> ₱95,000 (56%)</p>
                    <p><strong>Group B Products:</strong> ₱73,500 (44%)</p>
                    
                    <h3>Key Performance Indicators</h3>
                    <p><strong>Total Revenue:</strong> ₱168,500</p>
                    <p><strong>Total Units Sold:</strong> 311</p>
                    <p><strong>Average Transaction Value:</strong> ₱541.80</p>
                    <p><strong>Growth vs Last Month:</strong> +8.5%</p>
                `
            },
            'Inventory Status Report': {
                title: 'Inventory Status Report',
                date: new Date().toLocaleDateString(),
                content: `
                    <h3>Current Stock Levels</h3>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Current Stock</th>
                                <th>Min Level</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>A-100</td><td>145</td><td>50</td><td><span style="color: #51cf66;">✓ Optimal</span></td></tr>
                            <tr><td>A-500</td><td>98</td><td>40</td><td><span style="color: #51cf66;">✓ Optimal</span></td></tr>
                            <tr><td>B-100</td><td>132</td><td>60</td><td><span style="color: #51cf66;">✓ Optimal</span></td></tr>
                            <tr><td>B-500</td><td>45</td><td>50</td><td><span style="color: #ffd60a;">⚠ Low</span></td></tr>
                        </tbody>
                    </table>
                    
                    <h3>Incoming Shipments</h3>
                    <p><strong>Expected Delivery:</strong> February 20, 2025</p>
                    <p><strong>Quantity:</strong> 500 units</p>
                    <p><strong>Supplier:</strong> TechParts International</p>
                    
                    <h3>Inventory Forecast</h3>
                    <p>Based on current sales trends, inventory levels are expected to remain stable through March. Recommend restocking Group B products to maintain optimal levels.</p>
                `
            },
            'Client Analytics Report': {
                title: 'Client Analytics Report',
                date: new Date().toLocaleDateString(),
                content: `
                    <h3>Client Overview</h3>
                    <p><strong>Total Active Clients:</strong> 15</p>
                    <p><strong>New Clients This Month:</strong> 2</p>
                    <p><strong>Client Retention Rate:</strong> 93.3%</p>
                    
                    <h3>Top Clients by Revenue</h3>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>Revenue</th>
                                <th>Units Purchased</th>
                                <th>Last Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>EcoTech Solutions</td><td>₱34,500</td><td>65</td><td>Feb 10</td></tr>
                            <tr><td>Nexus Industries</td><td>₱28,300</td><td>52</td><td>Feb 8</td></tr>
                            <tr><td>Alpha Manufacturing</td><td>₱22,700</td><td>41</td><td>Feb 5</td></tr>
                            <tr><td>Delta Enterprises</td><td>₱18,500</td><td>35</td><td>Jan 28</td></tr>
                        </tbody>
                    </table>
                    
                    <h3>Client Acquisition & Retention</h3>
                    <p><strong>Customer Lifetime Value:</strong> ₱11,233 (average)</p>
                    <p><strong>Churn Rate:</strong> 6.7%</p>
                    <p><strong>Repeat Purchase Rate:</strong> 86.7%</p>
                `
            },
            'Delivery Summary Report': {
                title: 'Delivery Summary Report',
                date: new Date().toLocaleDateString(),
                content: `
                    <h3>Delivery Performance</h3>
                    <p><strong>Total Deliveries:</strong> 696</p>
                    <p><strong>On-Time Delivery Rate:</strong> 99.5%</p>
                    <p><strong>Average Delivery Time:</strong> 3.2 days</p>
                    
                    <h3>Delivery Status Breakdown</h3>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Delivered</td><td>674</td><td>96.8%</td></tr>
                            <tr><td>In Transit</td><td>12</td><td>1.7%</td></tr>
                            <tr><td>Pending</td><td>10</td><td>1.5%</td></tr>
                        </tbody>
                    </table>
                    
                    <h3>Logistics Analysis</h3>
                    <p><strong>Total Distance Covered:</strong> 15,240 km</p>
                    <p><strong>Average Cost Per Delivery:</strong> ₱42.50</p>
                    <p><strong>Fuel Efficiency:</strong> 8.2 km/liter</p>
                `
            },
            'Financial Report': {
                title: 'Financial Report',
                date: new Date().toLocaleDateString(),
                content: `
                    <h3>Financial Summary</h3>
                    <p><strong>Total Revenue:</strong> ₱168,500</p>
                    <p><strong>Cost of Goods Sold:</strong> ₱67,400</p>
                    <p><strong>Operating Expenses:</strong> ₱48,250</p>
                    <p><strong>Net Profit:</strong> ₱52,850</p>
                    
                    <h3>Profit Analysis</h3>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Gross Profit</td><td>₱101,100</td><td>59.9%</td></tr>
                            <tr><td>Operating Profit</td><td>₱52,850</td><td>31.4%</td></tr>
                            <tr><td>Net Profit</td><td>₱52,850</td><td>31.4%</td></tr>
                        </tbody>
                    </table>
                    
                    <h3>Financial Forecasts</h3>
                    <p>Based on current trends, we project for Q1 2025:</p>
                    <p><strong>Expected Revenue:</strong> ₱480,000 - ₱520,000</p>
                    <p><strong>Expected Profit Margin:</strong> 30% - 32%</p>
                `
            },
            'Product Model Report': {
                title: 'Product Model Report',
                date: new Date().toLocaleDateString(),
                content: `
                    <h3>Sales by Product Model</h3>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Model</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                                <th>Avg Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>A-100</td><td>145</td><td>₱46,400</td><td>₱320</td></tr>
                            <tr><td>A-200</td><td>158</td><td>₱71,100</td><td>₱450</td></tr>
                            <tr><td>B-100</td><td>132</td><td>₱85,800</td><td>₱650</td></tr>
                            <tr><td>B-500</td><td>45</td><td>₱56,250</td><td>₱1,250</td></tr>
                        </tbody>
                    </table>
                    
                    <h3>Product Performance Metrics</h3>
                    <p><strong>Best Selling Model:</strong> A-200 (158 units)</p>
                    <p><strong>Highest Revenue Model:</strong> B-500 (₱56,250)</p>
                    <p><strong>Most Popular Category:</strong> Group A (52.4% of sales)</p>
                    
                    <h3>Product Popularity Trends</h3>
                    <p>Group A products continue to dominate market share with steady demand. Group B premium models show strong growth potential with higher profit margins.</p>
                `
            }
        };

        let currentReportData = null;
        let currentFilterType = 'all';

        // Report functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Search input handler
            const searchInput = document.getElementById('reportSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    const searchTerm = this.value.toLowerCase();
                    filterBySearch(searchTerm);
                });
            }

            // Report card button handlers
            const reportCards = document.querySelectorAll('.report-card');
            reportCards.forEach((card, index) => {
                const buttons = card.querySelectorAll('.btn-report');
                const title = card.querySelector('.report-title')?.textContent || 'Report';
                
                buttons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const action = this.textContent.trim();
                        handleReportAction(title, action);
                    });
                });
            });
            
            // Generate button handler
            const generateBtn = document.querySelector('button[style*="linear-gradient"]');
            if (generateBtn) {
                generateBtn.addEventListener('click', function() {
                    const fromDate = document.querySelectorAll('.date-input')[0].value;
                    const toDate = document.querySelectorAll('.date-input')[1].value;
                    
                    if (!fromDate || !toDate) {
                        showNotification('Please select both start and end dates', 'error');
                        return;
                    }
                    
                    if (new Date(fromDate) > new Date(toDate)) {
                        showNotification('Start date must be before end date', 'error');
                        return;
                    }
                    
                    showNotification(`Custom report generated for ${fromDate} to ${toDate}`, 'success');
                    console.log('Report generated for period:', fromDate, 'to', toDate);
                });
            }
            
            // Export option handlers
            const exportButtons = document.querySelectorAll('button[style*="grid-column"]');
            exportButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const action = this.textContent.trim();
                    handleExport(action);
                });
            });
        });
        
        function handleReportAction(reportName, action) {
            switch(action.toUpperCase()) {
                case 'VIEW':
                    viewReport(reportName);
                    break;
                case 'PDF':
                case 'CSV':
                case 'XLSX':
                    downloadReport(reportName, action.toUpperCase());
                    break;
                default:
                    console.log('Action:', action, 'Report:', reportName);
            }
        }
        
        function viewReport(reportName) {
            const report = reportData[reportName];
            if (report) {
                currentReportData = report;
                document.getElementById('reportModalTitle').textContent = report.title;
                document.getElementById('reportModalBody').innerHTML = report.content;
                document.getElementById('reportModal').classList.add('show');
                showNotification(`Opening ${reportName}...`, 'info');
            }
        }
        
        function closeReportModal() {
            document.getElementById('reportModal').classList.remove('show');
        }
        
        function downloadCurrentReport() {
            if (currentReportData) {
                const element = document.getElementById('reportModalBody');
                const opt = {
                    margin: [15, 15, 15, 15],
                    filename: `${currentReportData.title.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, backgroundColor: '#ffffff' },
                    jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
                };
                
                // Create a styled wrapper for better PDF formatting
                const styledElement = document.createElement('div');
                styledElement.innerHTML = `
                    <div style="font-family: Arial, sans-serif; color: #333;">
                        <h1 style="color: #1a5490; border-bottom: 3px solid #1a5490; padding-bottom: 10px; margin-bottom: 20px; font-size: 24px;">
                            ${currentReportData.title}
                        </h1>
                        <p style="color: #666; margin-bottom: 20px; font-size: 12px;">
                            <strong>Generated:</strong> ${new Date().toLocaleString()}
                        </p>
                        <div style="font-size: 14px; line-height: 1.8;">
                            ${element.innerHTML}
                        </div>
                    </div>
                `;
                
                html2pdf().set(opt).from(styledElement).save();
                showNotification(`Downloading ${currentReportData.title} as PDF...`, 'success');
            }
        }
        
        function downloadReport(reportName, format) {
            const report = reportData[reportName];
            if (!report) return;
            
            if (format === 'PDF') {
                const element = document.createElement('div');
                element.innerHTML = `
                    <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.8;">
                        <h1 style="color: #1a5490; border-bottom: 3px solid #1a5490; padding-bottom: 10px; margin-bottom: 20px; font-size: 24px;">
                            ${report.title}
                        </h1>
                        <p style="color: #666; margin-bottom: 20px; font-size: 12px;">
                            <strong>Generated:</strong> ${new Date().toLocaleString()}
                        </p>
                        <div style="font-size: 14px;">
                            ${report.content}
                        </div>
                    </div>
                `;
                
                const opt = {
                    margin: [15, 15, 15, 15],
                    filename: `${reportName.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, backgroundColor: '#ffffff' },
                    jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
                };
                html2pdf().set(opt).from(element).save();
            } else {
                const filename = `${reportName.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.${format.toLowerCase()}`;
                showNotification(`Downloading ${reportName} as ${format}...`, 'success');
                console.log('Export file:', filename);
                // In a real app, this would trigger actual file download
            }
        }
        
        function handleExport(action) {
            const format = action.match(/CSV|XLSX|PDF/)[0];
            const filename = `BW_Gas_Detector_Export_${new Date().toISOString().split('T')[0]}.${format.toLowerCase()}`;
            
            if (format === 'PDF') {
                const element = document.createElement('div');
                element.innerHTML = `
                    <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.8;">
                        <h1 style="color: #1a5490; border-bottom: 3px solid #1a5490; padding-bottom: 10px; margin-bottom: 20px; font-size: 24px;">
                            BW Gas Detector - Complete Data Export
                        </h1>
                        <p style="color: #666; margin-bottom: 20px; font-size: 12px;">
                            <strong>Generated:</strong> ${new Date().toLocaleString()}
                        </p>
                        <h2 style="color: #1a5490; margin-top: 20px; margin-bottom: 10px; font-size: 16px;">Year-to-Date Summary</h2>
                        <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                            <tr style="background: #e8eef5; border: 1px solid #ddd;">
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Total Revenue</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">₱168,500</td>
                            </tr>
                            <tr style="border: 1px solid #ddd;">
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Units Delivered</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">696</td>
                            </tr>
                            <tr style="background: #e8eef5; border: 1px solid #ddd;">
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Units Sold</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">311</td>
                            </tr>
                            <tr style="border: 1px solid #ddd;">
                                <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Active Clients</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">15</td>
                            </tr>
                        </table>
                        <p style="margin-top: 20px; color: #666; font-size: 13px;">
                            This is a comprehensive export of all sales, delivery, and product data for the current period.
                        </p>
                    </div>
                `;
                
                const opt = {
                    margin: [15, 15, 15, 15],
                    filename: filename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, backgroundColor: '#ffffff' },
                    jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
                };
                html2pdf().set(opt).from(element).save();
            }
            
            showNotification(`Exporting all data as ${format}...`, 'success');
            console.log('Export file:', filename);
        }
        
        function showNotification(message, type = 'info') {
            console.log(`[${type.toUpperCase()}] ${message}`);
            
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#51cf66' : type === 'error' ? '#ff6b6b' : '#2f5fa7'};
                color: white;
                padding: 16px 24px;
                border-radius: 8px;
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                font-weight: 600;
                z-index: 10000;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Filter Reports by Category
        function filterReports(category) {
            currentFilterType = category;
            const reportCards = document.querySelectorAll('[data-category]');
            const filterBtns = document.querySelectorAll('.filter-btn');
            const searchInput = document.getElementById('reportSearch');
            
            // Clear search input when using category filter
            if (searchInput) {
                searchInput.value = '';
            }
            
            // Update active button state
            filterBtns.forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('onclick').includes(`'${category}'`)) {
                    btn.classList.add('active');
                }
            });
            
            // Filter cards
            let visibleCount = 0;
            reportCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                
                if (category === 'all' || cardCategory === category) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (visibleCount === 0) {
                showNotification('No reports found in this category', 'error');
            } else {
                showNotification(`Showing ${visibleCount} report(s)`, 'info');
            }
        }
        
        // Search Reports by Title/Description
        function filterBySearch(searchTerm) {
            const reportCards = document.querySelectorAll('[data-category]');
            let visibleCount = 0;
            
            reportCards.forEach(card => {
                const title = card.querySelector('.report-title')?.textContent.toLowerCase() || '';
                const description = card.querySelector('.report-description')?.textContent.toLowerCase() || '';
                const category = card.getAttribute('data-category');
                const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                
                // If category filter is active (not 'all'), respect it
                const matchesCategory = currentFilterType === 'all' || category === currentFilterType;
                
                if (matchesSearch && matchesCategory) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (searchTerm) {
                if (visibleCount === 0) {
                    showNotification(`No reports found matching "${searchTerm}"`, 'error');
                } else {
                    showNotification(`Found ${visibleCount} report(s)`, 'success');
                }
            }
        }
        
        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>

    <script src="js/app.js" defer></script>
</body>
</html>
