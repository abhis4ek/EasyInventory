<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';

$admin_id = $_SESSION['admin_id'];
$message = '';
$message_type = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM admin WHERE admin_id = ?");
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!password_verify($current_password, $result['password'])) {
        $errors[] = "Current password is incorrect";
    }
    
    if (strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "New password and confirmation do not match";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
        $update_stmt->bind_param('si', $hashed_password, $admin_id);
        
        if ($update_stmt->execute()) {
            $message = 'Password changed successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error changing password';
            $message_type = 'error';
        }
        $update_stmt->close();
    } else {
        $message = implode(', ', $errors);
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e6ed;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .settings-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .settings-section {
            margin-bottom: 40px;
        }
        
        .settings-section:last-child {
            margin-bottom: 0;
        }
        
        .section-title {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .required {
            color: #e74c3c;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .password-field {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .toggle-password:hover {
            color: #3498db;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }
        
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #856404;
            margin-right: 8px;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #856404;
        }
        
        .settings-info {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .settings-info i {
            color: #2e7d32;
            margin-right: 8px;
        }
        
        .settings-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 class="page-title">
                <i class="fas fa-cog"></i>
                Settings
            </h2>
        </div>

        <div class="settings-card">
            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Password Change Section -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-lock"></i>
                    Change Password
                </h3>

                <div class="info-box">
                    <p><i class="fas fa-shield-alt"></i> <strong>Security Guidelines:</strong></p>
                    <p>• Use at least 6 characters</p>
                    <p>• Include a mix of letters, numbers, and symbols</p>
                    <p>• Don't reuse passwords from other accounts</p>
                </div>

                <form method="POST" action="settings.php">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password <span class="required">*</span></label>
                        <div class="password-field">
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('current_password')"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password <span class="required">*</span></label>
                        <div class="password-field">
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   minlength="6"
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('new_password')"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                        <div class="password-field">
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   minlength="6"
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetPasswordForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </form>
            </div>

            <!-- System Information -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    System Information
                </h3>

                <div class="settings-info">
                    <p><i class="fas fa-check-circle"></i> <strong>Easy Inventory Management System</strong></p>
                    <p>Version 1.0.0 - All systems operational</p>
                    <p>Last login: <?= date('F d, Y \a\t H:i') ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Client-side password validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New password and confirmation do not match!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>