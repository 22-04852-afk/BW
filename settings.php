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
    <title>Settings - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .settings-section {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .settings-title {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .settings-title i {
            color: #f4d03f;
        }
        
        .settings-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .settings-item:last-child {
            border-bottom: none;
        }
        
        .settings-label {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .settings-label-text {
            font-size: 14px;
            font-weight: 600;
            color: #e0e0e0;
        }
        
        .settings-label-desc {
            font-size: 12px;
            color: #a0a0a0;
        }
        
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 28px;
            background: #d8dadc;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            user-select: none;
            flex-shrink: 0;
        }

        .toggle-switch:hover {
            opacity: 0.9;
        }
        
        .toggle-switch.active {
            background: linear-gradient(135deg, #5bbcff, #2f5fa7);
            border-color: #5bbcff;
        }
        
        .toggle-slider {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .toggle-switch.active .toggle-slider {
            left: 24px;
        }
        
        .select-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 14px;
            color: #e0e0e0;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            cursor: pointer;
        }
        
        .select-input:focus {
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
        
        .btn-save {
            background: linear-gradient(135deg, #f4d03f, #ffd60a);
            color: #1e2a38;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(244, 208, 63, 0.3);
        }
        
        .btn-danger {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
        }
        
        .btn-danger:hover {
            background: rgba(255, 107, 107, 0.2);
        }
        
        .settings-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }
        
        @media (max-width: 768px) {
            .settings-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
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
                <li class="menu-item active">
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
            <i class="fas fa-cog"></i> Settings
        </div>

        <div class="settings-container">
            <!-- Display Settings -->
            <div class="settings-section">
                <div class="settings-title">
                    <i class="fas fa-palette"></i> Display & Appearance
                </div>
                
                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Dark Theme</div>
                        <div class="settings-label-desc">Use dark mode interface</div>
                    </div>
                    <div class="toggle-switch active" id="darkModeToggle">
                        <div class="toggle-slider"></div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="settings-section">
                <div class="settings-title">
                    <i class="fas fa-bell"></i> Notifications
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Email Notifications</div>
                        <div class="settings-label-desc">Receive email alerts for important events</div>
                    </div>
                    <div class="toggle-switch active" onclick="toggleSetting(this)">
                        <div class="toggle-slider"></div>
                    </div>
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Delivery Updates</div>
                        <div class="settings-label-desc">Notifications when shipments arrive</div>
                    </div>
                    <div class="toggle-switch active" onclick="toggleSetting(this)">
                        <div class="toggle-slider"></div>
                    </div>
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Sales Alerts</div>
                        <div class="settings-label-desc">Alerts for significant sales milestones</div>
                    </div>
                    <div class="toggle-switch" onclick="toggleSetting(this)">
                        <div class="toggle-slider"></div>
                    </div>
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Report Frequency</div>
                        <div class="settings-label-desc">How often to receive summary reports</div>
                    </div>
                    <select class="select-input">
                        <option>Daily</option>
                        <option selected>Weekly</option>
                        <option>Monthly</option>
                        <option>Never</option>
                    </select>
                </div>
            </div>

            <!-- Data & Privacy Settings -->
            <div class="settings-section">
                <div class="settings-title">
                    <i class="fas fa-lock"></i> Data & Privacy
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Data Analytics</div>
                        <div class="settings-label-desc">Allow anonymous usage analytics</div>
                    </div>
                    <div class="toggle-switch active" onclick="toggleSetting(this)">
                        <div class="toggle-slider"></div>
                    </div>
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Session Timeout</div>
                        <div class="settings-label-desc">Auto-logout after inactivity (minutes)</div>
                    </div>
                    <select class="select-input">
                        <option>5 minutes</option>
                        <option selected>15 minutes</option>
                        <option>30 minutes</option>
                        <option>1 hour</option>
                        <option>Never</option>
                    </select>
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Two-Factor Authentication</div>
                        <div class="settings-label-desc">Enhanced security for account login</div>
                    </div>
                    <div class="toggle-switch" onclick="toggleSetting(this)">
                        <div class="toggle-slider"></div>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div class="settings-section">
                <div class="settings-title">
                    <i class="fas fa-sliders-h"></i> System
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Version</div>
                        <div class="settings-label-desc">Current application version</div>
                    </div>
                    <div style="color: #f4d03f; font-weight: 600;">v2.1.5</div>
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Cache</div>
                        <div class="settings-label-desc">Clear cached data to improve performance</div>
                    </div>
                    <button class="btn-save" style="background: rgba(255, 180, 0, 0.2); color: #ffd60a; border: 1px solid #ffd60a;">Clear Cache</button>
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Database Backup</div>
                        <div class="settings-label-desc">Last backup: Jan 25, 2025</div>
                    </div>
                    <button class="btn-save">Backup Now</button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="settings-section">
                <div class="settings-title" style="color: #ff6b6b;">
                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Reset All Settings</div>
                        <div class="settings-label-desc">Reset all settings to factory defaults</div>
                    </div>
                    <button class="btn-save btn-danger">Reset Settings</button>
                </div>

                <div class="settings-item">
                    <div class="settings-label">
                        <div class="settings-label-text">Delete Account</div>
                        <div class="settings-label-desc">Permanently delete your account and all data</div>
                    </div>
                    <button class="btn-save btn-danger">Delete Account</button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="settings-actions">
                <button class="btn-save">Save Changes</button>
                <button class="btn-save" style="background: rgba(255, 255, 255, 0.1); color: #e0e0e0; border: 1px solid rgba(255, 255, 255, 0.2);">Cancel</button>
            </div>
        </div>
    </div>

    <script src="js/app.js" defer></script>
    <script>
        function toggleSetting(element) {
            element.classList.toggle('active');
        }

        // Initialize dark mode toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('darkModeToggle');
            if (!toggle) return;
            
            // Set initial state from localStorage
            const isLight = localStorage.getItem('theme') === 'light';
            if (isLight) {
                toggle.classList.remove('active');
                document.documentElement.classList.add('light-mode');
                document.body.classList.add('light-mode');
            } else {
                toggle.classList.add('active');
                document.documentElement.classList.remove('light-mode');
                document.body.classList.remove('light-mode');
            }
            
            // Add click handler
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.toggle('active');
                
                // Toggle is for "Dark Theme" - active means dark mode ON
                if (this.classList.contains('active')) {
                    // Dark mode ON
                    document.documentElement.classList.remove('light-mode');
                    document.body.classList.remove('light-mode');
                    localStorage.setItem('theme', 'dark');
                    console.log('Theme switched to: Dark Mode');
                } else {
                    // Dark mode OFF = Light mode
                    document.documentElement.classList.add('light-mode');
                    document.body.classList.add('light-mode');
                    localStorage.setItem('theme', 'light');
                    console.log('Theme switched to: Light Mode');
                }
            });
        });
    </script>
</body>
</html>
