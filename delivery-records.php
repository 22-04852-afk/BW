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
    <title>Delivery Records - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: #2f5fa7;
            border-color: #2f5fa7;
            color: #fff;
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
            padding: 16px;
            text-align: left;
            font-size: 13px;
            color: #a0a0a0;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.6px;
        }

        table td {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            font-size: 14px;
        }
        
        table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge.delivered {            /* Keep table badges subtle; modal uses a stronger style */
            background: rgba(81, 207, 102, 0.2);
            color: #51cf66;
            padding: 6px 6px;
            font-weight: 700;
            border-radius: 10px;
        }
        
        .badge.in-transit {
            background: rgba(0, 217, 255, 0.2);
            color: #00d9ff;
        }
        
        .badge.pending {
            background: rgba(255, 214, 10, 0.2);
            color: #ffd60a;
        }
        
        .badge.cancelled {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
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
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .summary-label {
            font-size: 11px;
            color: #a0a0a0;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }
        
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filters {
                flex-direction: column;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            padding: 16px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            width: 90%;
            max-width: 560px;
            color: #e0e0e0;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }

        .modal-header h2 {
            color: #fff;
            margin: 0;
            font-size: 18px;
            letter-spacing: 0.3px;
        }

        .close-btn {
            background: none;
            border: none;
            color: #a0a0a0;
            font-size: 28px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #fff;
        }

        .modal-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .modal-row {
            display: flex;
            flex-direction: column;
        }

        .modal-label {
            font-size: 11px;
            color: #a0a0a0;
            text-transform: uppercase;
            margin-bottom: 6px; 
            letter-spacing: 0.5px;
            flex: 0 0 0px; /* smaller fixed column for label */
        }

        .modal-value {
            font-size: 14px;
            color: #fff;
            font-weight: 600;
        }

        .modal-row.full {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 96px 1fr;
            grid-auto-rows: auto;
            gap: 6px 12px;
        }

        /* place the badge under the left label column so status appears on the left */
        .modal-row.full .modal-label {
            grid-column: 1 / 2;
            align-self: start;
            margin-bottom: 0;
        }

        .modal-row.full .modal-badge {
            grid-column: 1 / 2;
            justify-self: start;
            align-self: center;
            display: inline-block;
            padding: 3px 6px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(90deg, #2ecc71 0%, #51cf66 100%);
            box-shadow: 0 1px 3px rgba(49, 128, 60, 0.08);
            white-space: nowrap;
            margin-top: 6px;
            width: fit-content;
            letter-spacing: 0;
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
                    <span>Andison</span>
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
                <li class="menu-item active">
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
        <div class="page-title">
            <i class="fas fa-truck"></i> Delivery Records
        </div>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total Delivered</div>
                <div class="summary-value">696</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">In Transit</div>
                <div class="summary-value">8</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Pending</div>
                <div class="summary-value">12</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Success Rate</div>
                <div class="summary-value">99.5%</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <button class="filter-btn active">All</button>
            <button class="filter-btn">Delivered</button>
            <button class="filter-btn">In Transit</button>
            <button class="filter-btn">Pending</button>
            <button class="filter-btn">Cancelled</button>
        </div>

        <!-- Delivery Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Client</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Ship Date</th>
                        <th>Est. Delivery</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#DEL-202501</td>
                        <td>Andison Zamora</td>
                        <td>Gas Detector A-100</td>
                        <td>25</td>
                        <td>Jan 28, 2025</td>
                        <td>Feb 2, 2025</td>
                        <td><span class="badge delivered">Delivered</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202501', 'Andison Zamora', 'Gas Detector A-100', '25', 'Jan 28, 2025', 'Feb 2, 2025', 'Delivered')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202502</td>
                        <td>Industrial Tech Co.</td>
                        <td>Gas Detector B-200</td>
                        <td>15</td>
                        <td>Jan 30, 2025</td>
                        <td>Feb 3, 2025</td>
                        <td><span class="badge in-transit">In Transit</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202502', 'Industrial Tech Co.', 'Gas Detector B-200', '15', 'Jan 30, 2025', 'Feb 3, 2025', 'In Transit')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202503</td>
                        <td>SafeGuard Solutions</td>
                        <td>Gas Detector A-200</td>
                        <td>30</td>
                        <td>Jan 29, 2025</td>
                        <td>Feb 1, 2025</td>
                        <td><span class="badge delivered">Delivered</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202503', 'SafeGuard Solutions', 'Gas Detector A-200', '30', 'Jan 29, 2025', 'Feb 1, 2025', 'Delivered')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202504</td>
                        <td>Summit Corp</td>
                        <td>Gas Detector B-300</td>
                        <td>20</td>
                        <td>Feb 5, 2025</td>
                        <td>Feb 10, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202504', 'Summit Corp', 'Gas Detector B-300', '20', 'Feb 5, 2025', 'Feb 10, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202505</td>
                        <td>Global Industries</td>
                        <td>Gas Detector A-500</td>
                        <td>18</td>
                        <td>Jan 31, 2025</td>
                        <td>Feb 5, 2025</td>
                        <td><span class="badge delivered">Delivered</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202505', 'Global Industries', 'Gas Detector A-500', '18', 'Jan 31, 2025', 'Feb 5, 2025', 'Delivered')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202506</td>
                        <td>Precision Labs</td>
                        <td>Gas Detector B-400</td>
                        <td>12</td>
                        <td>Feb 4, 2025</td>
                        <td>Feb 9, 2025</td>
                        <td><span class="badge in-transit">In Transit</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202506', 'Precision Labs', 'Gas Detector B-400', '12', 'Feb 4, 2025', 'Feb 9, 2025', 'In Transit')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202507</td>
                        <td>TechVision Inc</td>
                        <td>Gas Detector A-100</td>
                        <td>35</td>
                        <td>Feb 2, 2025</td>
                        <td>Feb 7, 2025</td>
                        <td><span class="badge delivered">Delivered</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202507', 'TechVision Inc', 'Gas Detector A-100', '35', 'Feb 2, 2025', 'Feb 7, 2025', 'Delivered')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202508</td>
                        <td>Quantum Systems</td>
                        <td>Gas Detector B-500</td>
                        <td>8</td>
                        <td>Feb 3, 2025</td>
                        <td>Feb 8, 2025</td>
                        <td><span class="badge delivered">Delivered</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202508', 'Quantum Systems', 'Gas Detector B-500', '8', 'Feb 3, 2025', 'Feb 8, 2025', 'Delivered')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202509</td>
                        <td>EcoTech Solutions</td>
                        <td>Gas Detector A-150</td>
                        <td>22</td>
                        <td>Feb 1, 2025</td>
                        <td>Feb 6, 2025</td>
                        <td><span class="badge in-transit">In Transit</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202509', 'EcoTech Solutions', 'Gas Detector A-150', '22', 'Feb 1, 2025', 'Feb 6, 2025', 'In Transit')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202510</td>
                        <td>Nexus Industries</td>
                        <td>Gas Detector B-250</td>
                        <td>16</td>
                        <td>Feb 2, 2025</td>
                        <td>Feb 7, 2025</td>
                        <td><span class="badge in-transit">In Transit</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202510', 'Nexus Industries', 'Gas Detector B-250', '16', 'Feb 2, 2025', 'Feb 7, 2025', 'In Transit')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202511</td>
                        <td>Alpha Manufacturing</td>
                        <td>Gas Detector A-300</td>
                        <td>28</td>
                        <td>Feb 4, 2025</td>
                        <td>Feb 9, 2025</td>
                        <td><span class="badge in-transit">In Transit</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202511', 'Alpha Manufacturing', 'Gas Detector A-300', '28', 'Feb 4, 2025', 'Feb 9, 2025', 'In Transit')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202512</td>
                        <td>Delta Enterprises</td>
                        <td>Gas Detector B-350</td>
                        <td>19</td>
                        <td>Feb 5, 2025</td>
                        <td>Feb 10, 2025</td>
                        <td><span class="badge in-transit">In Transit</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202512', 'Delta Enterprises', 'Gas Detector B-350', '19', 'Feb 5, 2025', 'Feb 10, 2025', 'In Transit')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202513</td>
                        <td>Gamma Corp</td>
                        <td>Gas Detector A-400</td>
                        <td>31</td>
                        <td>Feb 6, 2025</td>
                        <td>Feb 11, 2025</td>
                        <td><span class="badge in-transit">In Transit</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202513', 'Gamma Corp', 'Gas Detector A-400', '31', 'Feb 6, 2025', 'Feb 11, 2025', 'In Transit')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202514</td>
                        <td>Omega Tech</td>
                        <td>Gas Detector B-450</td>
                        <td>23</td>
                        <td>Feb 7, 2025</td>
                        <td>Feb 12, 2025</td>
                        <td><span class="badge in-transit">In Transit</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202514', 'Omega Tech', 'Gas Detector B-450', '23', 'Feb 7, 2025', 'Feb 12, 2025', 'In Transit')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202515</td>
                        <td>Zenith Systems</td>
                        <td>Gas Detector A-150</td>
                        <td>14</td>
                        <td>Feb 8, 2025</td>
                        <td>Feb 13, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202515', 'Zenith Systems', 'Gas Detector A-150', '14', 'Feb 8, 2025', 'Feb 13, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202516</td>
                        <td>Vertex Solutions</td>
                        <td>Gas Detector B-300</td>
                        <td>26</td>
                        <td>Feb 9, 2025</td>
                        <td>Feb 14, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202516', 'Vertex Solutions', 'Gas Detector B-300', '26', 'Feb 9, 2025', 'Feb 14, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202517</td>
                        <td>Horizon Group</td>
                        <td>Gas Detector A-250</td>
                        <td>17</td>
                        <td>Feb 10, 2025</td>
                        <td>Feb 15, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202517', 'Horizon Group', 'Gas Detector A-250', '17', 'Feb 10, 2025', 'Feb 15, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202518</td>
                        <td>Eclipse Industries</td>
                        <td>Gas Detector B-400</td>
                        <td>29</td>
                        <td>Feb 11, 2025</td>
                        <td>Feb 16, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202518', 'Eclipse Industries', 'Gas Detector B-400', '29', 'Feb 11, 2025', 'Feb 16, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202519</td>
                        <td>Comet Logistics</td>
                        <td>Gas Detector A-500</td>
                        <td>11</td>
                        <td>Feb 6, 2025</td>
                        <td>Feb 11, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202519', 'Comet Logistics', 'Gas Detector A-500', '11', 'Feb 6, 2025', 'Feb 11, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202520</td>
                        <td>Phoenix Corp</td>
                        <td>Gas Detector B-150</td>
                        <td>24</td>
                        <td>Feb 7, 2025</td>
                        <td>Feb 12, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202520', 'Phoenix Corp', 'Gas Detector B-150', '24', 'Feb 7, 2025', 'Feb 12, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202521</td>
                        <td>Aurora Enterprises</td>
                        <td>Gas Detector A-350</td>
                        <td>32</td>
                        <td>Feb 8, 2025</td>
                        <td>Feb 13, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202521', 'Aurora Enterprises', 'Gas Detector A-350', '32', 'Feb 8, 2025', 'Feb 13, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202522</td>
                        <td>Stellar Manufacturing</td>
                        <td>Gas Detector B-500</td>
                        <td>13</td>
                        <td>Feb 9, 2025</td>
                        <td>Feb 14, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202522', 'Stellar Manufacturing', 'Gas Detector B-500', '13', 'Feb 9, 2025', 'Feb 14, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202523</td>
                        <td>Luminous Tech</td>
                        <td>Gas Detector A-200</td>
                        <td>27</td>
                        <td>Feb 10, 2025</td>
                        <td>Feb 15, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202523', 'Luminous Tech', 'Gas Detector A-200', '27', 'Feb 10, 2025', 'Feb 15, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202524</td>
                        <td>Radiant Systems</td>
                        <td>Gas Detector B-600</td>
                        <td>9</td>
                        <td>Feb 11, 2025</td>
                        <td>Feb 16, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202524', 'Radiant Systems', 'Gas Detector B-600', '9', 'Feb 11, 2025', 'Feb 16, 2025', 'Pending')">View</a></td>
                    </tr>
                    <tr>
                        <td>#DEL-202525</td>
                        <td>Brilliant Industries</td>
                        <td>Gas Detector A-450</td>
                        <td>33</td>
                        <td>Feb 12, 2025</td>
                        <td>Feb 17, 2025</td>
                        <td><span class="badge pending">Pending</span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '#DEL-202525', 'Brilliant Industries', 'Gas Detector A-450', '33', 'Feb 12, 2025', 'Feb 17, 2025', 'Pending')">View</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTrackingId">Delivery Details</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-row">
                    <span class="modal-label">Tracking Number</span>
                    <span class="modal-value" id="modalTracking">#DEL-000000</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Client Name</span>
                    <span class="modal-value" id="modalClient">-</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Product</span>
                    <span class="modal-value" id="modalProduct">-</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Quantity</span>
                    <span class="modal-value" id="modalQuantity">0</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Ship Date</span>
                    <span class="modal-value" id="modalShipDate">-</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Est. Delivery</span>
                    <span class="modal-value" id="modalDeliveryDate">-</span>
                </div>
                <div class="modal-row full">
                    <span class="modal-label">Status</span>
                    <span class="modal-badge" id="modalStatusBadge"></span>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script>
        function getStatusBadgeClass(status) {
            status = status.toLowerCase();
            if (status.includes('delivered')) return 'badge delivered';
            if (status.includes('transit')) return 'badge in-transit';
            if (status.includes('pending')) return 'badge pending';
            if (status.includes('cancelled')) return 'badge cancelled';
            return 'badge';
        }

        function openModal(event, tracking, client, product, quantity, shipDate, deliveryDate, status) {
            event.preventDefault();
            document.getElementById('modalTracking').textContent = tracking;
            document.getElementById('modalTrackingId').textContent = `${tracking} Details`;
            document.getElementById('modalClient').textContent = client;
            document.getElementById('modalProduct').textContent = product;
            document.getElementById('modalQuantity').textContent = quantity;
            document.getElementById('modalShipDate').textContent = shipDate;
            document.getElementById('modalDeliveryDate').textContent = deliveryDate;
            
            const badgeEl = document.getElementById('modalStatusBadge');
            badgeEl.className = getStatusBadgeClass(status);
            badgeEl.textContent = status;
            
            document.getElementById('detailModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('show');
        }

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('detailModal');
            if (e.target === modal) {
                closeModal();
            }
        });

        // Filter functionality
        const filterBtns = document.querySelectorAll('.filter-btn');
        const tableRows = document.querySelectorAll('table tbody tr');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                filterBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');

                const filterValue = this.textContent.trim().toLowerCase();

                // Show/hide rows based on filter
                tableRows.forEach(row => {
                    const badge = row.querySelector('.badge');
                    if (badge) {
                        const badgeText = badge.textContent.trim().toLowerCase();
                        if (filterValue === 'all' || badgeText === filterValue || badgeText.includes(filterValue)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });

        // Sidebar toggle
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('sidebar-closed');
            });
        }
    </script>
</body>
</html>
