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
    <title>Models - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .models-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .model-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .model-card:hover {
            transform: translateY(-5px);
            border-color: #2f5fa7;
            box-shadow: 0 10px 30px rgba(47, 95, 167, 0.2);
        }
        
        .model-header {
            background: linear-gradient(135deg, #2f5fa7, #00d9ff);
            padding: 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .model-icon {
            font-size: 32px;
        }
        
        .model-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .model-content {
            padding: 20px;
        }
        
        .model-name {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .model-description {
            font-size: 13px;
            color: #a0a0a0;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .model-specs {
            display: grid;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .spec-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }
        
        .spec-label {
            color: #a0a0a0;
        }
        
        .spec-value {
            color: #f4d03f;
            font-weight: 600;
        }
        
        .model-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .stat {
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: 700;
            color: #00d9ff;
        }
        
        .stat-label {
            font-size: 10px;
            color: #a0a0a0;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .group-section {
            margin-bottom: 40px;
        }
        
        .group-title {
            font-size: 22px;
            font-weight: 700;
            color: #121212;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f4d03f;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .group-icon {
            font-size: 24px;
            color: #f4d03f;
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
            .models-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
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
                <li class="menu-item has-submenu active">
                    <a href="models.php" class="menu-link submenu-toggle" data-submenu="models-submenu">
                        <i class="fas fa-cube"></i>
                        <span class="menu-label">Models</span>
                        <i class="fas fa-chevron-right submenu-icon"></i>
                    </a>
                    <ul class="submenu active" id="models-submenu">
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
        <div class="page-title">
            <i class="fas fa-cube"></i> Product Models
        </div>

        <!-- Group A Models -->
        <div class="group-section">
            <div class="group-title">
                <span class="group-icon">A</span>
                Group A - Standard Series
            </div>
            <div class="models-grid">
                <div class="model-card">
                    <div class="model-header">
                        <div class="model-icon">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <div class="model-badge">Standard</div>
                    </div>
                    <div class="model-content">
                        <div class="model-name">A-100</div>
                        <div class="model-description">Entry-level gas detector with basic monitoring capabilities</div>
                        <div class="model-specs">
                            <div class="spec-item">
                                <span class="spec-label">Range</span>
                                <span class="spec-value">0-50 ppm</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Response Time</span>
                                <span class="spec-value">&lt;10 seconds</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Battery Life</span>
                                <span class="spec-value">200 hours</span>
                            </div>
                        </div>
                        <div class="model-stats">
                            <div class="stat">
                                <div class="stat-value">145</div>
                                <div class="stat-label">Sold</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">$320</div>
                                <div class="stat-label">Price</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="model-card">
                    <div class="model-header">
                        <div class="model-icon">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <div class="model-badge">Standard</div>
                    </div>
                    <div class="model-content">
                        <div class="model-name">A-200</div>
                        <div class="model-description">Mid-range detector with enhanced sensitivity and memory</div>
                        <div class="model-specs">
                            <div class="spec-item">
                                <span class="spec-label">Range</span>
                                <span class="spec-value">0-100 ppm</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Response Time</span>
                                <span class="spec-value">&lt;8 seconds</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Battery Life</span>
                                <span class="spec-value">300 hours</span>
                            </div>
                        </div>
                        <div class="model-stats">
                            <div class="stat">
                                <div class="stat-value">158</div>
                                <div class="stat-label">Sold</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">$450</div>
                                <div class="stat-label">Price</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="model-card">
                    <div class="model-header">
                        <div class="model-icon">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <div class="model-badge">Premium</div>
                    </div>
                    <div class="model-content">
                        <div class="model-name">A-500</div>
                        <div class="model-description">Professional-grade detector with dual sensor technology</div>
                        <div class="model-specs">
                            <div class="spec-item">
                                <span class="spec-label">Range</span>
                                <span class="spec-value">0-200 ppm</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Response Time</span>
                                <span class="spec-value">&lt;5 seconds</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Battery Life</span>
                                <span class="spec-value">500 hours</span>
                            </div>
                        </div>
                        <div class="model-stats">
                            <div class="stat">
                                <div class="stat-value">98</div>
                                <div class="stat-label">Sold</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">$780</div>
                                <div class="stat-label">Price</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Group B Models -->
        <div class="group-section">
            <div class="group-title">
                <span class="group-icon">B</span>
                Group B - Advanced Series
            </div>
            <div class="models-grid">
                <div class="model-card">
                    <div class="model-header">
                        <div class="model-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="model-badge">Advanced</div>
                    </div>
                    <div class="model-content">
                        <div class="model-name">B-100</div>
                        <div class="model-description">Smart detector with IoT connectivity and cloud sync</div>
                        <div class="model-specs">
                            <div class="spec-item">
                                <span class="spec-label">Range</span>
                                <span class="spec-value">0-100 ppm</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Connectivity</span>
                                <span class="spec-value">WiFi/4G</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Battery Life</span>
                                <span class="spec-value">400 hours</span>
                            </div>
                        </div>
                        <div class="model-stats">
                            <div class="stat">
                                <div class="stat-value">132</div>
                                <div class="stat-label">Sold</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">$650</div>
                                <div class="stat-label">Price</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="model-card">
                    <div class="model-header">
                        <div class="model-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="model-badge">Advanced</div>
                    </div>
                    <div class="model-content">
                        <div class="model-name">B-200</div>
                        <div class="model-description">Multi-sensor system with real-time analytics dashboard</div>
                        <div class="model-specs">
                            <div class="spec-item">
                                <span class="spec-label">Range</span>
                                <span class="spec-value">0-200 ppm</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Connectivity</span>
                                <span class="spec-value">5G Ready</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Battery Life</span>
                                <span class="spec-value">600 hours</span>
                            </div>
                        </div>
                        <div class="model-stats">
                            <div class="stat">
                                <div class="stat-value">156</div>
                                <div class="stat-label">Sold</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">$890</div>
                                <div class="stat-label">Price</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="model-card">
                    <div class="model-header">
                        <div class="model-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="model-badge">Premium</div>
                    </div>
                    <div class="model-content">
                        <div class="model-name">B-500</div>
                        <div class="model-description">Enterprise-level detector with predictive AI analytics</div>
                        <div class="model-specs">
                            <div class="spec-item">
                                <span class="spec-label">Range</span>
                                <span class="spec-value">0-500 ppm</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Connectivity</span>
                                <span class="spec-value">5G + Satellite</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Battery Life</span>
                                <span class="spec-value">800 hours</span>
                            </div>
                        </div>
                        <div class="model-stats">
                            <div class="stat">
                                <div class="stat-value">121</div>
                                <div class="stat-label">Sold</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value">$1,250</div>
                                <div class="stat-label">Price</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js" defer></script>
</body>
</html>
