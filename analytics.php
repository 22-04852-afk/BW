<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')==='light'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - BW Gas Detector</title>
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
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .gauge-card {
            background: linear-gradient(135deg, #4a7ba7 0%, #2e5c8a 100%);
            padding: 50px;
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .gauge-card h3 {
            font-size: 18px;
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 30px;
            text-transform: capitalize;
        }
        
        .gauge-chart {
            position: relative;
            width: 200px;
            height: 200px;
            margin-bottom: 20px;
        }
        
        .gauge-value {
            font-size: 64px;
            font-weight: 700;
            color: #f4d03f;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
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
        
        table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-delivered {
            background: rgba(52, 211, 153, 0.2);
            color: #34d399;
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
        
        .filter-section {
            background: #1a1a1a;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .filter-group {
            margin-bottom: 30px;
        }
        
        .accordion-item {
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .accordion-header {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            border: none;
            padding: 14px 16px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            text-align: left;
        }
        
        .accordion-header:hover {
            background: rgba(255, 255, 255, 0.08);
        }
        
        .accordion-icon {
            font-size: 10px;
            transition: transform 0.3s ease;
        }
        
        .accordion-header.active .accordion-icon {
            transform: rotate(180deg);
        }
        
        .accordion-content {
            background: rgba(0, 0, 0, 0.3);
            padding: 12px;
            display: none;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .accordion-content.show {
            display: block;
            max-height: 500px;
        }
        
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .checkbox-grid label {
            display: flex;
            align-items: center;
            color: #a0a0a0;
            font-size: 12px;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .checkbox-grid label:hover {
            background: rgba(244, 208, 63, 0.1);
            color: #e0e0e0;
        }
        
        .checkbox-grid input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: #f4d03f;
        }
        
        .checkbox-grid input[type="checkbox"]:checked + * {
            color: #f4d03f;
            font-weight: 600;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .apply-btn {
            flex: 1;
            background: linear-gradient(135deg, #f4d03f 0%, #e8c300 100%);
            color: #000;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .apply-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(244, 208, 63, 0.4);
        }
        
        .apply-btn:active {
            transform: scale(0.98);
        }
        
        .reset-btn {
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            color: #a0a0a0;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .reset-btn:hover {
            background: rgba(255, 0, 0, 0.1);
            color: #ff6b6b;
            border-color: #ff6b6b;
        }
        
        .filter-status {
            background: rgba(244, 208, 63, 0.1);
            border: 1px solid #f4d03f;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            color: #f4d03f;
            font-size: 12px;
            font-weight: 600;
            display: none;
            text-align: center;
        }
        
        .filter-status.show {
            display: block;
        }
        
        .horizontal-bar-container {
            background: linear-gradient(135deg, #4a7ba7 0%, #2e5c8a 100%);
            padding: 40px;
            border-radius: 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }
        
        .horizontal-bar-container h2 {
            text-align: center;
            color: #ffffff;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 30px;
        }
        
        .bar-row {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            gap: 15px;
        }
        
        .company-name {
            min-width: 280px;
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            text-align: right;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .bar-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .bar {
            height: 26px;
            background: linear-gradient(90deg, #ffeb3b 0%, #f4d03f 100%);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            font-weight: 700;
            color: #000;
            font-size: 13px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            min-width: 40px;
        }
        
        @media (max-width: 768px) {
            .filter-section {
                padding: 15px;
            }
            
            .accordion-item {
                margin-bottom: 10px;
            }
            
            .accordion-header {
                padding: 12px 14px;
                font-size: 12px;
            }
            
            .checkbox-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            
            .checkbox-grid label {
                font-size: 11px;
                padding: 4px;
            }
            
            .filter-buttons {
                flex-direction: column;
                gap: 8px;
            }
            
            .apply-btn, .reset-btn {
                padding: 12px;
                font-size: 13px;
            }
            
            .chart-row {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 12px;
            }
            
            table th, table td {
                padding: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .filter-section {
                padding: 12px;
            }
            
            .accordion-item {
                margin-bottom: 8px;
            }
            
            .accordion-header {
                padding: 10px 12px;
                font-size: 11px;
            }
            
            .checkbox-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            
            .checkbox-grid label {
                font-size: 10px;
                padding: 3px;
            }
            
            .filter-buttons {
                flex-direction: column;
                gap: 6px;
            }
            
            .apply-btn, .reset-btn {
                padding: 10px;
                font-size: 12px;
            }
                font-size: 12px;
            }
            
            .filter-options {
                gap: 6px;
            }
            
            .filter-btn {
                padding: 6px 12px;
                font-size: 10px;
            }
            
            .apply-btn {
                padding: 10px;
                font-size: 12px;
                margin-top: 10px;
            }
            
            .page-section {
                gap: 15px;
            }
            
            .gauge-card {
                padding: 25px;
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
                <li class="menu-item active">
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
            <div class="version-info">
                <p><strong>BW Gas Detector</strong></p>
                <p>v2.1.5</p>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="content-wrapper">
            <div class="page-title">
                <i class="fas fa-chart-bar"></i>
                <h1>Analytics Dashboard</h1>
            </div>

            <!-- FILTERS SECTION -->
            <div class="filter-section">
                <!-- Delivery Month Filter (Accordion) -->
                <div class="accordion-item">
                    <button type="button" class="accordion-header">
                        <i class="fas fa-calendar"></i> Delivery Month
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </button>
                    <div class="accordion-content">
                        <div class="checkbox-grid">
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="january"> January</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="february"> February</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="march"> March</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="april"> April</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="may"> May</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="june"> June</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="july"> July</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="august"> August</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="september"> September</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="month" value="october"> October</label>
                        </div>
                    </div>
                </div>

                <!-- Delivery Day Filter (Accordion) -->
                <div class="accordion-item">
                    <button type="button" class="accordion-header">
                        <i class="fas fa-calendar-days"></i> Delivery Day
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </button>
                    <div class="accordion-content">
                        <div class="checkbox-grid">
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="1"> 1</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="4"> 4</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="6"> 6</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="7"> 7</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="8"> 8</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="9"> 9</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="10"> 10</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="12"> 12</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="15"> 15</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="17"> 17</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="18"> 18</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="19"> 19</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="21"> 21</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="23"> 23</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="24"> 24</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="25"> 25</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="26"> 26</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="27"> 27</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="28"> 28</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="29"> 29</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="day" value="30"> 30</label>
                        </div>
                    </div>
                </div>

                <!-- Item Filter (Accordion) -->
                <div class="accordion-item">
                    <button type="button" class="accordion-header">
                        <i class="fas fa-cube"></i> Item
                        <i class="fas fa-chevron-down accordion-icon"></i>
                    </button>
                    <div class="accordion-content">
                        <div class="checkbox-grid">
                            <label><input type="checkbox" class="filter-checkbox" data-filter="item" value="mcx3-bc1"> MCX3-BC1</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="item" value="mcx3-fc1"> MCX3-FC1</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="item" value="mcx3-mpcb"> MCX3-MPCB</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="item" value="mcx3-xwhm"> MCX3-XWHM</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="item" value="mcxl-bc1"> MCXL-BC1</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="item" value="mcxl-fc1"> MCXL-FC1</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="item" value="mcxl-mpcb1"> MCXL-MPCB1</label>
                            <label><input type="checkbox" class="filter-checkbox" data-filter="item" value="mcxl-xwhm"> MCXL-XWHM</label>
                        </div>
                    </div>
                </div>

                <div class="filter-buttons">
                    <button class="apply-btn" id="applyBtn">Apply Filters</button>
                    <button class="reset-btn" id="resetBtn">Reset All</button>
                </div>
                <div class="filter-status"></div>
            </div>

            <!-- KEY METRICS SECTION -->
            <h2 class="section-title">📊 Key Metrics Overview</h2>
            <div class="page-section">
                <div class="gauge-card">
                    <h3>Total Quantity of Gas Detectors Delivered to Andison</h3>
                    <div class="gauge-chart">
                        <canvas id="gaugeChartAndison"></canvas>
                    </div>
                    <div class="gauge-value" id="gaugeValueAndison">696</div>
                </div>
                <div class="gauge-card">
                    <h3>Total Quantity of Gas Detectors Sold to Companies</h3>
                    <div class="gauge-chart">
                        <canvas id="gaugeChartCompanies"></canvas>
                    </div>
                    <div class="gauge-value" id="gaugeValueCompanies">311</div>
                </div>
            </div>

            <!-- TOP 15 CLIENT COMPANIES -->
            <h2 class="section-title">🏆 Quantity of Gas Detectors Sold To Top 15 Client Companies</h2>
            <div class="horizontal-bar-container">
                <div id="topCompaniesContainer"></div>
            </div>

            <!-- MONTHLY DELIVERY & SALES SECTION -->
            <h2 class="section-title">📅 Monthly Delivery & Sales Trends</h2>
            <div class="chart-row">
                <div class="chart-container-full">
                    <div class="chart-title">Monthly Delivery to Andison & Sales to Companies</div>
                    <canvas id="monthlyTrendChart" style="max-height: 300px;"></canvas>
                </div>
                <div class="chart-container-full">
                    <div class="chart-title">Monthly Comparison</div>
                    <canvas id="monthlyBarChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <!-- MONTHLY DETAIL TABLE -->
            <h2 class="section-title">Monthly Breakdown</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Delivered to Andison</th>
                            <th>Sold to Companies</th>
                            <th>Total Unit Sales</th>
                            <th>Growth %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>January</strong></td>
                            <td>98</td>
                            <td>201</td>
                            <td>299</td>
                            <td>+8.2%</td>
                        </tr>
                        <tr>
                            <td><strong>February</strong></td>
                            <td>112</td>
                            <td>224</td>
                            <td>336</td>
                            <td>+12.4%</td>
                        </tr>
                        <tr>
                            <td><strong>March</strong></td>
                            <td>125</td>
                            <td>248</td>
                            <td>373</td>
                            <td>+10.7%</td>
                        </tr>
                        <tr>
                            <td><strong>April</strong></td>
                            <td>102</td>
                            <td>215</td>
                            <td>317</td>
                            <td>-15.0%</td>
                        </tr>
                        <tr>
                            <td><strong>May</strong></td>
                            <td>118</td>
                            <td>265</td>
                            <td>383</td>
                            <td>+20.8%</td>
                        </tr>
                        <tr>
                            <td><strong>June</strong></td>
                            <td>134</td>
                            <td>289</td>
                            <td>423</td>
                            <td>+10.4%</td>
                        </tr>
                        <tr>
                            <td><strong>July</strong></td>
                            <td>127</td>
                            <td>273</td>
                            <td>400</td>
                            <td>-5.4%</td>
                        </tr>
                        <tr>
                            <td><strong>August</strong></td>
                            <td>119</td>
                            <td>256</td>
                            <td>375</td>
                            <td>-6.3%</td>
                        </tr>
                        <tr>
                            <td><strong>September</strong></td>
                            <td>145</td>
                            <td>291</td>
                            <td>436</td>
                            <td>+16.3%</td>
                        </tr>
                        <tr>
                            <td><strong>October</strong></td>
                            <td>138</td>
                            <td>312</td>
                            <td>450</td>
                            <td>+3.2%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- MODEL GROUP A ANALYTICS -->
            <h2 class="section-title">🎯 Group A Model Analytics</h2>
            <div class="chart-row">
                <div class="chart-container-full">
                    <div class="chart-title">Group A Sales Distribution (Andison vs Companies)</div>
                    <canvas id="groupAChart" style="max-height: 300px;"></canvas>
                </div>
                <div class="chart-container-full">
                    <div class="chart-title">Group A Model Performance</div>
                    <canvas id="groupABarChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <!-- GROUP A DETAIL TABLE -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Model</th>
                            <th>Delivered to Andison</th>
                            <th>Sold to Companies</th>
                            <th>Company Distribution</th>
                            <th>Total Units</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>A-100</strong></td>
                            <td>298</td>
                            <td>534</td>
                            <td>12 companies</td>
                            <td>832</td>
                        </tr>
                        <tr>
                            <td><strong>A-200</strong></td>
                            <td>356</td>
                            <td>602</td>
                            <td>14 companies</td>
                            <td>958</td>
                        </tr>
                        <tr>
                            <td><strong>A-500</strong></td>
                            <td>289</td>
                            <td>421</td>
                            <td>9 companies</td>
                            <td>710</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- MODEL GROUP B ANALYTICS -->
            <h2 class="section-title">🚀 Group B Model Analytics</h2>
            <div class="chart-row">
                <div class="chart-container-full">
                    <div class="chart-title">Group B Sales Distribution (Andison vs Companies)</div>
                    <canvas id="groupBChart" style="max-height: 300px;"></canvas>
                </div>
                <div class="chart-container-full">
                    <div class="chart-title">Group B Model Performance</div>
                    <canvas id="groupBBarChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <!-- GROUP B DETAIL TABLE -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Model</th>
                            <th>Delivered to Andison</th>
                            <th>Sold to Companies</th>
                            <th>Company Distribution</th>
                            <th>Total Units</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>B-100</strong></td>
                            <td>212</td>
                            <td>389</td>
                            <td>11 companies</td>
                            <td>601</td>
                        </tr>
                        <tr>
                            <td><strong>B-200</strong></td>
                            <td>298</td>
                            <td>512</td>
                            <td>13 companies</td>
                            <td>810</td>
                        </tr>
                        <tr>
                            <td><strong>B-500</strong></td>
                            <td>223</td>
                            <td>388</td>
                            <td>10 companies</td>
                            <td>611</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="js/app.js" defer></script>
    <script>
        console.log('=== ANALYTICS PAGE SCRIPT LOADED ===');
        
        // Gauge Charts for   Andison and Companies
        let gaugeChartAndison;
        let gaugeChartCompanies;
        let monthlyTrendChartInstance;
        let monthlyBarChartInstance;

        // Full dataset organized by month, day, and item
        const fullDataset = {
            monthly: {
                january: { andison: 98, companies: 201 },
                february: { andison: 112, companies: 224 },
                march: { andison: 125, companies: 248 },
                april: { andison: 102, companies: 215 },
                may: { andison: 118, companies: 265 },
                june: { andison: 134, companies: 289 },
                july: { andison: 127, companies: 273 },
                august: { andison: 119, companies: 256 },
                september: { andison: 145, companies: 291 },
                october: { andison: 138, companies: 312 }
            },
            items: {
                'mcx3-bc1': { andison: 95, companies: 145 },
                'mcx3-fc1': { andison: 87, companies: 134 },
                'mcx3-mpcb': { andison: 102, companies: 167 },
                'mcx3-xwhm': { andison: 89, companies: 152 },
                'mcxl-bc1': { andison: 98, companies: 156 },
                'mcxl-fc1': { andison: 92, companies: 148 },
                'mcxl-mpcb1': { andison: 105, companies: 173 },
                'mcxl-xwhm': { andison: 96, companies: 140 }
            }
        };

        function createGaugeChart(canvasId, value, max) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            return new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Value', 'Remaining'],
                    datasets: [{
                        data: [value, Math.max(0, max - value)],
                        backgroundColor: ['#f4d03f', 'rgba(255, 255, 255, 0.1)'],
                        borderColor: 'transparent',
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    circumference: 180,
                    rotation: 270,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Initialize all charts and filters when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded event fired');
        
            // Initialize charts
            gaugeChartAndison = createGaugeChart('gaugeChartAndison', 696, 800);
            gaugeChartCompanies = createGaugeChart('gaugeChartCompanies', 311, 400);

            // Create Monthly Trend Chart Instance
            const monthlyTrendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
            monthlyTrendChartInstance = new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [
                    {
                        label: 'Delivered to Andison',
                        data: [98, 112, 125, 102, 118, 134, 127, 119, 145, 138],
                        borderColor: '#2f5fa7',
                        backgroundColor: 'rgba(47, 95, 167, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#2f5fa7',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Sold to Companies',
                        data: [201, 224, 248, 215, 265, 289, 273, 256, 291, 312],
                        borderColor: '#f4d03f',
                        backgroundColor: 'rgba(244, 208, 63, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#f4d03f',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#e0e0e0', padding: 15, font: { size: 13, weight: 500 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#a0a0a0' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#a0a0a0' }
                    }
                }
            }
        });

        // Create Monthly Bar Chart Instance
        const monthlyBarCtx = document.getElementById('monthlyBarChart').getContext('2d');
        monthlyBarChartInstance = new Chart(monthlyBarCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [
                    {
                        label: 'Delivered to Andison',
                        data: [98, 112, 125, 102, 118, 134, 127, 119, 145, 138],
                        backgroundColor: '#2f5fa7',
                        borderRadius: 6
                    },
                    {
                        label: 'Sold to Companies',
                        data: [201, 224, 248, 215, 265, 289, 273, 256, 291, 312],
                        backgroundColor: '#f4d03f',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#e0e0e0', padding: 15, font: { size: 13, weight: 500 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#a0a0a0' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#a0a0a0' }
                    }
                }
            }
        });

        // Group A Doughnut Chart
        const groupACtx = document.getElementById('groupAChart').getContext('2d');
        new Chart(groupACtx, {
            type: 'doughnut',
            data: {
                labels: ['A-100', 'A-200', 'A-500'],
                datasets: [{
                    data: [832, 958, 710],
                    backgroundColor: ['#2f5fa7', '#00d9ff', '#34d399'],
                    borderColor: '#13172c',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { padding: 15 }
                    }
                }
            }
        });

        // Group A Bar Chart
        const groupABarCtx = document.getElementById('groupABarChart').getContext('2d');
        new Chart(groupABarCtx, {
            type: 'bar',
            data: {
                labels: ['A-100', 'A-200', 'A-500'],
                datasets: [
                    {
                        label: 'To Andison',
                        data: [298, 356, 289],
                        backgroundColor: '#2f5fa7',
                        borderRadius: 6
                    },
                    {
                        label: 'To Companies',
                        data: [534, 602, 421],
                        backgroundColor: '#f4d03f',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { padding: 15, font: { size: 13, weight: 500 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {},
                        ticks: {}
                    },
                    x: {
                        grid: { display: false },
                        ticks: {}
                    }
                }
            }
        });

        // Group B Doughnut Chart
        const groupBCtx = document.getElementById('groupBChart').getContext('2d');
        new Chart(groupBCtx, {
            type: 'doughnut',
            data: {
                labels: ['B-100', 'B-200', 'B-500'],
                datasets: [{
                    data: [601, 810, 611],
                    backgroundColor: ['#ff6b6b', '#ff9500', '#00d9ff'],
                    borderColor: '#13172c',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { padding: 15 }
                    }
                }
            }
        });

        // Group B Bar Chart
        const groupBBarCtx = document.getElementById('groupBBarChart').getContext('2d');
        new Chart(groupBBarCtx, {
            type: 'bar',
            data: {
                labels: ['B-100', 'B-200', 'B-500'],
                datasets: [
                    {
                        label: 'To Andison',
                        data: [212, 298, 223],
                        backgroundColor: '#ff6b6b',
                        borderRadius: 6
                    },
                    {
                        label: 'To Companies',
                        data: [389, 512, 388],
                        backgroundColor: '#ff9500',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { padding: 15, font: { size: 13, weight: 500 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {},
                        ticks: {}
                    },
                    x: {
                        grid: { display: false },
                        ticks: {}
                    }
                }
            }
        });

        // Gauge Charts for Andison and Companies
        const gaugeData = {
            andison: 696,
            companies: 311
        };

        // Gauge Chart - Andison
        const gaugeAndisonCtx = document.getElementById('gaugeChartAndison').getContext('2d');
        new Chart(gaugeAndisonCtx, {
            type: 'doughnut',
            data: {
                labels: ['Delivered', 'Remaining'],
                datasets: [{
                    data: [gaugeData.andison, 300 - (gaugeData.andison % 300)],
                    backgroundColor: ['#f4d03f', 'rgba(255, 255, 255, 0.1)'],
                    borderColor: 'transparent',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                circumference: 180,
                rotation: 270,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Gauge Chart - Companies
        const gaugeCompaniesCtx = document.getElementById('gaugeChartCompanies').getContext('2d');
        new Chart(gaugeCompaniesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sold', 'Remaining'],
                datasets: [{
                    data: [gaugeData.companies, 400 - (gaugeData.companies % 400)],
                    backgroundColor: ['#f4d03f', 'rgba(255, 255, 255, 0.1)'],
                    borderColor: 'transparent',
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                circumference: 180,
                rotation: 270,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Top 15 Companies Horizontal Bar Chart
        const topCompaniesData = [
            { name: 'to Andison Manila', value: 67 },
            { name: 'Industrial Controls Systems Inc.', value: 21 },
            { name: 'Linde Philippines Inc.', value: 18 },
            { name: 'Herma Shipping & Transport Corp.', value: 13 },
            { name: 'Linseed Field Corporation', value: 12 },
            { name: 'UTV Construction & Engineering Services', value: 10 },
            { name: 'Scientific Drilling Inc.', value: 8 },
            { name: 'Nikkeru Plant Maintenance', value: 8 },
            { name: 'LS Instrumentation Sales & Service', value: 8 },
            { name: 'Mitsubishi Power (Philippines) Inc.', value: 6 },
            { name: 'Joel Chavez Construction', value: 6 },
            { name: 'Seatrium Subic Shipyard, Inc.', value: 5 },
            { name: 'Nippon Kaiji', value: 5 },
            { name: 'Mechatrends Contractors Corporation', value: 5 },
            { name: 'TVI Resource Development Phils., Inc.', value: 4 }
        ];

        const container = document.getElementById('topCompaniesContainer');
        console.log('Container found:', container ? 'YES' : 'NO');
        
        if (container) {
            const maxValue = Math.max(...topCompaniesData.map(d => d.value));
            console.log('Max value:', maxValue);
            console.log('Companies count:', topCompaniesData.length);

            topCompaniesData.forEach(company => {
                const barWidth = (company.value / maxValue) * 100;
                const barHTML = `
                    <div class="bar-row">
                        <div class="company-name">${company.name}</div>
                        <div class="bar-wrapper">
                            <div class="bar" style="width: ${barWidth}%; min-width: 40px;">
                                ${company.value}
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += barHTML;
            });
            console.log('Companies populated successfully');
        }

            // Accordion Setup - Attach event listeners to headers
            const accordionHeaders = document.querySelectorAll('.accordion-header');
            console.log('=== ACCORDION SETUP ===');
            console.log('Accordion headers found:', accordionHeaders.length);
            
            if (accordionHeaders.length > 0) {
                accordionHeaders.forEach(function(header, index) {
                    console.log('Setting up accordion header', index, ':', header.textContent.trim());
                    
                    header.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const content = this.nextElementSibling;
                        console.log('Header clicked, content found:', content ? 'YES' : 'NO');
                        
                        if (content) {
                            content.classList.toggle('show');
                            this.classList.toggle('active');
                            
                            const isNowOpen = content.classList.contains('show');
                            console.log('Accordion toggled to:', isNowOpen ? 'OPEN' : 'CLOSED');
                        }
                    });
                });
            } else {
                console.error('NO ACCORDION HEADERS FOUND!');
            }
            
            // Multi-Select Filter Functionality
        let activeFilters = {
            months: [],
            days: [],
            items: []
        };

        function updateAnalyticsDisplay() {
            console.log('Updating analytics with filters:', JSON.stringify(activeFilters));
            
            let andisonTotal = 0;
            let companiesTotal = 0;
            let filterCount = 0;
            
            // Calculate totals from selected items
            if (activeFilters.items.length > 0) {
                activeFilters.items.forEach(item => {
                    if (fullDataset.items[item]) {
                        andisonTotal += fullDataset.items[item].andison;
                        companiesTotal += fullDataset.items[item].companies;
                        filterCount++;
                    }
                });
                console.log('Applied item filters:', activeFilters.items, '-> A:', andisonTotal, 'C:', companiesTotal);
            }
            // Calculate totals from selected months
            else if (activeFilters.months.length > 0) {
                activeFilters.months.forEach(month => {
                    if (fullDataset.monthly[month]) {
                        andisonTotal += fullDataset.monthly[month].andison;
                        companiesTotal += fullDataset.monthly[month].companies;
                        filterCount++;
                    }
                });
                console.log('Applied month filters:', activeFilters.months, '-> A:', andisonTotal, 'C:', companiesTotal);
            }
            // Calculate totals from selected days
            else if (activeFilters.days.length > 0) {
                activeFilters.days.forEach(day => {
                    const dayNum = parseInt(day);
                    andisonTotal += Math.round((696 / 30) * dayNum);
                    companiesTotal += Math.round((311 / 30) * dayNum);
                    filterCount++;
                });
                console.log('Applied day filters:', activeFilters.days, '-> A:', andisonTotal, 'C:', companiesTotal);
            }
            else {
                // No filters - show all data
                andisonTotal = 696;
                companiesTotal = 311;
            }
            
            // Update gauge charts
            try {
                gaugeChartAndison.data.datasets[0].data = [andisonTotal, 800 - andisonTotal];
                gaugeChartAndison.update();
                gaugeChartCompanies.data.datasets[0].data = [companiesTotal, 400 - companiesTotal];
                gaugeChartCompanies.update();
                
                // Update displayed values
                document.getElementById('gaugeValueAndison').textContent = andisonTotal;
                document.getElementById('gaugeValueCompanies').textContent = companiesTotal;
                
                console.log('Gauges updated successfully');
            } catch (e) {
                console.error('Error updating charts:', e);
            }
            
            // Update monthly charts
            try {
                monthlyTrendChartInstance.data.datasets[0].data = [98, 112, 125, 102, 118, 134, 127, 119, 145, 138];
                monthlyTrendChartInstance.data.datasets[1].data = [201, 224, 248, 215, 265, 289, 273, 256, 291, 312];
                monthlyTrendChartInstance.update();
                
                // Update bar chart
                monthlyBarChartInstance.data.datasets[0].data = monthlyTrendChartInstance.data.datasets[0].data;
                monthlyBarChartInstance.data.datasets[1].data = monthlyTrendChartInstance.data.datasets[1].data;
                monthlyBarChartInstance.update();
            } catch (e) {
                console.error('Error updating trend charts:', e);
            }
        }

        // Get all filter checkboxes
        const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
        console.log('Filter checkboxes found:', filterCheckboxes.length);
        
        // Attach change listener to each checkbox
        filterCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function(e) {
                const filterType = this.dataset.filter;
                const filterValue = this.value;
                
                console.log('Checkbox changed - Type:', filterType, 'Value:', filterValue, 'Checked:', this.checked);
                
                // Update activeFilters array
                if (filterType === 'month') {
                    if (this.checked) {
                        if (!activeFilters.months.includes(filterValue)) {
                            activeFilters.months.push(filterValue);
                        }
                    } else {
                        activeFilters.months = activeFilters.months.filter(m => m !== filterValue);
                    }
                } else if (filterType === 'day') {
                    if (this.checked) {
                        if (!activeFilters.days.includes(filterValue)) {
                            activeFilters.days.push(filterValue);
                        }
                    } else {
                        activeFilters.days = activeFilters.days.filter(d => d !== filterValue);
                    }
                } else if (filterType === 'item') {
                    if (this.checked) {
                        if (!activeFilters.items.includes(filterValue)) {
                            activeFilters.items.push(filterValue);
                        }
                    } else {
                        activeFilters.items = activeFilters.items.filter(i => i !== filterValue);
                    }
                }
                
                console.log('Active filters updated:', JSON.stringify(activeFilters));
            });
        });
        
        // Get Apply button
        const applyBtn = document.getElementById('applyBtn');
        console.log('Apply button found:', applyBtn ? 'YES' : 'NO');
        
        if (applyBtn) {
            applyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('=== APPLY BUTTON CLICKED ===');
                updateAnalyticsDisplay();
                updateFilterStatus();
            });
        }
        
        // Get Reset button
        const resetBtn = document.getElementById('resetBtn');
        console.log('Reset button found:', resetBtn ? 'YES' : 'NO');
        
        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('=== RESET BUTTON CLICKED ===');
                
                // Clear all checkboxes
                filterCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = false;
                });
                
                // Clear active filters
                activeFilters = {
                    months: [],
                    days: [],
                    items: []
                };
                
                console.log('Filters reset');
            });
        }
        
        function updateFilterStatus() {
            const statusEl = document.querySelector('.filter-status');
            const totalSelected = activeFilters.months.length + activeFilters.days.length + activeFilters.items.length;
            
            if (totalSelected === 0) {
                statusEl.classList.remove('show');
                return;
            }
            
            let parts = [];
            if (activeFilters.months.length > 0) {
                parts.push('Months: ' + activeFilters.months.join(', '));
            }
            if (activeFilters.days.length > 0) {
                parts.push('Days: ' + activeFilters.days.join(', '));
            }
            if (activeFilters.items.length > 0) {
                parts.push('Items: ' + activeFilters.items.join(', '));
            }
            
            statusEl.textContent = '✓ ' + totalSelected + ' filter(s) applied: ' + parts.join(' | ');
            statusEl.classList.add('show');
            console.log('Filter status updated:', statusEl.textContent);
        }
        
        console.log('Filter setup completed');
        
        }); // Close DOMContentLoaded event listener
    </script>
</body>
</html>
