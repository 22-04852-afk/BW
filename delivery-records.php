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
$result = $conn->query("SELECT * FROM delivery_records ORDER BY id DESC LIMIT 100");
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
                        <th>Action</th>
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
                    <tr>
                        <td><?php echo htmlspecialchars($record['serial_no'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['invoice_no'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['item_code'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['item_name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($record['quantity'] ?? '0'); ?></td>
                        <td><?php echo htmlspecialchars($delivery_date); ?></td>
                        <td><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($record['status'] ?? 'Delivered'); ?></span></td>
                        <td><a href="#" class="view-btn" onclick="openModal(event, '<?php echo htmlspecialchars($record['serial_no'] ?? ''); ?>', '<?php echo htmlspecialchars($record['invoice_no'] ?? ''); ?>', '<?php echo htmlspecialchars($record['item_code'] ?? ''); ?>', '<?php echo htmlspecialchars($record['item_name'] ?? ''); ?>', '<?php echo htmlspecialchars($record['quantity'] ?? '0'); ?>', '<?php echo htmlspecialchars($delivery_date); ?>', '<?php echo htmlspecialchars($record['status'] ?? 'Delivered'); ?>')">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

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
