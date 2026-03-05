<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

// Include database configuration
require_once 'db_config.php';

// Get statistics from database
$stats = [
    'total_delivered' => 0,
    'in_transit' => 0,
    'pending' => 0,
    'total_records' => 0,
    'total_quantity' => 0
];

// Count by status
$result = $conn->query("SELECT status, COUNT(*) as count, COALESCE(SUM(quantity), 0) as qty FROM delivery_records GROUP BY status");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status']);
        if (strpos($status, 'deliver') !== false) {
            $stats['total_delivered'] += intval($row['count']);
            $stats['total_quantity'] += intval($row['qty']);
        } elseif (strpos($status, 'transit') !== false) {
            $stats['in_transit'] += intval($row['count']);
        } elseif (strpos($status, 'pending') !== false) {
            $stats['pending'] += intval($row['count']);
        }
        $stats['total_records'] += intval($row['count']);
    }
}

// Calculate success rate
$success_rate = $stats['total_records'] > 0 ? round(($stats['total_delivered'] / $stats['total_records']) * 100, 1) : 0;

// Get all delivery records
$delivery_records = [];
$result = $conn->query("SELECT * FROM delivery_records ORDER BY id DESC LIMIT 500");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $delivery_records[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')==='light'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Records - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        /* Search Bar Styles */
        .search-container {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #2f5fa7;
            background: rgba(255, 255, 255, 0.08);
        }
        
        .search-box input::placeholder {
            color: #7a8a9a;
        }
        
        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7a8a9a;
        }
        
        .search-count {
            color: #a0a0a0;
            font-size: 13px;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
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
            padding: 30px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            width: 90%;
            max-width: 560px;
            color: #e0e0e0;
        }

        /* Larger modal for Add Record */
        .modal-content.modal-large {
            max-width: 950px;
            padding: 45px 50px;
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

        /* Add Record Button */
        .btn-add-record {
            background: linear-gradient(135deg, #2f5fa7 0%, #1e88e5 100%);
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-add-record:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(47, 95, 167, 0.3);
        }

        /* Form styles */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 14px;
            color: #a0a0a0;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 18px 20px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2f5fa7;
            background: rgba(255, 255, 255, 0.12);
        }

        .form-group select option {
            background: #1e2a38;
            color: #fff;
            padding: 12px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            gap: 20px;
            margin-top: 35px;
            justify-content: flex-end;
        }

        .btn-submit {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: #fff;
            border: none;
            padding: 18px 40px;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.35);
        }

        .btn-cancel-form {
            background: rgba(255, 255, 255, 0.1);
            color: #a0a0a0;
            border: none;
            padding: 18px 40px;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel-form:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        /* Action Buttons in Table */
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 20px;
            justify-content: center;
        }

        .action-buttons .view-btn {
            color: #f4d03f;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 6px;
            background: rgba(244, 208, 63, 0.1);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .action-buttons .view-btn:hover {
            color: #fff;
            background: rgba(244, 208, 63, 0.25);
            text-decoration: none;
        }

        .action-buttons .delete-btn {
            color: #e74c3c;
            text-decoration: none;
            font-size: 13px;
            padding: 8px 14px;
            border-radius: 6px;
            transition: all 0.2s ease;
            background: rgba(231, 76, 60, 0.1);
            white-space: nowrap;
        }

        .action-buttons .delete-btn:hover {
            background: rgba(231, 76, 60, 0.25);
            color: #ff6b5b;
        }

        /* Delete Confirmation Modal */
        .delete-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .delete-modal.show {
            display: flex;
            opacity: 1;
        }

        .delete-modal-content {
            background: linear-gradient(145deg, #1e2a38, #16202c);
            border-radius: 16px;
            padding: 35px 40px;
            max-width: 450px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .delete-modal-icon {
            font-size: 60px;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .delete-modal-title {
            font-size: 22px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 12px;
        }

        .delete-modal-message {
            font-size: 15px;
            color: #a0a0a0;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .delete-modal-message strong {
            color: #f4d03f;
        }

        .delete-modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .delete-modal-btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-confirm-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: #fff;
        }

        .btn-confirm-delete:hover {
            background: linear-gradient(135deg, #ff6b5b, #e74c3c);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .btn-cancel-delete {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-cancel-delete:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        /* Light mode styles for action buttons */
        [data-theme="light"] .action-buttons .view-btn {
            color: #1e88e5;
            background: rgba(30, 136, 229, 0.08);
        }

        [data-theme="light"] .action-buttons .view-btn:hover {
            color: #fff;
            background: #1e88e5;
        }

        [data-theme="light"] .action-buttons .delete-btn {
            color: #c0392b;
            background: rgba(231, 76, 60, 0.08);
        }

        [data-theme="light"] .action-buttons .delete-btn:hover {
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
        }

        [data-theme="light"] .delete-modal-content {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        [data-theme="light"] .delete-modal-title {
            color: #1a3a5c;
        }

        [data-theme="light"] .delete-modal-message {
            color: #5a6a7a;
        }

        [data-theme="light"] .delete-modal-message strong {
            color: #1e88e5;
        }

        [data-theme="light"] .btn-cancel-delete {
            background: #e8f4fc;
            color: #1a3a5c;
            border: 1px solid #c5ddf0;
        }

        [data-theme="light"] .btn-cancel-delete:hover {
            background: #d0e7f7;
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
                <div class="summary-label">Total Records</div>
                <div class="summary-value"><?php echo number_format($stats['total_records']); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Delivered</div>
                <div class="summary-value"><?php echo number_format($stats['total_delivered']); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">In Transit</div>
                <div class="summary-value"><?php echo number_format($stats['in_transit']); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Pending</div>
                <div class="summary-value"><?php echo number_format($stats['pending']); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Quantity</div>
                <div class="summary-value"><?php echo number_format($stats['total_quantity']); ?></div>
            </div>
        </div>

        <p style="margin-bottom: 15px; color: #a0a0a0; font-size: 13px;">
            Showing <?php echo count($delivery_records); ?> of <?php echo number_format($stats['total_records']); ?> records (latest first)
        </p>

        <!-- Search Bar -->
        <div class="search-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by serial no, invoice, item, company..." onkeyup="searchTable()">
                <i class="fas fa-search"></i>
            </div>
            <div class="search-count" id="searchCount">Showing all records</div>
            <button class="btn-add-record" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add Record
            </button>
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
                        <th>Serial No.</th>
                        <th>Invoice No.</th>
                        <th>Item Code</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Date Delivered</th>
                        <th>Status</th>
                        <th style="min-width: 160px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($delivery_records)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #a0a0a0;">
                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            No delivery records found. <a href="upload-data.php" style="color: #f4d03f;">Upload data</a> to get started.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($delivery_records as $record): 
                        $status_class = 'delivered';
                        $status_lower = strtolower($record['status'] ?? 'delivered');
                        if (strpos($status_lower, 'transit') !== false) $status_class = 'in-transit';
                        elseif (strpos($status_lower, 'pending') !== false) $status_class = 'pending';
                        elseif (strpos($status_lower, 'cancel') !== false) $status_class = 'cancelled';
                        
                        $delivery_date = '';
                        if (!empty($record['delivery_date'])) {
                            $delivery_date = date('M j, Y', strtotime($record['delivery_date']));
                        } elseif (!empty($record['delivery_month']) && !empty($record['delivery_day'])) {
                            $delivery_date = $record['delivery_month'] . ' ' . $record['delivery_day'];
                        }
                    ?>
                    <tr data-record-id="<?php echo htmlspecialchars($record['id'] ?? ''); ?>">
                        <td><?php echo htmlspecialchars($record['serial_no'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['invoice_no'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['item_code'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['item_name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['quantity'] ?? '0'); ?></td>
                        <td><?php echo htmlspecialchars($delivery_date); ?></td>
                        <td><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($record['status'] ?? 'Delivered'); ?></span></td>
                        <td class="action-buttons">
                            <a href="#" class="view-btn" onclick="openModal(event, '<?php echo htmlspecialchars($record['serial_no'] ?? ''); ?>', '<?php echo htmlspecialchars($record['invoice_no'] ?? ''); ?>', '<?php echo htmlspecialchars($record['item_code'] ?? ''); ?>', '<?php echo htmlspecialchars($record['item_name'] ?? ''); ?>', '<?php echo htmlspecialchars($record['quantity'] ?? '0'); ?>', '<?php echo htmlspecialchars($delivery_date); ?>', '<?php echo htmlspecialchars($record['status'] ?? 'Delivered'); ?>')">View</a>
                            <a href="#" class="delete-btn" onclick="deleteRecord(event, <?php echo intval($record['id'] ?? 0); ?>, '<?php echo htmlspecialchars($record['item_code'] ?? ''); ?>')" title="Delete Record"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add Record Modal -->
    <div id="addRecordModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header" style="margin-bottom: 30px; padding-bottom: 20px;">
                <h2 style="font-size: 26px;"><i class="fas fa-plus-circle" style="color: #2ecc71; margin-right: 14px; font-size: 28px;"></i>Add New Delivery Record</h2>
                <button class="close-btn" onclick="closeAddModal()" style="font-size: 36px;">&times;</button>
            </div>
            <form id="addRecordForm" onsubmit="submitAddRecord(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="add_serial_no">Serial No.</label>
                        <input type="text" id="add_serial_no" name="serial_no" placeholder="e.g., MA225-000613">
                    </div>
                    <div class="form-group">
                        <label for="add_invoice_no">Invoice No.</label>
                        <input type="text" id="add_invoice_no" name="invoice_no" placeholder="e.g., 5268850284">
                    </div>
                    <div class="form-group">
                        <label for="add_item_code">Item Code *</label>
                        <input type="text" id="add_item_code" name="item_code" placeholder="e.g., XT-XWHM-Y-NA" required>
                    </div>
                    <div class="form-group">
                        <label for="add_item_name">Description / Item Name</label>
                        <input type="text" id="add_item_name" name="item_name" placeholder="e.g., GasAlertMax XT O2/LEL/H2S/CO">
                    </div>
                    <div class="form-group">
                        <label for="add_company_name">Company / Sold To</label>
                        <input type="text" id="add_company_name" name="company_name" placeholder="e.g., Andison Industrial">
                    </div>
                    <div class="form-group">
                        <label for="add_quantity">Quantity *</label>
                        <input type="number" id="add_quantity" name="quantity" placeholder="e.g., 40" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="add_delivery_date">Delivery Date</label>
                        <input type="date" id="add_delivery_date" name="delivery_date">
                    </div>
                    <div class="form-group">
                        <label for="add_status">Status</label>
                        <select id="add_status" name="status">
                            <option value="Delivered">Delivered</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Pending">Pending</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="add_notes">Notes / Remarks</label>
                        <textarea id="add_notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel-form" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Record</button>
                </div>
            </form>
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
                    <span class="modal-label">Serial No.</span>
                    <span class="modal-value" id="modalTracking">-</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Invoice No.</span>
                    <span class="modal-value" id="modalClient">-</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Item Code</span>
                    <span class="modal-value" id="modalProduct">-</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Description</span>
                    <span class="modal-value" id="modalQuantity">-</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Quantity</span>
                    <span class="modal-value" id="modalShipDate">0</span>
                </div>
                <div class="modal-row">
                    <span class="modal-label">Date Delivered</span>
                    <span class="modal-value" id="modalDeliveryDate">-</span>
                </div>
                <div class="modal-row full">
                    <span class="modal-label">Status</span>
                    <span class="modal-badge" id="modalStatusBadge"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="delete-modal">
        <div class="delete-modal-content">
            <div class="delete-modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="delete-modal-title">Delete Record?</h3>
            <p class="delete-modal-message">
                Are you sure you want to delete <strong id="deleteItemName">this record</strong>?<br>
                This action cannot be undone.
            </p>
            <div class="delete-modal-actions">
                <button type="button" class="delete-modal-btn btn-cancel-delete" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="delete-modal-btn btn-confirm-delete" id="confirmDeleteBtn" onclick="confirmDelete()">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <script src="js/app.js" defer></script>
    <script>
        function getStatusBadgeClass(status) {
            status = status.toLowerCase();
            if (status.includes('delivered')) return 'badge delivered';
            if (status.includes('transit')) return 'badge in-transit';
            if (status.includes('pending')) return 'badge pending';
            if (status.includes('cancelled')) return 'badge cancelled';
            return 'badge';
        }

        function openModal(event, serialNo, invoiceNo, itemCode, description, quantity, deliveryDate, status) {
            event.preventDefault();
            document.getElementById('modalTracking').textContent = serialNo || '-';
            document.getElementById('modalTrackingId').textContent = serialNo ? `${serialNo} Details` : 'Delivery Details';
            document.getElementById('modalClient').textContent = invoiceNo || '-';
            document.getElementById('modalProduct').textContent = itemCode || '-';
            document.getElementById('modalQuantity').textContent = description || '-';
            document.getElementById('modalShipDate').textContent = quantity || '0';
            document.getElementById('modalDeliveryDate').textContent = deliveryDate || '-';
            
            const badgeEl = document.getElementById('modalStatusBadge');
            badgeEl.className = getStatusBadgeClass(status);
            badgeEl.textContent = status || 'Delivered';
            
            document.getElementById('detailModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('show');
        }

        // Delete Record Functions
        let deleteRecordId = null;
        let deleteRecordRow = null;

        function deleteRecord(event, recordId, itemCode) {
            event.preventDefault();
            deleteRecordId = recordId;
            deleteRecordRow = event.target.closest('tr');
            
            document.getElementById('deleteItemName').textContent = itemCode || 'this record';
            document.getElementById('deleteConfirmModal').classList.add('show');
        }

        function closeDeleteModal() {
            document.getElementById('deleteConfirmModal').classList.remove('show');
            deleteRecordId = null;
            deleteRecordRow = null;
        }

        function confirmDelete() {
            if (!deleteRecordId) return;
            
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            confirmBtn.disabled = true;
            
            fetch('api/delete-record.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: deleteRecordId })
            })
            .then(response => response.json())
            .then(result => {
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
                
                if (result.success) {
                    // Remove the row from table with animation
                    if (deleteRecordRow) {
                        deleteRecordRow.style.transition = 'all 0.3s ease';
                        deleteRecordRow.style.opacity = '0';
                        deleteRecordRow.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            deleteRecordRow.remove();
                            updateSearchCount();
                        }, 300);
                    }
                    closeDeleteModal();
                } else {
                    alert('Error: ' + (result.message || 'Failed to delete record'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
                alert('Error deleting record. Please try again.');
            });
        }

        // Close delete modal when clicking outside
        document.getElementById('deleteConfirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Add Record Modal Functions
        function openAddModal() {
            document.getElementById('addRecordModal').classList.add('show');
            // Set default date to today
            document.getElementById('add_delivery_date').value = new Date().toISOString().split('T')[0];
        }

        function closeAddModal() {
            document.getElementById('addRecordModal').classList.remove('show');
            document.getElementById('addRecordForm').reset();
        }

        function submitAddRecord(event) {
            event.preventDefault();
            
            const form = document.getElementById('addRecordForm');
            const submitBtn = form.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Gather form data
            const formData = {
                serial_no: document.getElementById('add_serial_no').value,
                invoice_no: document.getElementById('add_invoice_no').value,
                item_code: document.getElementById('add_item_code').value,
                item_name: document.getElementById('add_item_name').value,
                company_name: document.getElementById('add_company_name').value || 'Andison Industrial',
                quantity: parseInt(document.getElementById('add_quantity').value) || 0,
                delivery_date: document.getElementById('add_delivery_date').value,
                status: document.getElementById('add_status').value,
                notes: document.getElementById('add_notes').value
            };
            
            // Send to API
            fetch('api/add-record.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(result => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                if (result.success) {
                    alert('Record added successfully!');
                    closeAddModal();
                    // Reload page to show new record
                    window.location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to add record'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                alert('Error adding record. Please try again.');
            });
        }

        // Close Add Modal when clicking outside
        window.addEventListener('click', (e) => {
            const addModal = document.getElementById('addRecordModal');
            if (e.target === addModal) {
                closeAddModal();
            }
        });

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
                updateSearchCount();
            });
        });

        // Search functionality
        function searchTable() {
            const searchInput = document.getElementById('searchInput');
            const filter = searchInput.value.toLowerCase().trim();
            const table = document.querySelector('table tbody');
            const rows = table.querySelectorAll('tr');
            
            let visibleCount = 0;
            
            rows.forEach(row => {
                // Skip empty state row
                if (row.querySelector('td[colspan]')) return;
                
                const cells = row.querySelectorAll('td');
                let found = false;
                
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        found = true;
                    }
                });
                
                if (found || filter === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            updateSearchCount(visibleCount, filter);
        }

        function updateSearchCount(count, filter) {
            const searchCount = document.getElementById('searchCount');
            const totalRows = document.querySelectorAll('table tbody tr:not([style*=\"display: none\"])').length;
            
            if (filter && filter !== '') {
                searchCount.innerHTML = `<i class="fas fa-filter"></i> Found <strong>${count}</strong> matching records`;
                searchCount.style.background = 'rgba(47, 95, 167, 0.2)';
                searchCount.style.color = '#6ba3eb';
            } else {
                searchCount.innerHTML = 'Showing all records';
                searchCount.style.background = 'rgba(255, 255, 255, 0.05)';
                searchCount.style.color = '#a0a0a0';
            }
        }

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
