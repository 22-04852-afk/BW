<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Overview - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <i class="fas fa-industry"></i>
                    <span>Addison</span>
                </div>
            </div>

            <!-- Right Profile Section -->
            <div class="navbar-end">
                <div class="notification" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="profile-dropdown">
                    <button class="profile-btn" id="profileBtn" aria-label="Profile menu">
                        <img src="https://via.placeholder.com/40" alt="User avatar" class="profile-avatar">
                        <span class="profile-name">John Anderson</span>
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
                    <h3>Total Revenue</h3>
                    <div class="value">$168.5K</div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>Units Sold</h3>
                    <div class="value">311</div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>Active Clients</h3>
                    <div class="value">15</div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>Avg Order Value</h3>
                    <div class="value">$541</div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-tag"></i>
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
            <div class="chart-title">Top Performing Metrics</div>
            <table>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Current Month</th>
                        <th>Last Month</th>
                        <th>Growth</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Units Delivered</td>
                        <td>94</td>
                        <td>88</td>
                        <td>+6.8%</td>
                        <td><span class="badge success">on track</span></td>
                    </tr>
                    <tr>
                        <td>Units Sold</td>
                        <td>52</td>
                        <td>45</td>
                        <td>+15.6%</td>
                        <td><span class="badge success">exceeding</span></td>
                    </tr>
                    <tr>
                        <td>Revenue Generated</td>
                        <td>$28,100</td>
                        <td>$24,300</td>
                        <td>+15.6%</td>
                        <td><span class="badge success">excellent</span></td>
                    </tr>
                    <tr>
                        <td>New Clients Acquired</td>
                        <td>2</td>
                        <td>1</td>
                        <td>+100%</td>
                        <td><span class="badge success">growing</span></td>
                    </tr>
                    <tr>
                        <td>Avg Delivery Time</td>
                        <td>4.2 days</td>
                        <td>4.8 days</td>
                        <td>-12.5%</td>
                        <td><span class="badge success">improved</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
        // Initialize charts for Sales Overview
        function initializeSalesTrendChart() {
            const ctx = document.getElementById('salesTrendChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                        datasets: [
                            {
                                label: 'Units Sold',
                                data: [28, 35, 42, 38, 45, 52, 58, 48, 55, 52],
                                borderColor: '#f4d03f',
                                backgroundColor: 'rgba(244, 208, 63, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#f4d03f'
                            },
                            {
                                label: 'Revenue ($K)',
                                data: [15, 19, 23, 20, 24, 28, 32, 26, 30, 28],
                                borderColor: '#00d9ff',
                                backgroundColor: 'rgba(0, 217, 255, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#00d9ff'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                labels: { color: '#e0e0e0' }
                            }
                        },
                        scales: {
                            y: {
                                ticks: { color: '#a0a0a0' },
                                grid: { color: 'rgba(255, 255, 255, 0.05)' }
                            },
                            x: {
                                ticks: { color: '#a0a0a0' },
                                grid: { color: 'rgba(255, 255, 255, 0.05)' }
                            }
                        }
                    }
                });
            }
        }

        function initializeModelGroupChart() {
            const ctx = document.getElementById('modelGroupChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Group A', 'Group B'],
                        datasets: [{
                            data: [156, 155],
                            backgroundColor: ['#2f5fa7', '#ff006e'],
                            borderColor: '#13172c',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                labels: { color: '#e0e0e0' }
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
