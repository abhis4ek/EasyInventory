<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

$id = (int)($_GET['id'] ?? 0);

// Get admin/shop details
$admin_stmt = $conn->prepare("SELECT fullname, shop_name, email FROM admin WHERE admin_id = ?");
$admin_stmt->bind_param('i', $admin_id);
$admin_stmt->execute();
$admin_data = $admin_stmt->get_result()->fetch_assoc();
$admin_stmt->close();

// Get purchase details
$stmt = $conn->prepare("
  SELECT p.id, p.purchase_date, p.total_amount, 
         COALESCE(p.created_at, CONCAT(p.purchase_date, ' 00:00:00')) as created_at,
         s.name as supplier_name, s.street_address, s.city, s.pin_code, s.state, s.phone, s.email
  FROM purchases p 
  LEFT JOIN suppliers s ON p.supplier_id = s.id 
  WHERE p.id = ? AND p.admin_id = ?");
$stmt->bind_param("ii", $id, $admin_id);
$stmt->execute();
$purchase = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$purchase) {
    echo "Purchase not found or access denied.";
    exit();
}

// Get purchase items
$items_stmt = $conn->prepare("
  SELECT pi.*, pr.name as product_name, c.name as category_name
  FROM purchase_items pi
  JOIN products pr ON pi.product_id = pr.id
  LEFT JOIN categories c ON pr.category_id = c.id
  WHERE pi.purchase_id = ?
");
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items = $items_stmt->get_result();
$items_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice #<?= $purchase['id'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }
        
        .company-details h1 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .company-details p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .invoice-title {
            text-align: right;
        }
        
        .invoice-title h2 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .invoice-title p {
            color: #666;
            font-size: 14px;
        }
        
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .meta-box {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .meta-box h3 {
            color: #2563eb;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }
        
        .meta-box p {
            color: #333;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 5px;
        }
        
        .meta-box strong {
            display: inline-block;
            width: 100px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        table thead {
            background: #2563eb;
            color: white;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        table th:last-child,
        table td:last-child {
            text-align: right;
        }
        
        table tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }
        
        table tbody tr:hover {
            background: #f8f9fa;
        }
        
        table td {
            padding: 15px 12px;
            font-size: 14px;
            color: #333;
        }
        
        .item-name {
            font-weight: 600;
            color: #2563eb;
        }
        
        .item-category {
            font-size: 12px;
            color: #999;
            display: block;
            margin-top: 3px;
        }
        
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .total-box {
            width: 350px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .total-row.grand-total {
            border-top: 2px solid #2563eb;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        
        .action-buttons {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .action-buttons button {
            padding: 12px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-print {
            background: #2563eb;
            color: white;
        }
        
        .btn-print:hover {
            background: #1d4ed8;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }
            
            .action-buttons {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-header {
                flex-direction: column;
            }
            
            .invoice-title {
                text-align: left;
                margin-top: 20px;
            }
            
            .invoice-meta {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 12px;
            }
            
            table th,
            table td {
                padding: 8px 6px;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button class="btn-print" onclick="window.print()">🖨️ Print Invoice</button>
        <button class="btn-back" onclick="window.location.href='purchases.php'">← Back to Purchases</button>
    </div>

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-details">
                <h1><?= htmlspecialchars($admin_data['shop_name'] ?? 'My Shop') ?></h1>
                <p><strong>Owner:</strong> <?= htmlspecialchars($admin_data['fullname']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($admin_data['email']) ?></p>
            </div>
            <div class="invoice-title">
                <h2>PURCHASE ORDER</h2>
                <p><strong>Invoice #:</strong> PO-<?= str_pad($purchase['id'], 6, '0', STR_PAD_LEFT) ?></p>
                <p><strong>Date:</strong> <?= date('F d, Y', strtotime($purchase['purchase_date'])) ?></p>
            </div>
        </div>

        <div class="invoice-meta">
            <div class="meta-box">
                <h3>Purchased From</h3>
                <p><strong>Supplier:</strong> <?= htmlspecialchars($purchase['supplier_name'] ?? 'N/A') ?></p>
                <?php if($purchase['street_address']): ?>
                <p><strong>Address:</strong> <?= htmlspecialchars($purchase['street_address']) ?></p>
                <p><?= htmlspecialchars($purchase['city']) ?>, <?= htmlspecialchars($purchase['state']) ?> - <?= htmlspecialchars($purchase['pin_code']) ?></p>
                <?php endif; ?>
                <?php if($purchase['phone']): ?>
                <p><strong>Phone:</strong> <?= htmlspecialchars($purchase['phone']) ?></p>
                <?php endif; ?>
                <?php if($purchase['email']): ?>
                <p><strong>Email:</strong> <?= htmlspecialchars($purchase['email']) ?></p>
                <?php endif; ?>
            </div>
            
            <div class="meta-box">
                <h3>Purchase Details</h3>
                <p><strong>Purchase ID:</strong> #<?= $purchase['id'] ?></p>
                <p><strong>Order Date:</strong> <?= date('M d, Y', strtotime($purchase['purchase_date'])) ?></p>
                <p><strong>Created:</strong> <?= date('M d, Y H:i', strtotime($purchase['created_at'])) ?></p>
                <p><strong>Status:</strong> <span style="color: #27ae60; font-weight: 600;">COMPLETED</span></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product Details</th>
                    <th style="width: 100px; text-align: center;">Quantity</th>
                    <th style="width: 120px; text-align: right;">Unit Price</th>
                    <th style="width: 120px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $row_num = 1;
                while($item = $items->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $row_num++ ?></td>
                    <td>
                        <span class="item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                        <span class="item-category"><?= htmlspecialchars($item['category_name'] ?? '') ?></span>
                    </td>
                    <td style="text-align: center;"><?= $item['quantity'] ?></td>
                    <td style="text-align: right;">₹<?= number_format($item['unit_price'], 2) ?></td>
                    <td style="text-align: right;"><strong>₹<?= number_format($item['subtotal'], 2) ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>₹<?= number_format($purchase['total_amount'], 2) ?></span>
                </div>
                <div class="total-row grand-total">
                    <span>TOTAL AMOUNT:</span>
                    <span>₹<?= number_format($purchase['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated purchase order from <?= htmlspecialchars($admin_data['shop_name'] ?? 'Easy Inventory') ?></p>
            <p>Generated on <?= date('F d, Y \a\t H:i') ?></p>
        </div>
    </div>
</body>
</html>