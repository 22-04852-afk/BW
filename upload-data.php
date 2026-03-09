<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

// Get total record count from database
require_once 'db_config.php';
$totalRecords = 0;

if ($conn) {
    $result = @$conn->query("SELECT COUNT(*) as total FROM delivery_records");
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row && isset($row['total'])) {
            $totalRecords = intval($row['total']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')!=='dark'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Data - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .upload-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .upload-section {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: #f4d03f;
            font-size: 26px;
        }

        .section-subtitle {
            font-size: 13px;
            color: #a0a0a0;
            margin-bottom: 25px;
        }

        /* Upload Zone */
        .upload-zone {
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 50px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
        }

        .upload-zone:hover,
        .upload-zone.dragover {
            border-color: #f4d03f;
            background: rgba(244, 208, 63, 0.08);
        }

        .upload-icon {
            font-size: 48px;
            color: #f4d03f;
            margin-bottom: 15px;
        }

        .upload-zone h3 {
            color: #fff;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .upload-zone p {
            color: #a0a0a0;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .upload-formats {
            color: #7a8a9a;
            font-size: 12px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        #fileInput {
            display: none;
        }

        /* File Info */
        .file-info {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            display: none;
            gap: 12px;
            align-items: center;
        }

        .file-info.show {
            display: flex;
        }

        .file-icon {
            font-size: 24px;
            color: #00d9ff;
        }

        .file-details {
            flex: 1;
            text-align: left;
        }

        .file-name {
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .file-size {
            color: #a0a0a0;
            font-size: 12px;
        }

        .file-remove {
            background: rgba(255, 107, 107, 0.2);
            border: none;
            color: #ff6b6b;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .file-remove:hover {
            background: rgba(255, 107, 107, 0.4);
        }

        /* Buttons */
        .upload-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            justify-content: center;
        }

        .btn-upload,
        .btn-cancel,
        .btn-import {
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-upload {
            background: linear-gradient(135deg, #f4d03f 0%, #f1bf10 100%);
            color: #000;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(244, 208, 63, 0.3);
        }

        .btn-upload:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: #a0a0a0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .btn-import {
            background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
            color: #fff;
            display: none;
            pointer-events: auto !important;
        }

        .btn-import:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(81, 207, 102, 0.3);
        }

        /* Preview Section */
        .preview-section {
            display: none;
        }

        .preview-section.show {
            display: block;
        }

        .preview-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(81, 207, 102, 0.1);
            border-left: 4px solid #51cf66;
            border-radius: 8px;
        }

        .preview-stats {
            display: flex;
            gap: 30px;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-label {
            color: #a0a0a0;
            font-size: 12px;
        }

        .stat-value {
            color: #51cf66;
            font-weight: 700;
            font-size: 18px;
        }

        /* Data Preview Table */
        .table-container {
            background: #13172c;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container thead {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .table-container th {
            padding: 12px;
            text-align: left;
            font-size: 13px;
            color: #a0a0a0;
            text-transform: uppercase;
            font-weight: 600;
        }

        .table-container td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            font-size: 13px;
        }

        .table-container tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .row-number {
            color: #7a8a9a;
            font-size: 12px;
            text-align: center;
        }

        /* Status Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }

        .alert.show {
            display: flex;
        }

        .alert-icon {
            font-size: 16px;
        }

        .alert-success {
            background: rgba(81, 207, 102, 0.2);
            border-left: 4px solid #51cf66;
            color: #51cf66;
        }

        .alert-error {
            background: rgba(255, 107, 107, 0.2);
            border-left: 4px solid #ff6b6b;
            color: #ff6b6b;
        }

        .alert-warning {
            background: rgba(255, 214, 10, 0.2);
            border-left: 4px solid #ffd60a;
            color: #ffd60a;
        }

        .alert-info {
            background: rgba(0, 217, 255, 0.2);
            border-left: 4px solid #00d9ff;
            color: #00d9ff;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #f4d03f;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Template Section */
        .template-section {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .template-info {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .template-icon {
            font-size: 24px;
            color: #00d9ff;
            flex-shrink: 0;
        }

        .template-content h4 {
            color: #fff;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .template-content p {
            color: #a0a0a0;
            font-size: 12px;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .template-columns {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 12px;
            font-size: 12px;
            color: #7a8a9a;
        }

        .template-columns span {
            padding: 8px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            border-left: 3px solid #f4d03f;
            padding-left: 10px;
        }

        .btn-download-template {
            background: linear-gradient(135deg, #00d9ff 0%, #0099cc 100%);
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-download-template:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 217, 255, 0.3);
        }

        /* Hide parse button (parsing is automatic) */
        #parseBtn {
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .upload-section {
                padding: 25px;
            }

            .upload-zone {
                padding: 30px;
            }

            .upload-actions {
                flex-direction: column;
            }

            .btn-upload,
            .btn-cancel,
            .btn-import {
                width: 100%;
            }

            .preview-stats {
                flex-direction: column;
                gap: 15px;
            }

            .template-columns {
                grid-template-columns: 1fr;
            }
        }

        /* Success Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.show {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 450px;
            width: 90%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            animation: bounce 0.5s ease 0.3s;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .modal-icon i {
            font-size: 40px;
            color: #fff;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #51cf66;
            margin-bottom: 10px;
        }

        .modal-message {
            color: #a0a0a0;
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .modal-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
        }

        .modal-stat {
            text-align: center;
        }

        .modal-stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #51cf66;
        }

        .modal-stat-label {
            font-size: 12px;
            color: #7a8a9a;
            margin-top: 5px;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-modal {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-modal-primary {
            background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
            color: #fff;
        }

        .btn-modal-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(81, 207, 102, 0.3);
        }

        .btn-modal-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #a0a0a0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-modal-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        /* Light Mode Overrides */
        .light-mode .template-section {
            background: rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        .light-mode .template-icon {
            color: #0077b6;
        }

        .light-mode .template-content h4 {
            color: #1a1a2e;
        }

        .light-mode .template-content p {
            color: #555;
        }

        .light-mode .template-columns {
            color: #444;
        }

        .light-mode .template-columns span {
            background: rgba(0, 0, 0, 0.04);
            border-left-color: #e6a700;
        }

        .light-mode .btn-download-template {
            background: linear-gradient(135deg, #0077b6 0%, #005f8a 100%);
        }

        /* Delete Section Light Mode */
        .delete-section {
            border: 1px solid rgba(255, 107, 107, 0.3);
            background: linear-gradient(135deg, #2a1a1a 0%, #3a1f1f 100%);
        }

        .light-mode .delete-section {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
            border: 1px solid rgba(255, 107, 107, 0.4);
        }

        .delete-section-inner {
            background: rgba(255, 107, 107, 0.1);
        }

        .light-mode .delete-section-inner {
            background: rgba(255, 107, 107, 0.08);
        }

        .delete-section-title {
            color: #fff;
        }

        .light-mode .delete-section-title {
            color: #1a1a2e;
        }

        .delete-section-subtitle {
            color: #a0a0a0;
        }

        .light-mode .delete-section-subtitle {
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Delete Confirmation Modal (No longer used - deletion happens immediately after browser confirm) -->
    <div class="modal-overlay" id="deleteModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-icon" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h2 class="modal-title" style="color: #ff6b6b;">Deleting...</h2>
            <p class="modal-message">Please wait while we delete your data...</p>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="modal-title">Import Successful!</h2>
            <p class="modal-message" id="modalMessage">Your data has been imported successfully.</p>
            <div class="modal-stats">
                <div class="modal-stat">
                    <div class="modal-stat-value" id="modalImported">0</div>
                    <div class="modal-stat-label">Records Imported</div>
                </div>
                <div class="modal-stat">
                    <div class="modal-stat-value" id="modalFailed">0</div>
                    <div class="modal-stat-label">Failed</div>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="btn-modal btn-modal-primary" onclick="goToDeliveryRecords()">
                    <i class="fas fa-list"></i> View Records
                </button>
                <button class="btn-modal btn-modal-secondary" onclick="closeSuccessModal()">
                    <i class="fas fa-plus"></i> Import More
                </button>
            </div>
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
                    <a href="index.php" style="display:flex;align-items:center;">
                        <img src="assets/logo.png" alt="Andison" style="height:48px;width:auto;object-fit:contain;">
                    </a>
                </div>
            </div>

            <!-- Center Title -->
            <div class="navbar-center">
                <h1 class="dashboard-title">Upload Data</h1>
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

                <!-- Inventory -->
                <li class="menu-item">
                    <a href="inventory.php" class="menu-link">
                        <i class="fas fa-boxes"></i>
                        <span class="menu-label">Inventory</span>
                    </a>
                </li>

                <!-- Andison Manila -->
                <li class="menu-item">
                    <a href="andison-manila.php" class="menu-link">
                        <i class="fas fa-truck-fast"></i>
                        <span class="menu-label">Andison Manila</span>
                    </a>
                </li>

                <!-- Client Companies -->
                <li class="menu-item">
                    <a href="client-companies.php" class="menu-link">
                        <i class="fas fa-building"></i>
                        <span class="menu-label">Client Companies</span>
                    </a>
                </li>

                <!-- Models -->
                <li class="menu-item">
                    <a href="models.php" class="menu-link">
                        <i class="fas fa-cube"></i>
                        <span class="menu-label">Models</span>
                    </a>
                </li>

                <!-- Reports -->
                <li class="menu-item">
                    <a href="reports.php" class="menu-link">
                        <i class="fas fa-file-alt"></i>
                        <span class="menu-label">Reports</span>
                    </a>
                </li>

                <!-- Upload Data (NEW) -->
                <li class="menu-item active">
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
        <div class="upload-container">
            <!-- Template Information -->
            <div class="upload-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Expected File Format
                </h2>
                <p class="section-subtitle">Ensure your Excel file matches the required format below</p>

                <div class="template-section">
                    <div class="template-info">
                        <div class="template-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <div class="template-content">
                            <h4>📊 Excel File Requirements</h4>
                            <p>Your Excel file should contain the following columns (in any order). The system automatically maps common column names:</p>
                            <div class="template-columns">
                                <span><strong>Invoice No.</strong></span>
                                <span><strong>Date</strong></span>
                                <span><strong>Item</strong></span>
                                <span><strong>Description</strong></span>
                                <span><strong>Qty.</strong></span>
                                <span><strong>Serial No.</strong></span>
                                <span><strong>Date Delivered</strong></span>
                                <span><strong>Remarks</strong> (Optional)</span>
                            </div>
                            <p style="margin-top: 15px; font-style: italic;">
                                <i class="fas fa-lightbulb" style="color: #f4d03f;"></i>
                                The system also supports: Item_Code, Item_Name, Quantity, Status, Company_Name, etc.
                            </p>
                            <button class="btn-download-template" onclick="downloadTemplate()">
                                <i class="fas fa-download"></i> Download Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Messages -->
            <div id="alertContainer"></div>

            <!-- Upload Section -->
            <div class="upload-section">
                <h2 class="section-title">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Upload Excel Files
                </h2>
                <p class="section-subtitle">
                    <i class="fas fa-info-circle" style="color: #f4d03f;"></i>
                    To keep data separate, import files and sheets ONE AT A TIME
                </p>

                <div class="upload-zone" id="uploadZone">
                    <div class="upload-icon">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <h3>Drag Excel files here</h3>
                    <p>or click to select from your computer</p>
                    <div class="upload-formats">
                        Supported formats: <strong>.xlsx, .xls, .csv</strong> (Max 10MB per file) | <strong>Import one file at a time</strong>
                    </div>
                    <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" multiple />
                </div>

                <div class="files-list" id="filesList" style="margin-top: 20px; display: none;"></div>

                <!-- Sheet Selector (for multi-sheet Excel files) -->
                <div class="sheet-selector" id="sheetSelector" style="display: none; margin-top: 20px;">
                    <div id="sheetList" style="display: flex; flex-direction: column; gap: 10px;"></div>
                </div>

                <div class="upload-actions">
                    <button class="btn-upload" id="uploadBtn" onclick="parseAllFiles()" style="flex: 1; display: none;">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Data
                    </button>
                    <button class="btn-cancel" id="cancelBtn" onclick="resetUpload()" style="flex: 1;">
                        <i class="fas fa-times"></i> Clear Files
                    </button>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="upload-section preview-section" id="previewSection">
                <h2 class="section-title">
                    <i class="fas fa-eye"></i>
                    Data Preview
                </h2>
                <p class="section-subtitle">Review the data before importing</p>

                <div class="preview-info" id="previewInfo">
                    <div class="preview-stats">
                        <div class="stat">
                            <span class="stat-label">Total Rows:</span>
                            <span class="stat-value" id="totalRows">0</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Columns:</span>
                            <span class="stat-value" id="totalColumns">0</span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Valid Records:</span>
                            <span class="stat-value" id="validRecords">0</span>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table id="previewTable">
                        <thead id="tableHead"></thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>

                <!-- Chart Preview Section -->
                <div class="chart-preview-section" id="chartPreviewSection" style="display: none; margin-top: 30px;">
                    <h3 style="color: #f4d03f; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-chart-bar"></i> Data Visualization Preview
                    </h3>
                    <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
                        <!-- Summary Donut Charts -->
                        <div class="chart-card" style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 20px;">
                            <h4 style="color: #fff; margin-bottom: 15px; font-size: 14px;">Total Quantity by Status</h4>
                            <canvas id="statusChart" height="200"></canvas>
                        </div>
                        <div class="chart-card" style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 20px;">
                            <h4 style="color: #fff; margin-bottom: 15px; font-size: 14px;">Records by Month</h4>
                            <canvas id="monthlyChart" height="200"></canvas>
                        </div>
                        <div class="chart-card" style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 20px;">
                            <h4 style="color: #fff; margin-bottom: 15px; font-size: 14px;">Top Companies by Quantity</h4>
                            <canvas id="companyChart" height="200"></canvas>
                        </div>
                        <div class="chart-card" style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 20px;">
                            <h4 style="color: #fff; margin-bottom: 15px; font-size: 14px;">Quantity by Item/Model</h4>
                            <canvas id="itemChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <div class="upload-actions">
                    <button class="btn-import" id="importBtn" onclick="doImport()" style="display: none; cursor: pointer; position: relative; z-index: 10;">
                        <i class="fas fa-upload"></i> Import Data
                    </button>
                    <button class="btn-cancel" onclick="resetUpload()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>

            <!-- Delete All Data Section -->
            <div class="upload-section delete-section">
                <h2 class="section-title" style="color: #ff6b6b;">
                    <i class="fas fa-trash-alt"></i>
                    Manage Uploaded Data
                </h2>
                <p class="section-subtitle">Clear all uploaded records to start fresh</p>

                <div class="delete-section-inner" style="display: flex; align-items: center; gap: 20px; padding: 20px; border-radius: 12px;">
                    <div style="flex: 1;">
                        <p class="delete-section-title" style="margin-bottom: 5px; font-weight: 600;">
                            <i class="fas fa-database" style="color: #ff6b6b; margin-right: 8px;"></i>
                            Total Records: <span id="currentRecordCount" style="color: #ff6b6b; font-size: 20px;"><?php echo number_format($totalRecords); ?></span>
                        </p>
                        <p class="delete-section-subtitle" style="font-size: 12px;">Delete all data to upload a new Excel file from scratch</p>
                    </div>
                    <button id="deleteAllBtn" type="button" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: #fff; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; font-family: 'Poppins', sans-serif; transition: all 0.3s ease; display: flex; align-items: center; gap: 10px; white-space: nowrap; position: relative; z-index: 10;">
                        <i class="fas fa-trash-alt"></i> Delete All Data
                    </button>
                    <style>
                        button[onclick="showDeleteModal()"]:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
                        }
                        button[onclick="showDeleteModal()"]:active {
                            transform: translateY(0);
                        }
                    </style>
                </div>
            </div>
        </div>
    </main>

    <script src="js/app.js" defer></script>
    <!-- SheetJS XLSX library - local copy -->
    <script src="js/xlsx.min.js"></script>
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        // Debug: Check if XLSX loaded
        console.log('XLSX library loaded:', typeof XLSX !== 'undefined');
        
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const filesList = document.getElementById('filesList');
        const previewSection = document.getElementById('previewSection');
        const importBtn = document.getElementById('importBtn');
        const alertContainer = document.getElementById('alertContainer');
        const sheetSelector = document.getElementById('sheetSelector');
        const sheetList = document.getElementById('sheetList');
        const chartPreviewSection = document.getElementById('chartPreviewSection');

        let selectedFiles = [];
        let parsedData = null;
        let allParsedData = [];
        let workbookSheets = {}; // Store workbook sheets for selection
        let previewCharts = {}; // Store chart instances

        // Initialize database on page load
        window.addEventListener('load', () => {
            console.log('Page loaded. Import button:', importBtn);
            fetch('api/setup-db.php')
                .then(response => response.json())
                .catch(error => console.log('Database setup complete'));
        });

        // File Upload Handlers
        uploadZone.addEventListener('click', () => fileInput.click());

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length === 0) return;

            const maxSize = 10 * 1024 * 1024; // 10MB
            let validFiles = [];
            let errors = [];

            // Validate each file
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                if (!file.name.match(/\.(xlsx|xls|csv)$/i)) {
                    errors.push(`${file.name}: Invalid format`);
                    continue;
                }

                if (file.size > maxSize) {
                    errors.push(`${file.name}: File too large (max 10MB)`);
                    continue;
                }

                // Check if file already exists
                if (!selectedFiles.find(f => f.name === file.name)) {
                    validFiles.push(file);
                }
            }

            if (errors.length > 0) {
                showAlert('error', errors.join('<br>'));
            }

            if (validFiles.length > 0) {
                selectedFiles = [...selectedFiles, ...validFiles];
                updateFilesList();
                // Show upload button
                document.getElementById('uploadBtn').style.display = 'block';
            }
        }

        function updateFilesList() {
            if (selectedFiles.length === 0) {
                filesList.style.display = 'none';
                return;
            }

            filesList.style.display = 'block';
            let html = '<div style="margin-bottom: 10px; font-weight: 600; color: #f4d03f;"><i class="fas fa-files"></i> ' + selectedFiles.length + ' file(s) selected</div>';
            
            selectedFiles.forEach((file, index) => {
                html += `
                    <div class="file-info show" style="display: flex; margin-bottom: 10px;">
                        <div class="file-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <div class="file-details">
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                        </div>
                        <button class="file-remove" onclick="removeFile(${index})">Remove</button>
                    </div>
                `;
            });
            
            filesList.innerHTML = html;
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateFilesList();
            if (selectedFiles.length === 0) {
                resetUpload();
            }
        }

        async function parseAllFiles() {
            if (selectedFiles.length > 1) {
                showAlert('warning', 'Please upload and import files ONE AT A TIME to keep data separate. Processing first file only.');
            } else {
                showAlert('info', `Parsing file "${selectedFiles[0].name}"... Please wait.`);
            }
            
            allParsedData = [];
            workbookSheets = {};
            
            // Only process the first file
            const file = selectedFiles[0];
            try {
                const result = await parseFileWithSheets(file);
                if (result.hasMultipleSheets) {
                    // Show sheet selector - user must pick one sheet
                    workbookSheets[file.name] = result.sheets;
                    showSheetSelector(file.name, result.sheets);
                    return; // Wait for user to select a sheet
                } else {
                    // Single sheet file - load directly
                    allParsedData = result.data;
                    
                    if (allParsedData.length > 0) {
                        parsedData = allParsedData;
                        displayPreview(allParsedData);
                        generatePreviewCharts(allParsedData);
                        showAlert('success', `✓ Loaded "${file.name}": ${allParsedData.length} records. Review below and click Import.`);
                    } else {
                        showAlert('warning', `No data found in "${file.name}"`);
                    }
                }
            } catch (error) {
                showAlert('error', `Error parsing ${file.name}: ${error.message}`);
            }
        }
        }

        function parseFileWithSheets(file) {
            return new Promise((resolve, reject) => {
                if (file.name.endsWith('.csv')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const data = e.target.result;
                            const rows = parseCSV(data);
                            resolve({ hasMultipleSheets: false, data: rows });
                        } catch (error) {
                            reject(error);
                        }
                    };
                    reader.onerror = reject;
                    reader.readAsText(file);
                } else {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const data = e.target.result;
                            const workbook = XLSX.read(data, { type: 'binary', cellDates: true });
                            
                            // Check for multiple sheets
                            if (workbook.SheetNames.length > 1) {
                                const sheets = {};
                                workbook.SheetNames.forEach(sheetName => {
                                    const worksheet = workbook.Sheets[sheetName];
                                    const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                                    const rowCount = jsonData.length > 0 ? jsonData.length - 1 : 0;
                                    sheets[sheetName] = {
                                        worksheet: worksheet,
                                        rowCount: rowCount,
                                        workbook: workbook
                                    };
                                });
                                resolve({ hasMultipleSheets: true, sheets: sheets, workbook: workbook });
                            } else {
                                // Single sheet - parse directly
                                const firstSheet = workbook.SheetNames[0];
                                const rows = parseWorksheet(workbook.Sheets[firstSheet]);
                                resolve({ hasMultipleSheets: false, data: rows });
                            }
                        } catch (error) {
                            reject(error);
                        }
                    };
                    reader.onerror = reject;
                    reader.readAsBinaryString(file);
                }
            });
        }

        function parseWorksheet(worksheet) {
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
            if (jsonData.length < 2) return [];
            
            const headers = jsonData[0].map(h => String(h || '').trim()).filter(h => h !== '');
            if (headers.length === 0) return [];
            
            const rows = [];
            for (let i = 1; i < jsonData.length; i++) {
                const rowData = jsonData[i];
                if (!rowData || rowData.length === 0) continue;
                
                const row = {};
                headers.forEach((header, index) => {
                    let value = rowData[index];
                    if (value instanceof Date) {
                        value = value.toISOString().split('T')[0];
                    }
                    row[header] = value !== undefined ? value : '';
                });
                
                if (Object.values(row).some(v => v !== '')) {
                    rows.push(row);
                }
            }
            return rows;
        }

        function showSheetSelector(fileName, sheets) {
            sheetSelector.style.display = 'block';
            let html = `
                <div style="background: linear-gradient(135deg, rgba(255, 214, 10, 0.1) 0%, rgba(255, 214, 10, 0.05) 100%); border: 1px solid rgba(255, 214, 10, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <p style="color: #f4d03f; margin: 0; font-weight: 600; margin-bottom: 8px;"><i class="fas fa-exclamation-circle"></i> Import One Sheet at a Time</p>
                    <p style="color: #a0a0a0; margin: 0; font-size: 12px;">File "${fileName}" has ${Object.keys(sheets).length} sheets. To keep data separate, select <strong>ONE sheet</strong> to import now. You can upload the file again to import other sheets.</p>
                </div>
                <p style="color: #a0a0a0; margin-bottom: 15px; font-size: 13px;"><i class="fas fa-layer-group"></i> Available Sheets:</p>
            `;
            
            Object.keys(sheets).forEach((sheetName, index) => {
                const info = sheets[sheetName];
                html += `
                    <label class="sheet-radio" style="display: flex; align-items: center; gap: 8px; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 2px solid transparent; border-radius: 8px; cursor: pointer; margin-bottom: 8px; transition: all 0.3s ease;">
                        <input type="radio" name="sheet-selection" class="sheet-check" data-file="${fileName}" data-sheet="${sheetName}" ${index === 0 ? 'checked' : ''} onchange="updateSelectedSheets(this)">
                        <span style="color: #fff; font-weight: 500; flex: 1;">${sheetName}</span>
                        <span style="color: #a0a0a0; font-size: 12px; white-space: nowrap;">(${info.rowCount} rows)</span>
                    </label>
                `;
            });
            
            html += `
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button onclick="parseSelectedSheets()" style="flex: 1; background: linear-gradient(135deg, #f4d03f 0%, #f9d76a 100%); color: #1a3a5c; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-download"></i> Load This Sheet
                    </button>
                    <button onclick="resetUpload()" style="flex: 1; background: rgba(100, 100, 100, 0.5); color: #fff; border: 1px solid rgba(255, 255, 255, 0.2); padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            `;
            sheetList.innerHTML = html;
        }

        function updateSelectedSheets(radio) {
            // Update radio button styles when selected
            document.querySelectorAll('.sheet-radio').forEach(label => {
                const input = label.querySelector('input');
                if (input.checked) {
                    label.style.background = 'rgba(244, 208, 63, 0.15)';
                    label.style.borderColor = '#f4d03f';
                } else {
                    label.style.background = 'rgba(255,255,255,0.05)';
                    label.style.borderColor = 'transparent';
                }
            });
        }

        async function parseSelectedSheets() {
            const selectedRadio = document.querySelector('.sheet-check:checked');
            if (!selectedRadio) {
                showAlert('error', 'Please select a sheet to import');
                return;
            }
            
            const fileName = selectedRadio.dataset.file;
            const sheetName = selectedRadio.dataset.sheet;
            
            showAlert('info', `Loading sheet "${sheetName}" from "${fileName}"...`);
            allParsedData = [];
            
            if (workbookSheets[fileName] && workbookSheets[fileName][sheetName]) {
                const worksheet = workbookSheets[fileName][sheetName].worksheet;
                const rows = parseWorksheet(worksheet);
                allParsedData = rows;
            }
            
            if (allParsedData.length > 0) {
                parsedData = allParsedData;
                displayPreview(allParsedData);
                generatePreviewCharts(allParsedData);
                sheetSelector.style.display = 'none';
                showAlert('success', `✓ Loaded "${sheetName}": ${allParsedData.length} records. Review below and click Import.`);
            } else {
                showAlert('warning', `No data found in sheet "${sheetName}"`);
            }
        }

        function generatePreviewCharts(data) {
            if (!data || data.length === 0) return;
            
            // Destroy existing charts
            Object.values(previewCharts).forEach(chart => {
                if (chart) chart.destroy();
            });
            previewCharts = {};
            
            chartPreviewSection.style.display = 'block';
            
            // Analyze data for chart generation
            const headers = Object.keys(data[0]);
            
            // Find relevant columns
            const monthCol = headers.find(h => h.toLowerCase().includes('month') || h.toLowerCase().includes('delivery month'));
            const companyCol = headers.find(h => h.toLowerCase().includes('company') || h.toLowerCase().includes('sold to') || h.toLowerCase().includes('client'));
            const qtyCol = headers.find(h => h.toLowerCase().includes('qty') || h.toLowerCase().includes('quantity'));
            const itemCol = headers.find(h => h.toLowerCase().includes('item') || h.toLowerCase().includes('description') || h.toLowerCase().includes('model'));
            const statusCol = headers.find(h => h.toLowerCase().includes('status'));
            
            // 1. Monthly Distribution Chart
            if (monthCol) {
                const monthData = {};
                data.forEach(row => {
                    const month = row[monthCol] || 'Unknown';
                    const qty = parseInt(row[qtyCol]) || 1;
                    monthData[month] = (monthData[month] || 0) + qty;
                });
                
                const ctx = document.getElementById('monthlyChart').getContext('2d');
                previewCharts.monthly = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(monthData),
                        datasets: [{
                            label: 'Quantity per Month',
                            data: Object.values(monthData),
                            backgroundColor: ['#f39c12', '#3498db', '#e74c3c', '#2ecc71', '#9b59b6', '#1abc9c', '#34495e', '#f1c40f', '#e67e22', '#95a5a6', '#d35400', '#c0392b']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { color: '#a0a0a0' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                            x: { ticks: { color: '#a0a0a0' }, grid: { display: false } }
                        }
                    }
                });
            }
            
            // 2. Top Companies Chart
            if (companyCol) {
                const companyData = {};
                data.forEach(row => {
                    const company = row[companyCol] || 'Unknown';
                    const qty = parseInt(row[qtyCol]) || 1;
                    companyData[company] = (companyData[company] || 0) + qty;
                });
                
                // Sort and get top 10
                const sorted = Object.entries(companyData).sort((a, b) => b[1] - a[1]).slice(0, 10);
                
                const ctx = document.getElementById('companyChart').getContext('2d');
                previewCharts.company = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: sorted.map(s => s[0].substring(0, 25)),
                        datasets: [{
                            label: 'Quantity',
                            data: sorted.map(s => s[1]),
                            backgroundColor: '#3498db'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { color: '#a0a0a0' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                            y: { ticks: { color: '#a0a0a0', font: { size: 10 } }, grid: { display: false } }
                        }
                    }
                });
            }
            
            // 3. Items/Models Chart
            if (itemCol) {
                const itemData = {};
                data.forEach(row => {
                    const item = row[itemCol] || 'Unknown';
                    const qty = parseInt(row[qtyCol]) || 1;
                    itemData[item] = (itemData[item] || 0) + qty;
                });
                
                // Sort and get top 10
                const sorted = Object.entries(itemData).sort((a, b) => b[1] - a[1]).slice(0, 10);
                
                const ctx = document.getElementById('itemChart').getContext('2d');
                previewCharts.item = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: sorted.map(s => s[0].substring(0, 20)),
                        datasets: [{
                            label: 'Quantity',
                            data: sorted.map(s => s[1]),
                            backgroundColor: '#e74c3c'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { color: '#a0a0a0' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                            x: { ticks: { color: '#a0a0a0', maxRotation: 45, font: { size: 9 } }, grid: { display: false } }
                        }
                    }
                });
            }
            
            // 4. Status/Summary Donut Chart
            const totalQty = data.reduce((sum, row) => sum + (parseInt(row[qtyCol]) || 1), 0);
            const totalRecords = data.length;
            
            const ctx = document.getElementById('statusChart').getContext('2d');
            previewCharts.status = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Total Records', 'Total Quantity'],
                    datasets: [{
                        data: [totalRecords, totalQty],
                        backgroundColor: ['#f39c12', '#3498db']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#a0a0a0' } }
                    }
                }
            });
        }

        function parseFileAsync(file) {
            return new Promise((resolve, reject) => {
                if (file.name.endsWith('.csv')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const data = e.target.result;
                            const rows = parseCSV(data);
                            resolve(rows);
                        } catch (error) {
                            reject(error);
                        }
                    };
                    reader.onerror = function(error) {
                        reject(error);
                    };
                    reader.readAsText(file);
                } else {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const data = e.target.result;
                            const workbook = XLSX.read(data, { type: 'binary', cellDates: true });
                            const firstSheet = workbook.SheetNames[0];
                            const worksheet = workbook.Sheets[firstSheet];
                            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                            
                            if (jsonData.length < 2) {
                                reject(new Error('File is empty or has no data rows'));
                                return;
                            }
                            
                            const headers = jsonData[0].map(h => String(h).trim());
                            const rows = [];
                            
                            for (let i = 1; i < jsonData.length; i++) {
                                const rowData = jsonData[i];
                                if (!rowData || rowData.length === 0) continue;
                                
                                const row = {};
                                headers.forEach((header, index) => {
                                    let value = rowData[index];
                                    if (value instanceof Date) {
                                        value = value.toISOString().split('T')[0];
                                    }
                                    row[header] = value !== undefined ? value : '';
                                });
                                
                                if (Object.values(row).some(v => v !== '')) {
                                    rows.push(row);
                                }
                            }
                            
                            resolve(rows);
                        } catch (error) {
                            reject(error);
                        }
                    };
                    reader.onerror = function(error) {
                        reject(error);
                    };
                    reader.readAsBinaryString(file);
                }
            });
        }

        function parseCSV(data) {
            const lines = data.split('\n');
            const headers = lines[0].split(',').map(h => h.trim());
            const rows = [];

            for (let i = 1; i < lines.length; i++) {
                if (lines[i].trim() === '') continue;
                const values = lines[i].split(',').map(v => v.trim());
                const row = {};
                headers.forEach((header, index) => {
                    row[header] = values[index] || '';
                });
                rows.push(row);
            }

            return rows;
        }

        function displayPreview(rows) {
            if (rows.length === 0) {
                showAlert('warning', 'No data found in the file.');
                return;
            }

            parsedData = rows;
            const headers = Object.keys(rows[0]);

            // Update stats
            document.getElementById('totalRows').textContent = rows.length;
            document.getElementById('totalColumns').textContent = headers.length;
            document.getElementById('validRecords').textContent = rows.length;

            // Create table header
            const thead = document.getElementById('tableHead');
            thead.innerHTML = '<tr><th class="row-number">#</th>';
            headers.forEach(header => {
                thead.innerHTML += `<th>${header}</th>`;
            });
            thead.innerHTML += '</tr>';

            // Create table body (first 10 rows)
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';
            rows.slice(0, 10).forEach((row, index) => {
                let tr = `<tr><td class="row-number">${index + 1}</td>`;
                headers.forEach(header => {
                    tr += `<td>${row[header] || '-'}</td>`;
                });
                tr += '</tr>';
                tbody.innerHTML += tr;
            });

            previewSection.classList.add('show');
            importBtn.style.display = 'block';
            console.log('Preview shown. Import button visible:', importBtn.style.display);

            if (rows.length > 10) {
                showAlert('info', `Showing first 10 rows of ${rows.length} records.`);
            }
        }

        function doImport() {
            console.log('Import button clicked! parsedData:', parsedData ? parsedData.length : 0);
            if (!parsedData || parsedData.length === 0) {
                showAlert('error', 'No data to import. Please select file(s) first.');
                return;
            }

            importBtn.innerHTML = '<span class="spinner"></span> Importing...';
            importBtn.disabled = true;

            // Prepare data for import
            const fileNames = selectedFiles.map(f => f.name).join(', ');
            const importData = {
                data: parsedData,
                fileName: fileNames,
                timestamp: new Date().toISOString()
            };

            console.log('Sending import data with', parsedData.length, 'rows from', selectedFiles.length, 'file(s)');

            // Send to backend
            fetch('api/import-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(importData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(result => {
                console.log('Import result:', result);
                importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Data';
                importBtn.disabled = false;

                if (result.success) {
                    // Show success popup modal
                    showSuccessModal(result.imported, result.failed || 0, fileNames);
                    // Don't auto-reset - let user click 'View Records' or 'Import More'
                } else {
                    showAlert('error', result.message || 'Import failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Import error:', error);
                importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Data';
                importBtn.disabled = false;
                showAlert('error', 'Error during import: ' + error.message);
            });
        }

        function resetUpload() {
            selectedFiles = [];
            parsedData = null;
            allParsedData = [];
            workbookSheets = {};
            fileInput.value = '';
            filesList.style.display = 'none';
            filesList.innerHTML = '';
            sheetSelector.style.display = 'none';
            sheetList.innerHTML = '';
            chartPreviewSection.style.display = 'none';
            // Destroy charts
            Object.values(previewCharts).forEach(chart => {
                if (chart) chart.destroy();
            });
            previewCharts = {};
            previewSection.classList.remove('show');
            document.getElementById('uploadBtn').style.display = 'none';
            importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Data';
            importBtn.disabled = false;
            importBtn.style.display = 'none';
            alertContainer.innerHTML = '';
        }

        function showAlert(type, message) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} show`;
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            alert.innerHTML = `
                <i class="fas ${icons[type]} alert-icon"></i>
                <span>${message}</span>
            `;

            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);

            // Auto-remove success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => alert.remove(), 5000);
            }
        }

        function showSuccessModal(imported, failed, fileName) {
            document.getElementById('modalImported').textContent = imported;
            document.getElementById('modalFailed').textContent = failed;
            document.getElementById('modalMessage').textContent = 
                `Successfully imported ${imported} records from "${fileName}"`;
            document.getElementById('successModal').classList.add('show');
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.remove('show');
            resetUpload();
        }

        function goToDeliveryRecords() {
            window.location.href = 'delivery-records.php';
        }

        function downloadTemplate() {
            // Create empty template with headers only
            const templateData = [
                {
                    'Invoice No.': '',
                    'Date': '',
                    'Item': '',
                    'Description': '',
                    'Qty.': '',
                    'Serial No.': '',
                    'Date Delivered': '',
                    'Remarks': ''
                }
            ];

            if (typeof XLSX === 'undefined') {
                showAlert('error', 'Excel library not loaded. Please refresh the page.');
                return;
            }
            
            createAndDownloadTemplate(templateData);
        }

        function createAndDownloadTemplate(data) {
            const workbook = XLSX.utils.book_new();
            const worksheet = XLSX.utils.json_to_sheet(data, { skipHeader: false });
            
            // Remove the empty row, keep only headers
            const range = XLSX.utils.decode_range(worksheet['!ref']);
            range.e.r = 0; // Set end row to 0 (headers only)
            worksheet['!ref'] = XLSX.utils.encode_range(range);
            
            // Delete the empty data row cells
            for (let col = range.s.c; col <= range.e.c; col++) {
                const cellAddress = XLSX.utils.encode_cell({ r: 1, c: col });
                delete worksheet[cellAddress];
            }
            
            // Set column widths
            worksheet['!cols'] = [
                { wch: 15 },
                { wch: 12 },
                { wch: 18 },
                { wch: 35 },
                { wch: 8 },
                { wch: 18 },
                { wch: 15 },
                { wch: 20 }
            ];

            XLSX.utils.book_append_sheet(workbook, worksheet, 'Template');
            XLSX.writeFile(workbook, 'BW_Gas_Detector_Import_Template.xlsx');
        }

        // Sidebar toggle (from main theme)
        // Sidebar toggle is handled by app.js

        // Load current record count and refresh periodically
        async function loadRecordCount() {
            try {
                const response = await fetch('api/check-data.php?nocache=' + Date.now());
                const data = await response.json();
                const count = data && typeof data.total_records !== 'undefined' ? parseInt(data.total_records) : 0;
                document.getElementById('currentRecordCount').textContent = count.toLocaleString();
                return count;
            } catch (error) {
                console.error('Error loading record count:', error);
                // Keep existing count if API fails
            }
        }

        // Refresh record count every 5 seconds
        window.addEventListener('load', () => {
            setInterval(loadRecordCount, 5000);
        });

        // Show delete confirmation modal
        function showDeleteModal() {
            console.log('DELETE BUTTON CLICKED!');
            
            const countElement = document.getElementById('currentRecordCount');
            let count = '0';
            if (countElement) {
                count = countElement.textContent.replace(/,/g, '');
            }
            
            console.log('Current count:', count);
            
            // Simple confirm dialog - NO conditions, just show it
            const message = 'WARNING!\n\nAre you sure you want to DELETE ALL DATA?\n\n' + count + ' records will be permanently deleted.\n\nThis action CANNOT be undone!';
            console.log('Showing confirm dialog with message:', message);
            
            const result = confirm(message);
            console.log('Confirm result:', result);
            
            if (result === true) {
                console.log('User clicked OK - deleting now');
                deleteAllData(count);
            } else {
                console.log('User clicked Cancel');
            }
        }

        // Close delete modal
        function closeDeleteModal() {
            console.log('closeDeleteModal called');
            document.getElementById('deleteModal').classList.remove('show');
        }

        // Delete all data function
        function deleteAllData(count) {
            console.log('deleteAllData called with count:', count);
            showAlert('info', 'Deleting ' + count + ' records... Please wait.');
            
            // Make the API call
            fetch('api/delete-all-records.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => {
                console.log('Response received:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showAlert('success', 'Successfully deleted ' + data.deleted_count + ' records! Reloading...');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('error', 'Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showAlert('error', 'Error: ' + error.message);
            });
        }

        // Profile dropdown
        
        // ===== DELETE ALL DATA BUTTON HANDLER =====
        // This runs AFTER the page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('PAGE LOADED - Setting up delete button');
            const deleteBtn = document.getElementById('deleteAllBtn');
            if (deleteBtn) {
                console.log('Delete button found, adding click handler');
                deleteBtn.addEventListener('click', function(e) {
                    console.log('DELETE BUTTON CLICKED!!!');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const countElement = document.getElementById('currentRecordCount');
                    let count = '0';
                    if (countElement) {
                        count = countElement.textContent.replace(/,/g, '');
                    }
                    
                    console.log('Record count:', count);
                    
                    // Show confirmation dialog
                    const message = 'WARNING!\n\nAre you ABSOLUTELY SURE you want to DELETE ALL DATA?\n\n' + count + ' records will be permanently deleted.\n\nThis action CANNOT be undone!';
                    
                    const confirmed = window.confirm(message);
                    console.log('User confirmed:', confirmed);
                    
                    if (confirmed === true) {
                        console.log('Proceeding with deletion...');
                        deleteAllData(count);
                    }
                });
            } else {
                console.warn('Delete button NOT found!');
            }
        });
    </script>
</body>
</html>
