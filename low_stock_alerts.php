<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];
$fullname = $_SESSION['fullname'] ?? 'Admin User';

// Get low stock products (stock <= 20)
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.stock, p.cost_price, p.selling_price, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.admin_id = ? AND p.stock > 0 AND p.stock <= 20
    ORDER BY p.stock ASC, p.name
");
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$low_stock = $stmt->get_result();
$stmt->close();

// Get out of stock products (stock = 0)
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.stock, p.cost_price, p.selling_price, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.admin_id = ? AND p.stock = 0
    ORDER BY p.name
");
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$out_of_stock = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Alerts</title>
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
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e6ed;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: #2c3e50;
        }
        
        .alert-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .alert-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #f57c00;
        }
        
        .alert-card.danger {
            border-left-color: #d32f2f;
        }
        
        .alert-card h3 {
            font-size: 1rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .alert-card .count {
            font-size: 2.5rem;
            font-weight: bold;
            color: #f57c00;
        }
        
        .alert-card.danger .count {
            color: #d32f2f;
        }
        
        .content-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 1.3rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .warning-icon {
            color: #f57c00;
            font-size: 1.5rem;
        }
        
        .danger-icon {
            color: #d32f2f;
            font-size: 1.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e6ed;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        .stock-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stock-low {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .stock-out {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-btn:hover {
            background-color: #2980b9;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #27ae60;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #1565c0;
        }
        
        @media (max-width: 768px) {
            .alert-cards {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 0.9rem;
            }
            
            table th, table td {
                padding: 10px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 class="page-title">📢 Stock Alerts & Notifications</h2>
    </div>

    <div class="alert-cards">
        <div class="alert-card">
            <h3>Low Stock Warning</h3>
            <div class="count"><?= $low_stock->num_rows ?></div>
            <p>Products with stock ≤ 20</p>
        </div>
        
        <div class="alert-card danger">
            <h3>Out of Stock</h3>
            <div class="count"><?= $out_of_stock->num_rows ?></div>
            <p>Products with zero stock</p>
        </div>
    </div>

    <div class="info-box">
        <p><strong>ℹ️ Stock Alert Levels:</strong></p>
        <p>• <strong>Low Stock:</strong> When product quantity is between 1-20 units</p>
        <p>• <strong>Out of Stock:</strong> When product quantity is 0</p>
        <p>• <strong>Recommended Action:</strong> Reorder products before they run out completely</p>
    </div>

    <!-- Low Stock Products -->
    <div class="content-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-exclamation-triangle warning-icon"></i>
                Low Stock Products
            </h3>
        </div>
        
        <?php if($low_stock->num_rows > 0): ?>
            <?php $low_stock->data_seek(0); ?>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($product = $low_stock->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                        <td><?= htmlspecialchars($product['category_name']) ?></td>
                        <td><strong><?= $product['stock'] ?> units</strong></td>
                        <td>
                            <span class="stock-badge stock-low">
                                <i class="fas fa-exclamation-triangle"></i> Low Stock
                            </span>
                        </td>
                        <td>₹<?= number_format($product['cost_price'], 2) ?></td>
                        <td>₹<?= number_format($product['selling_price'], 2) ?></td>
                        <td>
                            <a href="javascript:void(0)" onclick="parent.loadPage('purchases.php')" class="action-btn">
                                <i class="fas fa-shopping-cart"></i> Reorder
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <i class="fas fa-check-circle"></i>
                <p><strong>All Good!</strong></p>
                <p>No products with low stock at the moment.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Out of Stock Products -->
    <div class="content-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-times-circle danger-icon"></i>
                Out of Stock Products
            </h3>
        </div>
        
        <?php if($out_of_stock->num_rows > 0): ?>
            <?php $out_of_stock->data_seek(0); ?>
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($product = $out_of_stock->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                        <td><?= htmlspecialchars($product['category_name']) ?></td>
                        <td><strong><?= $product['stock'] ?> units</strong></td>
                        <td>
                            <span class="stock-badge stock-out">
                                <i class="fas fa-times-circle"></i> Out of Stock
                            </span>
                        </td>
                        <td>₹<?= number_format($product['cost_price'], 2) ?></td>
                        <td>₹<?= number_format($product['selling_price'], 2) ?></td>
                        <td>
                            <a href="javascript:void(0)" onclick="parent.loadPage('purchases.php')" class="action-btn">
                                <i class="fas fa-shopping-cart"></i> Reorder Now
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
            <div class="no-data">
                <i class="fas fa-check-circle"></i>
                <p><strong>Excellent!</strong></p>
                <p>No products are currently out of stock.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>