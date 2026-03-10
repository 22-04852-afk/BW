<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

require_once 'db_config.php';

// Get all deliveries to "to Andison Manila" with full details
$companyName = 'to Andison Manila';
$delivery_records = [];
$totalQuantity = 0;

$result = $conn->query("
    SELECT 
        id,
        invoice_no,
        delivery_date,
        delivery_month,
        delivery_day,
        delivery_year,
        item_code,
        item_name,
        quantity,
        uom,
        serial_no,
        company_name,
        sold_to_month,
        sold_to_day,
        notes as remarks,
        groupings,
        status
    FROM delivery_records
    WHERE company_name = '{$companyName}'
    ORDER BY delivery_year DESC, delivery_month DESC, delivery_day DESC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $delivery_records[] = $row;
        $totalQuantity += intval($row['quantity'] ?? 0);
    }
}

$totalDeliveries = count($delivery_records);

// Count distinct item codes
$itemCodes = array_unique(array_column($delivery_records, 'item_code'));
$totalItemTypes = count($itemCodes);
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
            width: 100%;
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
            color: #333333;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            color: #0066cc;
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
            color: #0066cc;
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
            color: #333333;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0066cc;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #0066cc;
        }

        .table-responsive {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            min-width: 1800px;
            border-collapse: collapse;
            background: #ffffff;
        }

        thead {
            background: #eeeeee;
            border-bottom: 2px solid #ffffff;
        }

        th {
            padding: 16px;
            text-align: left;
            color: #333333;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #ffffff;
            color: #333333;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        /* Column-specific widths */
        th:nth-child(1), td:nth-child(1) { min-width: 110px; } /* Invoice No */
        th:nth-child(2), td:nth-child(2) { min-width: 90px; }  /* Date */
        th:nth-child(3), td:nth-child(3) { min-width: 110px; } /* Delivery Month */
        th:nth-child(4), td:nth-child(4) { min-width: 100px; } /* Delivery Day */
        th:nth-child(5), td:nth-child(5) { min-width: 60px; }  /* Year */
        th:nth-child(6), td:nth-child(6) { min-width: 90px; }  /* Item */
        th:nth-child(7), td:nth-child(7) { min-width: 180px; } /* Description */
        th:nth-child(8), td:nth-child(8) { min-width: 70px; }  /* Qty */
        th:nth-child(9), td:nth-child(9) { min-width: 70px; }  /* UOM */
        th:nth-child(10), td:nth-child(10) { min-width: 100px; } /* Serial No */
        th:nth-child(11), td:nth-child(11) { min-width: 100px; } /* Sold To */
        th:nth-child(12), td:nth-child(12) { min-width: 100px; } /* Date Delivered */
        th:nth-child(13), td:nth-child(13) { min-width: 90px; } /* Sold To Month */
        th:nth-child(14), td:nth-child(14) { min-width: 90px; } /* Sold To Day */
        th:nth-child(15), td:nth-child(15) { min-width: 120px; } /* Remarks */
        th:nth-child(16), td:nth-child(16) { min-width: 100px; } /* Groupings */
        th:nth-child(17), td:nth-child(17) { min-width: 80px; text-align: center; } /* Action */

        .item-code {
            color: #0066cc;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .quantity {
            color: #27ae60;
            font-weight: 600;
            font-size: 16px;
        }

        .date-cell {
            color: #666666;
            font-size: 13px;
        }

        .status-delivered {
            background: rgba(39, 174, 96, 0.2);
            color: #27ae60;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #666666;
        }

        .no-data i {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .view-btn {
            padding: 6px 12px;
            background: rgba(0, 102, 204, 0.1);
            border: 1px solid rgba(0, 102, 204, 0.3);
            border-radius: 4px;
            color: #0066cc;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
        }

        .view-btn:hover {
            background: rgba(0, 102, 204, 0.2);
            border-color: rgba(0, 102, 204, 0.5);
        }

        .edit-btn {
            padding: 6px 10px;
            background: rgba(230, 126, 34, 0.1);
            border: none;
            border-radius: 4px;
            color: #e67e22;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .edit-btn:hover {
            background: rgba(230, 126, 34, 0.2);
        }

        .delete-btn {
            padding: 6px 10px;
            background: rgba(231, 76, 60, 0.1);
            border: none;
            border-radius: 4px;
            color: #e74c3c;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .delete-btn:hover {
            background: rgba(231, 76, 60, 0.2);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }

        .back-btn {
            padding: 10px 20px;
            background: #0066cc;
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
            background: #004999;
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
                        <div class="stat-value"><?php echo $totalItemTypes; ?></div>
                    </div>
                </div>

                <!-- Items Summary -->
                <div class="section-title">
                    <i class="fas fa-list"></i> Delivery Records
                </div>
                <?php if ($totalDeliveries > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice No.</th>
                                <th>Date</th>
                                <th>Delivery Month to Andison</th>
                                <th>Delivery Day to Andison</th>
                                <th>Year</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Qty.</th>
                                <th>UOM</th>
                                <th>Serial No.</th>
                                <th>Sold To</th>
                                <th>Date Delivered</th>
                                <th>Sold To Month</th>
                                <th>Sold To Day</th>
                                <th>Remarks</th>
                                <th>Groupings</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($delivery_records as $record):
                                $delivery_date = '';
                                if (!empty($record['delivery_date'])) {
                                    $delivery_date = date('M j, Y', strtotime($record['delivery_date']));
                                }
                                
                                $date_col = '';
                                if (!empty($record['delivery_date'])) {
                                    $date_col = date('m/d/Y', strtotime($record['delivery_date']));
                                }
                                
                                $sold_to_month = !empty($record['sold_to_month']) ? $record['sold_to_month'] : '';
                                $sold_to_day = !empty($record['sold_to_day']) ? $record['sold_to_day'] : '';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['invoice_no'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($date_col); ?></td>
                                <td><?php echo htmlspecialchars($record['delivery_month'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['delivery_day'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['delivery_year'] ?? ''); ?></td>
                                <td><span class="item-code"><?php echo htmlspecialchars($record['item_code'] ?? ''); ?></span></td>
                                <td><?php echo htmlspecialchars($record['item_name'] ?? ''); ?></td>
                                <td><span class="quantity"><?php echo (!empty($record['quantity']) && $record['quantity'] > 0) ? htmlspecialchars($record['quantity']) : ''; ?></span></td>
                                <td><?php echo htmlspecialchars($record['uom'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['serial_no'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['company_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($delivery_date); ?></td>
                                <td><?php echo htmlspecialchars($sold_to_month); ?></td>
                                <td><?php echo htmlspecialchars($sold_to_day); ?></td>
                                <td><?php echo htmlspecialchars($record['remarks'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['groupings'] ?? ''); ?></td>
                                <td style="text-align: center;">
                                    <div class="action-buttons">
                                        <a href="#" class="view-btn" onclick="openModal(event, <?php echo intval($record['id'] ?? 0); ?>)" title="View Record">View</a>
                                        <a href="#" class="edit-btn" onclick="openEditModal(event, <?php echo intval($record['id'] ?? 0); ?>)" title="Edit Record"><i class="fas fa-edit"></i></a>
                                        <a href="#" class="delete-btn" onclick="deleteRecord(event, <?php echo intval($record['id'] ?? 0); ?>, '<?php echo htmlspecialchars($record['serial_no'] ?? ''); ?>')" title="Delete Record"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>No delivery records found for Andison Manila</p>
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

        // Open view modal for record details
        function openModal(event, recordId) {
            event.preventDefault();
            // Redirect to delivery-records.php with the record ID
            window.location.href = 'delivery-records.php?view=' + recordId;
        }

        // Open edit modal for editing record
        function openEditModal(event, recordId) {
            event.preventDefault();
            // Redirect to delivery-records.php with the record ID for editing
            window.location.href = 'delivery-records.php?edit=' + recordId;
        }

        // Delete record
        function deleteRecord(event, recordId, serialNo) {
            event.preventDefault();
            if (confirm('Are you sure you want to delete this record? (Serial: ' + serialNo + ')')) {
                // Send delete request
                fetch('api/delete-record.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: recordId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Record deleted successfully');
                        location.reload();
                    } else {
                        alert('Error deleting record: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting record');
                });
            }
        }
    </script>
</body>
</html>
