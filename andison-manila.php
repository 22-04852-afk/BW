<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

require_once 'db_config.php';

// Get all deliveries to "to Andison Manila"
$companyName = 'to Andison Manila';
$result = $conn->query("
    SELECT 
        item_code,
        item_name,
        quantity,
        delivery_year,
        delivery_month,
        delivery_day,
        status,
        notes,
        created_at
    FROM delivery_records
    WHERE company_name = '{$companyName}'
    AND company_name != 'Stock Addition'
    ORDER BY delivery_year DESC, delivery_month DESC, delivery_day DESC
");

$deliveries = [];
$totalQuantity = 0;
$itemTypes = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $deliveries[] = $row;
        $totalQuantity += intval($row['quantity']);
        
        if (!isset($itemTypes[$row['item_code']])) {
            $itemTypes[$row['item_code']] = [
                'name' => $row['item_name'],
                'total' => 0,
                'deliveries' => 0
            ];
        }
        $itemTypes[$row['item_code']]['total'] += intval($row['quantity']);
        $itemTypes[$row['item_code']]['deliveries']++;
    }
}

$totalDeliveries = count($deliveries);
$totalItems = count($itemTypes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')!=='dark'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andison Manila Deliveries - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .andison-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            color: #f4d03f;
            font-size: 32px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #f4d03f;
            margin: 10px 0;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f4d03f;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #f4d03f;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
        }

        thead {
            background: rgba(244, 208, 63, 0.1);
            border-bottom: 2px solid #f4d03f;
        }

        th {
            padding: 15px;
            text-align: left;
            color: #f4d03f;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
            font-size: 14px;
        }

        tbody tr:hover {
            background: rgba(244, 208, 63, 0.05);
        }

        .item-code {
            color: #00d9ff;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .quantity {
            color: #2ecc71;
            font-weight: 600;
            font-size: 16px;
        }

        .date-cell {
            color: #999;
            font-size: 13px;
        }

        .status-delivered {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .no-data i {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .back-btn {
            padding: 10px 20px;
            background: #666;
            border: none;
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #777;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- TOP NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
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

            <div class="navbar-end">
                <div class="profile-dropdown">
                    <button type="button" class="profile-btn" id="profileBtn" aria-label="Profile menu">
                        <span class="profile-name"><?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="profileMenu">
                        <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li class="menu-item">
                        <a href="index.php" class="menu-link">
                            <i class="fas fa-chart-line"></i>
                            <span class="menu-label">Dashboard</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="sales-overview.php" class="menu-link">
                            <i class="fas fa-chart-pie"></i>
                            <span class="menu-label">Sales Overview</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="sales-records.php" class="menu-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="menu-label">Sales Records</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="delivery-records.php" class="menu-link">
                            <i class="fas fa-truck"></i>
                            <span class="menu-label">Delivery Records</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="inventory.php" class="menu-link">
                            <i class="fas fa-boxes"></i>
                            <span class="menu-label">Inventory</span>
                        </a>
                    </li>

                    <li class="menu-item active">
                        <a href="andison-manila.php" class="menu-link">
                            <i class="fas fa-truck-fast"></i>
                            <span class="menu-label">Andison Manila</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="client-companies.php" class="menu-link">
                            <i class="fas fa-building"></i>
                            <span class="menu-label">Client Companies</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="models.php" class="menu-link">
                            <i class="fas fa-cube"></i>
                            <span class="menu-label">Models</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="reports.php" class="menu-link">
                            <i class="fas fa-file-alt"></i>
                            <span class="menu-label">Reports</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="upload-data.php" class="menu-link">
                            <i class="fas fa-upload"></i>
                            <span class="menu-label">Upload Data</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="settings.php" class="menu-link">
                            <i class="fas fa-cog"></i>
                            <span class="menu-label">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <p class="company-info">Andison Industrial</p>
                <p class="company-year">© 2025</p>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content" id="mainContent">
            <div class="andison-container">
                <div class="page-header">
                    <div class="page-title">
                        <i class="fas fa-truck-fast"></i>
                        Andison Manila Deliveries
                    </div>
                    <a href="javascript:history.back()" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Deliveries</div>
                        <div class="stat-value"><?php echo $totalDeliveries; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Units</div>
                        <div class="stat-value"><?php echo number_format($totalQuantity); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Item Types</div>
                        <div class="stat-value"><?php echo $totalItems; ?></div>
                    </div>
                </div>

                <!-- Items Summary -->
                <div class="section-title">
                    <i class="fas fa-boxes"></i> Items Delivered (Summary)
                </div>
                <?php if ($totalItems > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Total Units</th>
                                <th>Deliveries</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemTypes as $code => $item): ?>
                            <tr>
                                <td><span class="item-code"><?php echo htmlspecialchars($code); ?></span></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><span class="quantity"><?php echo number_format($item['total']); ?></span></td>
                                <td><?php echo $item['deliveries']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>No deliveries found</p>
                </div>
                <?php endif; ?>

                <!-- Delivery History -->
                <div class="section-title">
                    <i class="fas fa-history"></i> Delivery History
                </div>
                <?php if ($totalDeliveries > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deliveries as $delivery): 
                                $dateFormatted = "{$delivery['delivery_month']} {$delivery['delivery_day']}, {$delivery['delivery_year']}";
                            ?>
                            <tr>
                                <td><span class="date-cell"><?php echo htmlspecialchars($dateFormatted); ?></span></td>
                                <td><span class="item-code"><?php echo htmlspecialchars($delivery['item_code']); ?></span></td>
                                <td><?php echo htmlspecialchars(substr($delivery['item_name'], 0, 40)); ?></td>
                                <td><span class="quantity"><?php echo number_format($delivery['quantity']); ?></span></td>
                                <td><span class="status-delivered"><?php echo htmlspecialchars($delivery['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($delivery['notes'] ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>No delivery history found</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="js/app.js" defer></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('hamburgerBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        });

        // Profile dropdown toggle
        document.getElementById('profileBtn').addEventListener('click', function() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const profileBtn = document.getElementById('profileBtn');
            const profileMenu = document.getElementById('profileMenu');
            if (!event.target.closest('.profile-dropdown')) {
                profileMenu.style.display = 'none';
            }
        });

        // Theme toggle
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = !html.classList.contains('light-mode');
            if (isDark) {
                html.classList.add('light-mode');
                document.body.classList.add('light-mode');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.remove('light-mode');
                document.body.classList.remove('light-mode');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</body>
</html>
