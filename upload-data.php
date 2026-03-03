<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Data - BW Gas Detector</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <span>Addison</span>
                </div>
            </div>

            <!-- Center Title -->
            <div class="navbar-center">
                <h1 class="dashboard-title">Upload Data</h1>
            </div>

            <!-- Right Profile Section -->
            <div class="navbar-end">
                <div class="notification" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="profile-dropdown">
                    <button class="profile-btn" id="profileBtn" aria-label="Profile menu">
                        <img src="https://via.placeholder.com/40" alt="User avatar" class="profile-avatar">
                        <span class="profile-name">John Anderson</span>
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
            <p class="company-info">Addison Industrial</p>
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
                            <p>Your Excel file should contain the following columns (in any order). All fields are required:</p>
                            <div class="template-columns">
                                <span><strong>Delivery_Month</strong></span>
                                <span><strong>Delivery_Day</strong></span>
                                <span><strong>Item_Code</strong></span>
                                <span><strong>Item_Name</strong></span>
                                <span><strong>Company_Name</strong></span>
                                <span><strong>Quantity</strong></span>
                                <span><strong>Status</strong></span>
                                <span><strong>Notes</strong> (Optional)</span>
                            </div>
                            <p style="margin-top: 15px; font-style: italic;">
                                <i class="fas fa-lightbulb" style="color: #f4d03f;"></i>
                                Tip: Download the template to see the exact format expected.
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
                    Upload Excel File
                </h2>
                <p class="section-subtitle">Drag and drop your file or click to browse</p>

                <div class="upload-zone" id="uploadZone">
                    <div class="upload-icon">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <h3>Drag Excel file here</h3>
                    <p>or click to select from your computer</p>
                    <div class="upload-formats">
                        Supported formats: <strong>.xlsx, .xls, .csv</strong> (Max 10MB)
                    </div>
                    <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" />
                </div>

                <div class="file-info" id="fileInfo">
                    <div class="file-icon">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <div class="file-details">
                        <div class="file-name" id="fileName">file.xlsx</div>
                        <div class="file-size" id="fileSize">0 MB</div>
                    </div>
                    <button class="file-remove" id="fileRemove">Remove</button>
                </div>

                <div class="upload-actions">
                    <button class="btn-upload" id="uploadBtn" onclick="parseFile(selectedFile)" style="flex: 1; display: none;">
                        <i class="fas fa-upload"></i> Upload File
                    </button>
                    <button class="btn-cancel" id="cancelBtn" onclick="resetUpload()" style="flex: 1;">
                        <i class="fas fa-times"></i> Clear File
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

                <div class="upload-actions">
                    <button class="btn-import" id="importBtn">
                        <i class="fas fa-upload"></i> Import Data
                    </button>
                    <button class="btn-cancel" onclick="resetUpload()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js"></script>
    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const parseBtn = document.getElementById('parseBtn');
        const fileRemove = document.getElementById('fileRemove');
        const previewSection = document.getElementById('previewSection');
        const importBtn = document.getElementById('importBtn');
        const alertContainer = document.getElementById('alertContainer');

        let selectedFile = null;
        let parsedData = null;

        // Initialize database on page load
        window.addEventListener('load', () => {
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

        fileRemove.addEventListener('click', (e) => {
            e.stopPropagation();
            resetUpload();
        });

        function handleFiles(files) {
            if (files.length === 0) return;

            const file = files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB

            // Validation
            if (!file.name.match(/\.(xlsx|xls|csv)$/i)) {
                showAlert('error', 'Invalid file format. Please upload Excel (.xlsx, .xls) or CSV file.');
                return;
            }

            if (file.size > maxSize) {
                showAlert('error', 'File is too large. Maximum size is 10MB.');
                return;
            }

            selectedFile = file;
            updateFileInfo(file);
        }

        function updateFileInfo(file) {
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            fileInfo.classList.add('show');
            document.getElementById('uploadBtn').style.display = 'block';
        }

        parseBtn.addEventListener('click', () => {
            if (!selectedFile) return;
            parseFile(selectedFile);
        });

        function parseFile(file) {
            const reader = new FileReader();
            // Show parsing status
            showAlert('info', 'Parsing file... Please wait.');


            reader.onload = function(e) {
                try {
                    const data = e.target.result;
                    let rows = [];

                    if (file.name.endsWith('.csv')) {
                        rows = parseCSV(data);
                    } else {
                        // For Excel files, we'll use SheetJS library
                        parseExcelFile(file);
                        return;
                    }

                    displayPreview(rows);
                    showAlert('success', 'File parsed successfully! Review the data and click Import.');

                } catch (error) {
                    showAlert('error', 'Error parsing file: ' + error.message);
                }
            };

            reader.readAsText(file);
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

        function parseExcelFile(file) {
            // Load SheetJS library
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js';
            script.onload = function() {
                try {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, { type: 'array' });
                        const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                        const rows = XLSX.utils.sheet_to_json(worksheet);
                        
                        displayPreview(rows);
                        showAlert('success', 'File parsed successfully! Review the data and click Import.');
                    };
                    reader.readAsArrayBuffer(file);
                } catch (error) {
                    showAlert('error', 'Error parsing Excel file: ' + error.message);
                }
            };
            document.head.appendChild(script);
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

            if (rows.length > 10) {
                showAlert('info', `Showing first 10 rows of ${rows.length} records.`);
            }
        }

        importBtn.addEventListener('click', () => {
            if (!parsedData || parsedData.length === 0) return;

            importBtn.innerHTML = '<span class="spinner"></span> Importing...';
            importBtn.disabled = true;

            // Prepare data for import
            const importData = {
                data: parsedData,
                fileName: selectedFile.name,
                timestamp: new Date().toISOString()
            };

            // Send to backend
            fetch('api/import-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(importData)
            })
            .then(response => response.json())
            .then(result => {
                importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Data';
                importBtn.disabled = false;

                if (result.success) {
                    showAlert('success', `Successfully imported ${result.imported} records from ${selectedFile.name}`);
                    setTimeout(() => {
                        resetUpload();
                    }, 2000);
                } else {
                    showAlert('error', result.message || 'Import failed. Please try again.');
                }
            })
            .catch(error => {
                importBtn.innerHTML = '<i class="fas fa-upload"></i> Import Data';
                importBtn.disabled = false;
                showAlert('error', 'Error during import: ' + error.message);
            });
        });

        function resetUpload() {
            selectedFile = null;
            parsedData = null;
            fileInput.value = '';
            fileInfo.classList.remove('show');
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

        function downloadTemplate() {
            // Create sample template
            const templateData = [
                {
                    'Delivery_Month': 'January',
                    'Delivery_Day': '1',
                    'Item_Code': 'MCX3-BC1',
                    'Item_Name': 'BW Gas Detector - Model 3 BC1',
                    'Company_Name': 'Addison Industrial',
                    'Quantity': '10',
                    'Status': 'Delivered',
                    'Notes': 'Sample data'
                }
            ];

            // Check if SheetJS is available
            if (typeof XLSX === 'undefined') {
                // Load SheetJS
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js';
                script.onload = function() {
                    createAndDownloadTemplate(templateData);
                };
                document.head.appendChild(script);
            } else {
                createAndDownloadTemplate(templateData);
            }
        }

        function createAndDownloadTemplate(data) {
            const workbook = XLSX.utils.book_new();
            const worksheet = XLSX.utils.json_to_sheet(data);
            
            // Set column widths
            worksheet['!cols'] = [
                { wch: 15 },
                { wch: 12 },
                { wch: 12 },
                { wch: 30 },
                { wch: 25 },
                { wch: 10 },
                { wch: 15 },
                { wch: 20 }
            ];

            XLSX.utils.book_append_sheet(workbook, worksheet, 'Template');
            XLSX.writeFile(workbook, 'BW_Gas_Detector_Import_Template.xlsx');
        }

        // Sidebar toggle (from main theme)
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('sidebar-closed');
            });
        }

        // Profile dropdown
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');

        if (profileBtn) {
            profileBtn.addEventListener('click', () => {
                profileMenu.classList.toggle('active');
            });

            document.addEventListener('click', (e) => {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
