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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Companies - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .companies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .company-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .company-card:hover {
            transform: translateY(-5px);
            border-color: #2f5fa7;
            box-shadow: 0 10px 30px rgba(47, 95, 167, 0.2);
        }
        
        .company-logo {
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
        
        .company-name {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }
        
        .company-industry {
            font-size: 12px;
            color: #a0a0a0;
            margin-bottom: 15px;
        }
        
        .company-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #e0e0e0;
        }
        
        .info-item i {
            color: #f4d03f;
            width: 16px;
        }
        
        .company-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 14px;
            font-weight: 700;
            color: #f4d03f;
        }
        
        .stat-label {
            font-size: 10px;
            color: #a0a0a0;
            text-transform: uppercase;
        }
        
        .search-container {
            margin-bottom: 25px;
            display: flex;
            gap: 12px;
        }
        
        .search-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 12px 16px;
            color: #e0e0e0;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }
        
        .search-input::placeholder {
            color: #a0a0a0;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #2f5fa7;
            background: rgba(255, 255, 255, 0.08);
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
        
        .view-more-btn {
            background: linear-gradient(135deg, #2f5fa7, #1e3c72);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            margin-top: 15px;
        }
        
        .view-more-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 95, 167, 0.3);
        }
        
        @media (max-width: 768px) {
            .companies-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .search-container {
                flex-direction: column;
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
                <li class="menu-item active">
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
        <div class="page-title">
            <i class="fas fa-building"></i> Client Companies
        </div>

        <div class="search-container">
            <input type="text" class="search-input" placeholder="Search companies by name...">
        </div>

        <div class="companies-grid">
            <div class="company-card">
                <div class="company-logo">
                    <i class="fas fa-building"></i>
                </div>
                <div class="company-name">Andison Zamora</div>
                <div class="company-industry">Manufacturing Sector</div>
                <div class="company-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>contact@andison.com</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>(555) 123-4567</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>New York, NY</span>
                    </div>
                </div>
                <div class="company-stats">
                    <div class="stat">
                        <div class="stat-value">97</div>
                        <div class="stat-label">Units Sold</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">$52.5K</div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>
                <button class="view-more-btn">View Profile</button>
            </div>

            <div class="company-card">
                <div class="company-logo">
                    <i class="fas fa-industry"></i>
                </div>
                <div class="company-name">Industrial Tech Co.</div>
                <div class="company-industry">Technology & Safety</div>
                <div class="company-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>sales@techtech.com</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>(555) 234-5678</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Los Angeles, CA</span>
                    </div>
                </div>
                <div class="company-stats">
                    <div class="stat">
                        <div class="stat-value">64</div>
                        <div class="stat-label">Units Sold</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">$34.6K</div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>
                <button class="view-more-btn">View Profile</button>
            </div>

            <div class="company-card">
                <div class="company-logo">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="company-name">SafeGuard Solutions</div>
                <div class="company-industry">Safety Systems</div>
                <div class="company-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>info@safeguard.com</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>(555) 345-6789</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Chicago, IL</span>
                    </div>
                </div>
                <div class="company-stats">
                    <div class="stat">
                        <div class="stat-value">45</div>
                        <div class="stat-label">Units Sold</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">$24.3K</div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>
                <button class="view-more-btn">View Profile</button>
            </div>

            <div class="company-card">
                <div class="company-logo">
                    <i class="fas fa-mountain"></i>
                </div>
                <div class="company-name">Summit Corp</div>
                <div class="company-industry">Energy Sector</div>
                <div class="company-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>business@summit.com</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>(555) 456-7890</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Houston, TX</span>
                    </div>
                </div>
                <div class="company-stats">
                    <div class="stat">
                        <div class="stat-value">31</div>
                        <div class="stat-label">Units Sold</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">$16.8K</div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>
                <button class="view-more-btn">View Profile</button>
            </div>

            <div class="company-card">
                <div class="company-logo">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="company-name">Global Industries</div>
                <div class="company-industry">Distribution</div>
                <div class="company-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>orders@global.com</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>(555) 567-8901</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Miami, FL</span>
                    </div>
                </div>
                <div class="company-stats">
                    <div class="stat">
                        <div class="stat-value">28</div>
                        <div class="stat-label">Units Sold</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">$15.1K</div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>
                <button class="view-more-btn">View Profile</button>
            </div>

            <div class="company-card">
                <div class="company-logo">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="company-name">Precision Labs</div>
                <div class="company-industry">Research & Testing</div>
                <div class="company-info">
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span>lab@precision.com</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>(555) 678-9012</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Boston, MA</span>
                    </div>
                </div>
                <div class="company-stats">
                    <div class="stat">
                        <div class="stat-value">22</div>
                        <div class="stat-label">Units Sold</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">$11.9K</div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>
                <button class="view-more-btn">View Profile</button>
            </div>
        </div>
    </div>

    <script src="js/app.js" defer></script>
</body>
</html>
