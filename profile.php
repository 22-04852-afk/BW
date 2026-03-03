<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BW Gas Detector Sales Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-card {
            background: linear-gradient(135deg, #1e2a38 0%, #2a3f5f 100%);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar-wrapper {
            position: relative;
            width: 140px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #f4d03f;
            object-fit: cover;
            background: #2f5fa7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #f4d03f;
            overflow: hidden;
            flex-shrink: 0;
        }

        .profile-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ff006e, #ff4d7d);
            border: 3px solid #13172c;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .avatar-upload-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 0, 110, 0.5);
        }

        #profilePictureInput {
            display: none;
        }

        .profile-header h1 {
            font-size: 28px;
            color: #fff;
            margin: 10px 0 5px;
        }

        .profile-header p {
            color: #a0a0a0;
            font-size: 14px;
        }

        .profile-section {
            margin-bottom: 30px;
        }

        .profile-section h3 {
            font-size: 14px;
            color: #f4d03f;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .profile-info {
            display: grid;
            gap: 15px;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .info-label {
            font-size: 12px;
            color: #a0a0a0;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            color: #e0e0e0;
            font-weight: 500;
        }

        .profile-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-action {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2f5fa7, #1e3c72);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 95, 167, 0.3);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .btn-logout {
            grid-column: 1 / -1;
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
        }

        .btn-logout:hover {
            background: rgba(255, 107, 107, 0.2);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: #13172c;
            padding: 30px;
            border-radius: 16px;
            max-width: 400px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-title {
            font-size: 20px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            color: #a0a0a0;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #e0e0e0;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #2f5fa7;
            box-shadow: 0 0 0 3px rgba(47, 95, 167, 0.1);
        }

        .modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-modal {
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-save {
            background: #f4d03f;
            color: #1e2a38;
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: #e0e0e0;
        }

        .notification {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            animation: slideIn 0.3s ease;
        }

        .notification.show {
            display: block;
        }

        .notification.success {
            background: rgba(81, 207, 102, 0.1);
            color: #51cf66;
            border: 1px solid rgba(81, 207, 102, 0.3);
        }

        .notification.error {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .profile-card {
                padding: 25px;
            }

            .profile-header h1 {
                font-size: 22px;
            }

            .profile-actions {
                grid-template-columns: 1fr;
            }

            .btn-logout {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="notification" id="notification"></div>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar-wrapper">
                    <div class="profile-avatar-large" id="profileAvatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <button class="avatar-upload-btn" id="avatarUploadBtn" type="button" title="Upload Profile Picture">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <input type="file" id="profilePictureInput" accept="image/*">
                <h1 id="profileName">User Name</h1>
                <p id="profileEmail">user@example.com</p>
            </div>

            <div class="profile-section">
                <h3>Personal Information</h3>
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">First Name</div>
                        <div class="info-value" id="firstName">—</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Last Name</div>
                        <div class="info-value" id="lastName">—</div>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h3>Account Information</h3>
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value" id="emailAddress">—</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value" id="memberSince">—</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Last Login</div>
                        <div class="info-value" id="lastLogin">—</div>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <button class="btn-action btn-edit" id="editBtn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <button class="btn-action btn-back" id="backBtn">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button class="btn-action btn-logout" id="profileLogoutBtn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <h2 class="modal-title">Edit Profile</h2>
            <form id="editForm">
                <div class="form-group">
                    <label for="editFirstName">First Name</label>
                    <input type="text" id="editFirstName" required>
                </div>
                <div class="form-group">
                    <label for="editLastName">Last Name</label>
                    <input type="text" id="editLastName" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-modal btn-save">Save Changes</button>
                    <button type="button" class="btn-modal btn-cancel" id="cancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/profile.js"></script>
</body>
</html>
