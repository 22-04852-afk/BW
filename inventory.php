<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php', true, 302);
    exit;
}

require_once 'db_config.php';

// Get selected dataset from URL parameter
$selected_dataset = isset($_GET['dataset']) ? trim($_GET['dataset']) : 'all';

// Build dataset filter clause for queries
$dataset_filter = "";
if ($selected_dataset !== 'all' && $selected_dataset !== '') {
    $safe_dataset = $conn->real_escape_string($selected_dataset);
    $dataset_filter = " AND dataset_name = '$safe_dataset'";
}

// Search/filter by item
$searchItem = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchQuery = '';
if ($searchItem) {
    $searchItem = $conn->real_escape_string($searchItem);
    $searchQuery = "WHERE (item_code LIKE '%{$searchItem}%' OR item_name LIKE '%{$searchItem}%')";
}

// Add dataset filter to where clause
if ($dataset_filter) {
    if (strpos($searchQuery, 'WHERE') !== false) {
        $searchQuery .= $dataset_filter;
    } else {
        $searchQuery = "WHERE 1=1$dataset_filter";
    }
}

// Get all items with stock and delivery counts
$items = [];
$result = $conn->query("
    SELECT 
        item_code,
        MAX(item_name) as item_name,
        COUNT(CASE WHEN company_name != 'Stock Addition' THEN 1 END) as delivery_count,
        COALESCE(SUM(CASE WHEN company_name != 'Stock Addition' THEN quantity ELSE 0 END), 0) as units_delivered,
        COALESCE(SUM(CASE WHEN company_name = 'Stock Addition' THEN quantity ELSE 0 END), 0) as units_added,
        COALESCE(SUM(CASE WHEN company_name = 'Stock Addition' THEN quantity ELSE 0 END), 0) - 
        COALESCE(SUM(CASE WHEN company_name != 'Stock Addition' THEN quantity ELSE 0 END), 0) as current_stock,
        MAX(CASE WHEN company_name != 'Stock Addition' THEN delivery_year || '-' || CASE 
            WHEN delivery_month = 'January' THEN '01'
            WHEN delivery_month = 'February' THEN '02'
            WHEN delivery_month = 'March' THEN '03'
            WHEN delivery_month = 'April' THEN '04'
            WHEN delivery_month = 'May' THEN '05'
            WHEN delivery_month = 'June' THEN '06'
            WHEN delivery_month = 'July' THEN '07'
            WHEN delivery_month = 'August' THEN '08'
            WHEN delivery_month = 'September' THEN '09'
            WHEN delivery_month = 'October' THEN '10'
            WHEN delivery_month = 'November' THEN '11'
            WHEN delivery_month = 'December' THEN '12'
            ELSE '00' END || '-' || printf('%02d', delivery_day) END) as last_delivery_date,
        MAX(CASE WHEN company_name != 'Stock Addition' THEN created_at END) as last_delivery_timestamp
    FROM delivery_records
    {$searchQuery}
    GROUP BY item_code
    ORDER BY item_name ASC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Calculate accurate last delivery date
        $lastDeliveryDate = null;
        if ($row['last_delivery_date']) {
            $lastDeliveryDate = $row['last_delivery_date'];
        } elseif ($row['last_delivery_timestamp']) {
            $lastDeliveryDate = date('Y-m-d', strtotime($row['last_delivery_timestamp']));
        }
        
        $items[] = [
            'code' => $row['item_code'],
            'name' => $row['item_name'],
            'stock' => intval($row['current_stock']),
            'units_added' => intval($row['units_added']),
            'units_delivered' => intval($row['units_delivered']),
            'deliveries' => intval($row['delivery_count']),
            'last_delivery' => $lastDeliveryDate
        ];
    }
}

// Get inventory stats
$totalItems = count($items);
$totalStock = array_sum(array_column($items, 'stock'));
$totalDeliveries = array_sum(array_column($items, 'deliveries'));
$negativeStockItems = array_filter($items, function($item) { return $item['stock'] < 0; });
$hasNegativeStock = count($negativeStockItems) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>(function(){if(localStorage.getItem('theme')!=='dark'){document.documentElement.classList.add('light-mode');document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('light-mode')})}})()</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <link rel="stylesheet" href="css/style.css">
    <style>
        html, body {
            overflow-x: hidden;
        }

        .inventory-container,
        .section-title,
        .page-header {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
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
            gap: 12px;
        }

        .page-title i {
            color: #f4d03f;
        }

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
            flex: 1;
            min-width: 300px;
        }

        .search-box input {
            padding: 12px 16px;
            border-radius: 8px;
            border: 2px solid rgba(244, 208, 63, 0.3);
            background: linear-gradient(145deg, rgba(30, 42, 56, 0.95), rgba(20, 30, 45, 0.98));
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            min-width: 250px;
            transition: all 0.3s ease;
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .search-box input:focus {
            outline: none;
            border-color: #f4d03f;
            box-shadow: 0 0 20px rgba(244, 208, 63, 0.2);
        }

        .search-box button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #f4d03f 0%, #f9d76a 100%);
            border: none;
            border-radius: 8px;
            color: #1a3a5c;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .search-box button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 208, 63, 0.3);
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

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: #f4d03f;
        }

        .stat-card .icon {
            font-size: 36px;
            color: #f4d03f;
            margin-bottom: 12px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .stat-card .label {
            font-size: 13px;
            color: #a0a0a0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .inventory-table-container {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            overflow-x: auto;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table thead th {
            background: rgba(47, 95, 167, 0.3);
            padding: 14px 18px;
            text-align: left;
            font-weight: 600;
            color: #fff;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f4d03f;
        }

        .inventory-table tbody td {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
            font-size: 14px;
        }

        .inventory-table tbody tr:hover {
            background: rgba(47, 95, 167, 0.15);
        }

        .item-code {
            background: rgba(244, 208, 63, 0.15);
            color: #f4d03f;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .stock-quantity {
            font-weight: 600;
            color: #2ecc71;
            font-size: 16px;
        }

        .stock-low {
            color: #ff6b6b;
        }

        .stock-warning {
            background: rgba(255, 107, 107, 0.15);
            color: #ff6b6b;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .stock-good {
            color: #2ecc71;
        }

        .delivery-info {
            font-size: 12px;
            color: #a0a0a0;
            margin-top: 4px;
        }

        .action-btn {
            padding: 8px 12px;
            background: linear-gradient(135deg, #f4d03f 0%, #f9d76a 100%);
            border: none;
            border-radius: 6px;
            color: #1a3a5c;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 208, 63, 0.3);
        }

        .action-btn i {
            font-size: 12px;
        }
            background: rgba(0, 217, 255, 0.15);
            color: #00d9ff;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
        }

        .last-delivery {
            font-size: 12px;
            color: #a0a0a0;
        }

        .no-items {
            text-align: center;
            padding: 40px 20px;
            color: #a0a0a0;
        }

        .no-items i {
            font-size: 48px;
            color: #666;
            margin-bottom: 15px;
            display: block;
        }

        .btn-submit, .btn-cancel {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            margin-right: 10px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.3);
        }

        .btn-cancel {
            background: #666;
            color: white;
        }

        .btn-cancel:hover {
            background: #777;
        }

        .modal-body {
            padding: 20px;
        }

        .init-stock-item {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            align-items: start;
            position: relative;
        }

        .init-stock-item > div:last-child {
            grid-column: 1 / -1;
            text-align: right;
            margin-top: -5px;
        }

        .init-stock-item label {
            font-size: 12px;
            color: #999;
            display: block;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .init-stock-item input {
            background: #1a3a5c;
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        .init-stock-item input:focus {
            outline: none;
            border-color: #f4d03f;
            box-shadow: 0 0 10px rgba(244, 208, 63, 0.3);
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
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            margin: 50px auto;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(244, 208, 63, 0.3);
        }

        .modal-header h2 {
            color: #fff;
            font-size: 20px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-header h2 i {
            color: #f4d03f;
        }

        .close-btn {
            background: none;
            border: none;
            color: #a0a0a0;
            font-size: 28px;
            cursor: pointer;
            transition: color 0.3s;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            color: #fff;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #a0a0a0;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid rgba(244, 208, 63, 0.3);
            background: linear-gradient(145deg, rgba(30, 42, 56, 0.95), rgba(20, 30, 45, 0.98));
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #f4d03f;
            box-shadow: 0 0 15px rgba(244, 208, 63, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .form-actions button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-submit {
            background: linear-gradient(135deg, #f4d03f 0%, #f9d76a 100%);
            color: #1a3a5c;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 208, 63, 0.3);
        }

        .btn-cancel {
            background: rgba(100, 100, 100, 0.5);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-cancel:hover {
            background: rgba(100, 100, 100, 0.7);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.15);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: #2ecc71;
        }

        .alert-error {
            background: rgba(255, 107, 107, 0.15);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ff6b6b;
        }

        .add-stock-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #f4d03f 0%, #f9d76a 100%);
            border: none;
            border-radius: 8px;
            color: #1a3a5c;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-stock-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(244, 208, 63, 0.3);
        }

        /* Light Mode Modal */
        html.light-mode .modal-content,
        body.light-mode .modal-content {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid #c5ddf0;
        }

        html.light-mode .modal-header h2,
        body.light-mode .modal-header h2 {
            color: #1a3a5c;
        }

        html.light-mode .modal-header,
        body.light-mode .modal-header {
            border-bottom: 2px solid rgba(30, 136, 229, 0.2);
        }

        html.light-mode .form-group label,
        body.light-mode .form-group label {
            color: #5a6a7a;
        }

        html.light-mode .form-group input,
        body.light-mode .form-group input,
        html.light-mode .form-group textarea,
        body.light-mode .form-group textarea {
            background: linear-gradient(145deg, #ffffff, #f0f7ff);
            border: 2px solid rgba(30, 136, 229, 0.3);
            color: #1a3a5c;
        }

        html.light-mode .form-group input::placeholder,
        body.light-mode .form-group input::placeholder,
        html.light-mode .form-group textarea::placeholder,
        body.light-mode .form-group textarea::placeholder {
            color: rgba(0, 0, 0, 0.4);
        }

        html.light-mode .form-group input:focus,
        body.light-mode .form-group input:focus,
        html.light-mode .form-group textarea:focus,
        body.light-mode .form-group textarea:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 15px rgba(30, 136, 229, 0.2);
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 100px auto;
                padding: 20px;
            }

            .modal-header h2 {
                font-size: 18px;
            }
        }
        html.light-mode .page-title,
        body.light-mode .page-title,
        html.light-mode .section-title,
        body.light-mode .section-title {
            color: #1a3a5c;
        }

        html.light-mode .stat-card,
        body.light-mode .stat-card,
        html.light-mode .inventory-table-container,
        body.light-mode .inventory-table-container {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 1px solid #c5ddf0;
        }

        html.light-mode .stat-card .value,
        body.light-mode .stat-card .value {
            color: #1a3a5c;
        }

        html.light-mode .stat-card .label,
        body.light-mode .stat-card .label {
            color: #5a6a7a;
        }

        html.light-mode .inventory-table thead th,
        body.light-mode .inventory-table thead th {
            background: rgba(30, 136, 229, 0.1);
            color: #1a3a5c;
            border-bottom: 2px solid #1e88e5;
        }

        html.light-mode .inventory-table tbody td,
        body.light-mode .inventory-table tbody td {
            color: #333;
            border-bottom: 1px solid #e0e0e0;
        }

        html.light-mode .inventory-table tbody tr:hover,
        body.light-mode .inventory-table tbody tr:hover {
            background: rgba(30, 136, 229, 0.05);
        }

        html.light-mode .item-code,
        body.light-mode .item-code {
            background: rgba(30, 136, 229, 0.15);
            color: #1e88e5;
        }

        html.light-mode .stock-quantity,
        body.light-mode .stock-quantity {
            color: #2ecc71;
        }

        html.light-mode .delivery-count,
        body.light-mode .delivery-count {
            background: rgba(30, 136, 229, 0.15);
            color: #1e88e5;
        }

        html.light-mode .last-delivery,
        body.light-mode .last-delivery {
            color: #5a6a7a;
        }

        html.light-mode .search-box input,
        body.light-mode .search-box input {
            background: linear-gradient(145deg, #ffffff, #f0f7ff);
            border: 2px solid rgba(30, 136, 229, 0.3);
            color: #1a3a5c;
        }

        html.light-mode .search-box input::placeholder,
        body.light-mode .search-box input::placeholder {
            color: rgba(0, 0, 0, 0.4);
        }

        html.light-mode .search-box input:focus,
        body.light-mode .search-box input:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 20px rgba(30, 136, 229, 0.2);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 992px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-box {
                width: 100%;
                flex-direction: column;
                min-width: unset;
            }

            .search-box input {
                width: 100%;
                min-width: unset;
            }

            .stats-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-card .value {
                font-size: 26px;
            }

            .inventory-table thead th,
            .inventory-table tbody td {
                padding: 12px 10px;
                font-size: 12px;
            }

            .add-stock-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 24px;
            }

            .stats-cards {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }

            .stat-card {
                padding: 15px;
            }

            .stat-card .icon {
                font-size: 28px;
            }

            .stat-card .value {
                font-size: 22px;
            }

            .stat-card .label {
                font-size: 11px;
            }

            .page-header {
                margin-bottom: 20px;
            }

            .inventory-table thead th,
            .inventory-table tbody td {
                padding: 10px 8px;
                font-size: 11px;
            }

            .section-title {
                font-size: 18px;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 20px;
            }

            .stat-card {
                padding: 15px;
                display: flex;
                align-items: center;
                gap: 12px;
                text-align: left;
            }

            .stat-card .icon {
                font-size: 24px;
            }

            .inventory-table {
                font-size: 12px;
            }

            .inventory-table thead th,
            .inventory-table tbody td {
                padding: 8px 6px;
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

                    <li class="menu-item active">
                        <a href="inventory.php" class="menu-link">
                            <i class="fas fa-boxes"></i>
                            <span class="menu-label">Inventory</span>
                        </a>
                    </li>

                    <li class="menu-item">
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-boxes"></i>
                    Inventory
                </h1>
                <div class="search-box">
                    <form method="get" style="display: flex; gap: 10px; width: 100%; align-items: center;">
                        <?php if ($selected_dataset !== 'all'): ?>
                        <input type="hidden" name="dataset" value="<?php echo htmlspecialchars($selected_dataset); ?>">
                        <?php endif; ?>
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search by item code or name..." 
                            value="<?php echo htmlspecialchars($searchItem); ?>"
                        >
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                        <?php if ($searchItem): ?>
                            <a href="inventory.php<?php echo $selected_dataset !== 'all' ? '?dataset=' . urlencode($selected_dataset) : ''; ?>" style="padding: 12px 24px; background: #666; border-radius: 8px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <button class="add-stock-btn" onclick="openAddStockModal()">
                    <i class="fas fa-plus"></i> Add Stock
                </button>
            </div>
            
            <!-- Dataset Indicator Banner -->
            <div style="background: linear-gradient(90deg, #2a3f5f 0%, #1e2a38 100%); border-left: 4px solid #f4d03f; padding: 12px 16px; margin-bottom: 20px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-database" style="color: #f4d03f; font-size: 14px;"></i>
                <span style="color: #8a9ab5; font-size: 12px;">Current Dataset:</span>
                <strong style="color: #fff; font-size: 13px;"><?php echo $selected_dataset === 'all' ? 'ALL DATA' : htmlspecialchars(strtoupper($selected_dataset)); ?></strong>
                <?php if ($selected_dataset !== 'all'): ?>
                <a href="inventory.php" style="margin-left: auto; color: #f4d03f; font-size: 12px; text-decoration: none; opacity: 0.8; transition: opacity .2s;" title="View all datasets">
                    <i class="fas fa-times-circle"></i> Clear
                </a>
                <?php endif; ?>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-box"></i></div>
                    <div class="value"><?php echo $totalItems; ?></div>
                    <div class="label">Total Items</div>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-warehouse"></i></div>
                    <div class="value"><?php echo number_format($totalStock); ?></div>
                    <div class="label">Total Stock</div>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-dolly"></i></div>
                    <div class="value"><?php echo number_format($totalDeliveries); ?></div>
                    <div class="label">Total Deliveries</div>
                </div>
            </div>

            <!-- Inventory Table -->
            <h2 class="section-title">
                <i class="fas fa-table"></i>
                Item Inventory Details
            </h2>

            <div class="inventory-table-container">
                <?php if (empty($items)): ?>
                    <div class="no-items">
                        <i class="fas fa-inbox"></i>
                        <p>No items found</p>
                    </div>
                <?php else: ?>
                    <table class="inventory-table">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Current Stock</th>
                                <th>Delivered</th>
                                <th>Last Delivery</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $lastDate = $item['last_delivery'] ? date('M d, Y', strtotime($item['last_delivery'])) : 'N/A';
                                $stockStatus = $item['stock'] <= 20 ? 'stock-low' : 'stock-good';
                                $stockWarning = $item['stock'] <= 20 ? '<div class="stock-warning">⚠ Low Stock</div>' : '';
                            ?>
                            <tr>
                                <td><span class="item-code"><?php echo htmlspecialchars($item['code']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>
                                    <span class="stock-quantity <?php echo $stockStatus; ?>"><?php echo number_format($item['stock']); ?></span>
                                    <?php echo $stockWarning; ?>
                                </td>
                                <td><span class="delivery-count"><?php echo $item['deliveries'] ?? 0; ?></span></td>
                                <td><span class="last-delivery"><?php echo $lastDate; ?></span></td>
                                <td>
                                    <button class="action-btn" onclick="openAddStockModal('<?php echo htmlspecialchars($item['code']); ?>', '<?php echo htmlspecialchars($item['name']); ?>')">
                                        <i class="fas fa-plus"></i> Add Stock
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Initialize Inventory Modal -->
    <div id="initializeModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2><i class="fas fa-inbox"></i> Fix Negative Stock</h2>
                <button class="close-btn" onclick="closeInitializeModal()">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <p style="color: #999; margin-bottom: 20px;">
                    <i class="fas fa-info-circle"></i> These items have negative stock because they were delivered before being added to inventory. 
                    Set the actual quantity you have in stock now:
                </p>
                <div id="negativeStockItems"></div>
            </div>
            <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <button class="btn-submit" onclick="submitInitializeStock()">
                    <i class="fas fa-check"></i> Add Initial Stock
                </button>
                <button class="btn-cancel" onclick="closeInitializeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Add Stock Modal -->
    <div id="addStockModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Add Stock</h2>
                <button class="close-btn" onclick="closeAddStockModal()">&times;</button>
            </div>
            <div id="modalAlert" class="alert"></div>
            <form id="addStockForm" onsubmit="submitAddStock(event)">
                <div class="form-group">
                    <label for="itemCode">Item Code *</label>
                    <input 
                        type="text" 
                        id="itemCode" 
                        name="item_code" 
                        placeholder="e.g., MCX3-BC1" 
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="itemName">Item Name *</label>
                    <input 
                        type="text" 
                        id="itemName" 
                        name="item_name" 
                        placeholder="e.g., BW Gas Detector - Model 3 BC1" 
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity *</label>
                    <input 
                        type="number" 
                        id="quantity" 
                        name="quantity" 
                        placeholder="Enter quantity" 
                        min="1" 
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        placeholder="Add any additional notes..."
                    ></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check"></i> Add Stock
                    </button>
                    <button type="button" class="btn-cancel" onclick="closeAddStockModal()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js" defer></script>
    <script>
        // Initialize Stock Modal
        function openInitializeModal() {
            const modal = document.getElementById('initializeModal');
            const container = document.getElementById('negativeStockItems');
            
            // Get items data from PHP
            const items = <?php echo json_encode(array_values($negativeStockItems)); ?>;
            
            let html = '';
            items.forEach(item => {
                // Suggest just enough to cover the negative (bring to 0)
                // User can adjust it to their actual stock level
                const suggested = Math.abs(item['stock']);
                html += `
                    <div class="init-stock-item">
                        <div>
                            <label>Item Code</label>
                            <input type="text" value="${item['code']}" readonly style="background: #2a3f5f;">
                        </div>
                        <div>
                            <label>Actual Stock You Have (units)</label>
                            <input type="number" id="qty_${item['code']}" placeholder="E.g., ${suggested}" min="0" required>
                        </div>
                        <div style="font-size: 12px; color: #999;">
                            Current: <span style="color: #ff6b6b; font-weight: bold;">${item['stock']}</span>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            modal.style.display = 'block';
        }

        function closeInitializeModal() {
            document.getElementById('initializeModal').style.display = 'none';
        }

        function submitInitializeStock() {
            const items = <?php echo json_encode(array_values($negativeStockItems)); ?>;
            const stockData = [];
            
            items.forEach(item => {
                const qty = parseInt(document.getElementById('qty_' + item['code']).value) || 0;
                if (qty > 0) {
                    stockData.push({
                        item_code: item['code'],
                        item_name: item['name'],
                        quantity: qty
                    });
                }
            });
            
            if (stockData.length === 0) {
                alert('Please enter at least one quantity');
                return;
            }
            
            // Submit via hidden form
            const formData = new FormData();
            formData.append('action', 'bulk_initialize');
            formData.append('items', JSON.stringify(stockData));
            
            fetch('api/initialize-stock.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✓ Added initial stock to ${data.count} items!`);
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding stock');
            });
        }

        // Modal functions
        function openAddStockModal(itemCode, itemName) {
            document.getElementById('itemCode').value = itemCode || '';
            document.getElementById('itemName').value = itemName || '';
            document.getElementById('quantity').value = '';
            document.getElementById('notes').value = '';
            document.getElementById('addStockModal').style.display = 'block';
            document.getElementById('modalAlert').style.display = 'none';
        }

        function closeAddStockModal() {
            document.getElementById('addStockModal').style.display = 'none';
            document.getElementById('addStockForm').reset();
            document.getElementById('modalAlert').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addStockModal');
            if (event.target === modal) {
                closeAddStockModal();
            }
        }

        // Submit add stock form
        function submitAddStock(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('addStockForm'));
            const alertDiv = document.getElementById('modalAlert');

            fetch('api/add-stock.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertDiv.className = 'alert alert-success';
                    alertDiv.innerHTML = `<i class="fas fa-check-circle"></i> Stock added successfully! Refreshing...`;
                    alertDiv.style.display = 'block';
                    
                    // Refresh page after 1.5 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alertDiv.className = 'alert alert-error';
                    alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.error || 'Error adding stock'}`;
                    alertDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alertDiv.className = 'alert alert-error';
                alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error adding stock`;
                alertDiv.style.display = 'block';
            });
        }

        // Hamburger menu toggle
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        
        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // Profile dropdown
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');
        
        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('active');
            });

            document.addEventListener('click', function() {
                profileMenu.classList.remove('active');
            });
        }
    </script>
</body>
</html>
