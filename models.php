<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

require_once 'db_config.php';

// Fetch all unique item_codes with their stats from delivery_records
$models = [];
$result = $conn->query("
    SELECT 
        item_code,
        MAX(item_name) as item_name,
        SUM(quantity) as total_qty,
        COUNT(*) as order_count,
        MAX(delivery_year) as last_year,
        MAX(delivery_month) as last_month
    FROM delivery_records
    WHERE item_code IS NOT NULL AND item_code != ''
    GROUP BY item_code
    ORDER BY total_qty DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $models[] = $row;
    }
}

$groupACodes = array_map('strtoupper', [
    'MCXL-XWHM-Y-NA','XT-XWHM-Y-NA','MCX3-XWHM-Y-NA','BWC2-H','BWC2R-X',
    'BWS-BC1','M020-12111-111','BWS1-Z-Y','BWC3-H','BWC2-M',
    'BWS1-HL-Y','M020-12311-111','HRR-G103009ABK-000','BWS1-XL-Y'
]);
$groupBCodes = array_map('strtoupper', [
    'SR-X2V','CRT0500003DA58','MCXL-FC1','MCXL-BC1','MC2-FPCB1',
    'SR-W-MP75C','SR-Q1-4R','CRT0500003DA34','XT-MPCB2','MCX3-MPCB',
    'REG 0.5','MCXL-MPCB1','XT-RPUMP-K1','MCX3-FC1','MCX3-BC1',
    'XT-BC1','CRT0200161DA34','REG-0.5','CRT0500003DA116','REG-1.0',
    'M0931K','HU-PCB','XT-SC1','SR-M-MC','BWS-FC1','XT-BAT-K1','SR-H-MC'
]);
$modelsA = array_values(array_filter($models, fn($m) => in_array(strtoupper($m['item_code']), $groupACodes)));
$modelsB = array_values(array_filter($models, fn($m) => in_array(strtoupper($m['item_code']), $groupBCodes)));
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
        /* ── Page title ── */
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-text-light);
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-title i { color: var(--color-accent); }

        /* ── Summary strip ── */
        .models-summary {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }
        .models-summary-item {
            background: var(--color-dark-secondary);
            border: 1px solid var(--color-border);
            border-radius: 10px;
            padding: 16px 24px;
            min-width: 140px;
        }
        .models-summary-item .ms-label {
            font-size: 11px;
            color: var(--color-text-lighter);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .models-summary-item .ms-value {
            font-size: 24px;
            font-weight: 700;
            line-height: 1;
        }
        .ms-value.accent  { color: var(--color-accent); }
        .ms-value.cyan    { color: #00d9ff; }
        .ms-value.primary { color: var(--color-text-light); }

        /* ── Group cards grid ── */
        .group-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            max-width: 680px;
        }

        /* ── Individual group card ── */
        .group-card {
            background: var(--color-dark-secondary);
            border: 1px solid var(--color-border);
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            font-family: 'Poppins', sans-serif;
        }
        .group-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.35);
        }
        .group-card.card-a:hover { border-color: #2f5fa7; }
        .group-card.card-b:hover { border-color: #0891b2; }

        .group-card-banner {
            padding: 32px 20px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            position: relative;
            overflow: hidden;
        }
        .card-a .group-card-banner {
            background: linear-gradient(135deg, #1e4fa0 0%, #2f5fa7 50%, #0090c8 100%);
        }
        .card-b .group-card-banner {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 50%, #00d9ff 100%);
        }
        .group-card-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 70% 30%, rgba(255,255,255,0.12) 0%, transparent 70%);
        }
        .group-letter {
            font-size: 56px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            letter-spacing: -2px;
            position: relative;
            text-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        .group-name {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            letter-spacing: 0.5px;
            position: relative;
        }

        .group-card-body {
            padding: 20px;
        }
        .group-kpi-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 18px;
        }
        .group-kpi {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--color-border);
            border-radius: 8px;
            padding: 10px 6px;
            text-align: center;
        }
        .group-kpi-value {
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
        }
        .card-a .group-kpi-value.v1 { color: var(--color-accent); }
        .card-a .group-kpi-value.v2 { color: #00d9ff; }
        .card-a .group-kpi-value.v3 { color: var(--color-text-light); }
        .card-b .group-kpi-value.v1 { color: var(--color-accent); }
        .card-b .group-kpi-value.v2 { color: #00d9ff; }
        .card-b .group-kpi-value.v3 { color: var(--color-text-light); }
        .group-kpi-label {
            font-size: 9px;
            color: var(--color-text-lighter);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .group-card-cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            color: var(--color-text-lighter);
            padding-top: 14px;
            border-top: 1px solid var(--color-border);
            transition: color 0.2s;
        }
        .group-card:hover .group-card-cta { color: var(--color-accent); }
        .group-card-cta i { font-size: 11px; transition: transform 0.2s; }
        .group-card:hover .group-card-cta i { transform: translateX(3px); }

        /* ── Modal ── */
        .gmodal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.72);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            backdrop-filter: blur(2px);
        }
        .gmodal-box {
            background: var(--color-dark-secondary);
            border: 1px solid var(--color-border);
            border-radius: 14px;
            width: 100%;
            max-width: 820px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 24px 64px rgba(0,0,0,0.55);
            font-family: 'Poppins', sans-serif;
        }
        .gmodal-header {
            padding: 22px 28px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .gmodal-title {
            font-size: 17px;
            font-weight: 600;
            color: var(--color-text-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .gmodal-title .badge-a { background: rgba(47,95,167,0.25); color: #6ea8fe; }
        .gmodal-title .badge-b { background: rgba(124,58,237,0.25); color: #c084fc; }
        .gmodal-title .group-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .gmodal-close {
            background: none;
            border: none;
            color: var(--color-text-lighter);
            font-size: 18px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            line-height: 1;
            transition: background 0.15s, color 0.15s;
        }
        .gmodal-close:hover {
            background: rgba(255,255,255,0.08);
            color: var(--color-text-light);
        }
        .gmodal-search {
            padding: 14px 28px;
            border-bottom: 1px solid var(--color-border);
            flex-shrink: 0;
        }
        .gmodal-search input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--color-border);
            border-radius: 7px;
            padding: 10px 14px 10px 38px;
            color: var(--color-text-light);
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            outline: none;
            box-sizing: border-box;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 14px center;
            transition: border-color 0.2s;
        }
        .gmodal-search input:focus { border-color: var(--color-primary); }
        .gmodal-table-wrap {
            overflow-y: auto;
            flex: 1;
            padding: 0 28px 16px;
        }
        .gmodal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .gmodal-table thead th {
            font-size: 11px;
            color: var(--color-text-lighter);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 10px 10px 0;
            border-bottom: 1px solid var(--color-border);
            position: sticky;
            top: 0;
            background: var(--color-dark-secondary);
            white-space: nowrap;
        }
        .gmodal-table thead th:last-child,
        .gmodal-table thead th:nth-last-child(2) { text-align: right; padding-right: 0; }
        .gmodal-table tbody tr {
            border-bottom: 1px solid rgba(255,255,255,0.04);
            transition: background 0.15s;
        }
        .gmodal-table tbody tr:hover { background: rgba(255,255,255,0.03); }
        .gmodal-table td {
            padding: 13px 10px 13px 0;
            vertical-align: middle;
        }
        .gmodal-table td:last-child,
        .gmodal-table td:nth-last-child(1) { text-align: right; padding-right: 0; }
        .td-num  { color: rgba(255,255,255,0.25); font-size: 12px; width: 32px; }
        .td-code { color: var(--color-accent); font-weight: 600; font-size: 14px; }
        .td-name { color: var(--color-text-lighter); font-size: 13px; }
        .td-units { color: #00d9ff; font-weight: 700; text-align: right !important; font-size: 14px; }
        .td-orders { color: var(--color-text-light); text-align: right !important; padding-right: 4px !important; font-size: 14px; }
        .gmodal-footer {
            padding: 13px 28px;
            border-top: 1px solid var(--color-border);
            font-size: 12px;
            color: var(--color-text-lighter);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            gap: 12px;
        }
        .gmodal-footer strong { color: var(--color-text-light); }
        .gmodal-empty {
            display: none;
            text-align: center;
            padding: 48px 20px;
            color: var(--color-text-lighter);
        }
        .gmodal-empty i { display: block; font-size: 28px; margin-bottom: 10px; opacity: 0.35; }

        /* ── Light mode adjustments ── */
        html.light-mode .group-card,
        body.light-mode .group-card {
            background: #ffffff;
            border-color: #dde2ec;
        }
        html.light-mode .group-kpi,
        body.light-mode .group-kpi {
            background: rgba(0,0,0,0.03);
            border-color: #dde2ec;
        }
        html.light-mode .group-kpi-label,
        body.light-mode .group-kpi-label { color: #5a7a9a; }
        html.light-mode .group-card-cta,
        body.light-mode .group-card-cta { color: #5a7a9a; border-color: #dde2ec; }
        html.light-mode .gmodal-box,
        body.light-mode .gmodal-box { background: #ffffff; border-color: #dde2ec; }
        html.light-mode .gmodal-header,
        html.light-mode .gmodal-search,
        html.light-mode .gmodal-footer,
        body.light-mode .gmodal-header,
        body.light-mode .gmodal-search,
        body.light-mode .gmodal-footer { border-color: #dde2ec; }
        html.light-mode .gmodal-title,
        body.light-mode .gmodal-title { color: #1a3a5c; }
        html.light-mode .gmodal-search input,
        body.light-mode .gmodal-search input {
            background: #f4f8fc;
            border-color: #b8d4e8;
            color: #1a3a5c;
        }
        html.light-mode .gmodal-table thead,
        body.light-mode .gmodal-table thead { background: #ffffff; }
        html.light-mode .gmodal-table thead th,
        body.light-mode .gmodal-table thead th { background: #ffffff; border-color: #dde2ec; }
        html.light-mode .gmodal-table tbody tr:hover,
        body.light-mode .gmodal-table tbody tr:hover { background: #f0f7ff; }
        html.light-mode .td-name,
        body.light-mode .td-name { color: #4a6a8a; }
        html.light-mode .td-orders,
        body.light-mode .td-orders { color: #1a3a5c; }
        html.light-mode .models-summary-item,
        body.light-mode .models-summary-item { background: #fff; border-color: #dde2ec; }
        html.light-mode .models-summary-item .ms-label,
        body.light-mode .models-summary-item .ms-label { color: #5a7a9a; }

        @media (max-width: 600px) {
            .group-cards-grid { max-width: 100%; grid-template-columns: 1fr; }
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
                    <a href="index.php" style="display:flex;align-items:center;">
                        <img src="assets/logo.png" alt="Andison" style="height:48px;width:auto;object-fit:contain;">
                    </a>
                </div>
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

                <!-- Models -->
                <li class="menu-item active">
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

        <?php
        $totalUnits  = array_sum(array_column($models, 'total_qty'));
        $totalOrders = array_sum(array_column($models, 'order_count'));
        $totalQtyA   = array_sum(array_column($modelsA, 'total_qty'));
        $ordersA     = array_sum(array_column($modelsA, 'order_count'));
        $totalQtyB   = array_sum(array_column($modelsB, 'total_qty'));
        $ordersB     = array_sum(array_column($modelsB, 'order_count'));
        ?>

        <!-- Summary Strip -->
        <div class="models-summary">
            <div class="models-summary-item">
                <div class="ms-label">Total Models</div>
                <div class="ms-value accent"><?php echo count($models); ?></div>
            </div>
            <div class="models-summary-item">
                <div class="ms-label">Units Delivered</div>
                <div class="ms-value cyan"><?php echo number_format($totalUnits); ?></div>
            </div>
            <div class="models-summary-item">
                <div class="ms-label">Total Orders</div>
                <div class="ms-value primary"><?php echo number_format($totalOrders); ?></div>
            </div>
        </div>

        <!-- Group Cards -->
        <div class="group-cards-grid">

            <!-- Group A -->
            <div class="group-card card-a" onclick="openModal('A')">
                <div class="group-card-banner">
                    <div class="group-letter">A</div>
                    <div class="group-name">Group A &mdash; Standard Series</div>
                </div>
                <div class="group-card-body">
                    <div class="group-kpi-row">
                        <div class="group-kpi">
                            <div class="group-kpi-value v1"><?php echo count($modelsA); ?></div>
                            <div class="group-kpi-label">Models</div>
                        </div>
                        <div class="group-kpi">
                            <div class="group-kpi-value v2"><?php echo number_format($totalQtyA); ?></div>
                            <div class="group-kpi-label">Units</div>
                        </div>
                        <div class="group-kpi">
                            <div class="group-kpi-value v3"><?php echo number_format($ordersA); ?></div>
                            <div class="group-kpi-label">Orders</div>
                        </div>
                    </div>
                    <div class="group-card-cta">
                        <span>View products</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>

            <!-- Group B -->
            <div class="group-card card-b" onclick="openModal('B')">
                <div class="group-card-banner">
                    <div class="group-letter">B</div>
                    <div class="group-name">Group B &mdash; Advanced Series</div>
                </div>
                <div class="group-card-body">
                    <div class="group-kpi-row">
                        <div class="group-kpi">
                            <div class="group-kpi-value v1"><?php echo count($modelsB); ?></div>
                            <div class="group-kpi-label">Models</div>
                        </div>
                        <div class="group-kpi">
                            <div class="group-kpi-value v2"><?php echo number_format($totalQtyB); ?></div>
                            <div class="group-kpi-label">Units</div>
                        </div>
                        <div class="group-kpi">
                            <div class="group-kpi-value v3"><?php echo number_format($ordersB); ?></div>
                            <div class="group-kpi-label">Orders</div>
                        </div>
                    </div>
                    <div class="group-card-cta">
                        <span>View products</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Group Products Modal -->
    <div class="gmodal-overlay" id="groupModal" onclick="if(event.target===this)closeModal()">
        <div class="gmodal-box">
            <div class="gmodal-header">
                <div class="gmodal-title" id="modalTitle"></div>
                <button class="gmodal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="gmodal-search">
                <input type="text" id="modalSearch" placeholder="Search item code or name..." oninput="filterModal(this.value)">
            </div>
            <div class="gmodal-table-wrap">
                <table class="gmodal-table">
                    <thead>
                        <tr>
                            <th style="width:28px;">#</th>
                            <th>Item Code</th>
                            <th>Name</th>
                            <th style="text-align:right;">Units</th>
                            <th style="text-align:right;">Orders</th>
                        </tr>
                    </thead>
                    <tbody id="modalBody"></tbody>
                </table>
                <div class="gmodal-empty" id="modalEmpty">
                    <i class="fas fa-search"></i>No items found.
                </div>
            </div>
            <div class="gmodal-footer" id="modalFooter"></div>
        </div>
    </div>

    <script>
        const groupData = {
            A: <?php echo json_encode($modelsA); ?>,
            B: <?php echo json_encode($modelsB); ?>
        };
        let currentGroup = null;

        function openModal(group) {
            currentGroup = group;
            const data = groupData[group];
            const badge = group === 'A'
                ? `<span class="group-badge badge-a">Group A</span>`
                : `<span class="group-badge badge-b">Group B</span>`;
            document.getElementById('modalTitle').innerHTML = badge + ' &nbsp;' + data.length + ' model' + (data.length !== 1 ? 's' : '');
            document.getElementById('modalSearch').value = '';
            renderModalRows(data);
            document.getElementById('groupModal').style.display = 'flex';
        }

        function renderModalRows(data) {
            const tbody = document.getElementById('modalBody');
            const empty = document.getElementById('modalEmpty');
            const footer = document.getElementById('modalFooter');
            empty.style.display = data.length === 0 ? 'block' : 'none';
            if (data.length === 0) { tbody.innerHTML = ''; footer.innerHTML = ''; return; }
            tbody.innerHTML = data.map((m, i) => `
                <tr>
                    <td class="td-num">${i + 1}</td>
                    <td class="td-code">${m.item_code}</td>
                    <td class="td-name">${m.item_name || '&mdash;'}</td>
                    <td class="td-units">${parseInt(m.total_qty).toLocaleString()}</td>
                    <td class="td-orders">${parseInt(m.order_count).toLocaleString()}</td>
                </tr>
            `).join('');
            const total = data.reduce((s, m) => s + parseInt(m.total_qty), 0);
            footer.innerHTML = `Showing <strong>${data.length}</strong> item${data.length !== 1 ? 's' : ''} &nbsp;&middot;&nbsp; Total units: <strong style="color:#00d9ff;">${total.toLocaleString()}</strong>`;
        }

        function filterModal(query) {
            const q = query.toLowerCase();
            const filtered = groupData[currentGroup].filter(m =>
                m.item_code.toLowerCase().includes(q) || (m.item_name || '').toLowerCase().includes(q)
            );
            renderModalRows(filtered);
        }

        function closeModal() {
            document.getElementById('groupModal').style.display = 'none';
        }

        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
    </script>
    <script src="js/app.js" defer></script>
</body>
</html>

