<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';

$admin_id = $_SESSION['admin_id'];
$fullname = $_SESSION['fullname'] ?? 'Admin User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
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
            line-height: 1.6;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .logo {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo h1 {
            font-size: 1.8rem;
        }
        
        .logo span {
            color: #fff;
        }
        
        .menu {
            list-style: none;
            padding: 0 15px;
        }
        
        .menu-item {
            margin-bottom: 5px;
        }
        
        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .menu-link:hover, .menu-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .menu-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .main-content {
            margin-left: 250px;
            flex: 1;
            width: calc(100% - 250px);
        }
        
        #contentFrame {
            width: 100%;
            height: 100vh;
            border: none;
            display: block;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <h1>Easy<span>Inventory</span></h1>
            </div>
            <ul class="menu">
                <li class="menu-item">
                    <a onclick="loadPage('dashboard.php')" class="menu-link active" id="nav-dashboard">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="menu-item">
                    <a onclick="loadPage('inventory.php')" class="menu-link" id="nav-inventory">
                        <i class="fas fa-box"></i> Inventory
                    </a>
                </li>
                <li class="menu-item">
                    <a onclick="loadPage('categories.php')" class="menu-link" id="nav-categories">
                        <i class="fas fa-boxes"></i> Categories
                    </a>
                </li>
                <li class="menu-item">
                    <a onclick="loadPage('suppliers.php')" class="menu-link" id="nav-suppliers">
                        <i class="fas fa-truck"></i> Suppliers
                    </a>
                </li>
                <li class="menu-item">
                    <a onclick="loadPage('customers.php')" class="menu-link" id="nav-customers">
                        <i class="fas fa-users"></i> Customers
                    </a>
                </li>
                <li class="menu-item">
                    <a onclick="loadPage('purchases.php')" class="menu-link" id="nav-purchases">
                        <i class="fas fa-truck-loading"></i> Purchases
                    </a>
                </li>
                <li class="menu-item">
                    <a onclick="loadPage('sales.php')" class="menu-link" id="nav-sales">
                        <i class="fas fa-shopping-bag"></i> Sales
                    </a>
                </li>
                <li class="menu-item">
                    <a onclick="loadPage('reports.php')" class="menu-link" id="nav-reports">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <li class="menu-item">
                    <a href="logout.php" class="menu-link" onclick="return confirm('Are you sure you want to logout?');">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <iframe id="contentFrame" src="dashboard.php" name="contentFrame"></iframe>
        </div>
    </div>

    <script>
        function loadPage(page) {
            document.getElementById('contentFrame').src = page;
            
            // Update active menu item
            document.querySelectorAll('.menu-link').forEach(link => {
                link.classList.remove('active');
            });
            event.target.closest('.menu-link').classList.add('active');
        }
    </script>
</body>
</html>