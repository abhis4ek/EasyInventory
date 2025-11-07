<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';

$admin_id = $_SESSION['admin_id'];
$fullname = $_SESSION['fullname'] ?? 'Admin User';
$email = $_SESSION['email'] ?? '';

// Fetch full user details
$stmt = $conn->prepare("SELECT fullname, shop_name, email FROM admin WHERE admin_id = ?");
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = '';
$message_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_fullname = trim($_POST['fullname'] ?? '');
    $new_shop_name = trim($_POST['shop_name'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    
    $errors = [];
    
    if (empty($new_fullname)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($new_shop_name)) {
        $errors[] = "Shop name is required";
    }
    
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email is already taken by another user
    if ($new_email !== $user_data['email']) {
        $check_stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email = ? AND admin_id != ?");
        $check_stmt->bind_param('si', $new_email, $admin_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "Email is already in use by another account";
        }
        $check_stmt->close();
    }
    
    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE admin SET fullname = ?, shop_name = ?, email = ? WHERE admin_id = ?");
        $update_stmt->bind_param('sssi', $new_fullname, $new_shop_name, $new_email, $admin_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['fullname'] = $new_fullname;
            $_SESSION['shop_name'] = $new_shop_name;
            $_SESSION['email'] = $new_email;
            
            $user_data['fullname'] = $new_fullname;
            $user_data['shop_name'] = $new_shop_name;
            $user_data['email'] = $new_email;
            
            $message = 'Profile updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error updating profile: ' . $update_stmt->error;
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
    <title>My Profile</title>
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
        
        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .profile-role {
            color: #7f8c8d;
            font-size: 0.9rem;
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
        
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
        
        .input-icon input {
            padding-left: 45px;
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
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #2196f3;
            margin-right: 8px;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 class="page-title">
                <i class="fas fa-user-circle"></i>
                My Profile
            </h2>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_data['fullname']) ?>&background=3498db&color=fff&size=120" 
                     alt="Profile" 
                     class="profile-avatar">
                <div class="profile-name"><?= htmlspecialchars($user_data['fullname']) ?></div>
                <div class="profile-role">Administrator</div>
            </div>

            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <p><i class="fas fa-info-circle"></i> <strong>Update your profile information below</strong></p>
                <p>Changes will be applied immediately across the system.</p>
            </div>

            <form method="POST" action="profile.php">
                <div class="form-group">
                    <label for="fullname">Full Name <span class="required">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               id="fullname" 
                               name="fullname" 
                               value="<?= htmlspecialchars($user_data['fullname']) ?>" 
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="shop_name">Shop Name <span class="required">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-store"></i>
                        <input type="text" 
                               id="shop_name" 
                               name="shop_name" 
                               value="<?= htmlspecialchars($user_data['shop_name'] ?? '') ?>" 
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($user_data['email']) ?>" 
                               required>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>