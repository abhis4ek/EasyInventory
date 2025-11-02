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

// Get sale details
$stmt = $conn->prepare("
  SELECT s.id, s.sale_date, s.total_amount, 
         COALESCE(s.created_at, CONCAT(s.sale_date, ' 00:00:00')) as created_at,
         c.name as customer_name, c.street_address, c.city, c.pin_code, c.state, c.phone, c.email
  FROM sales s 
  LEFT JOIN customers c ON s.customer_id = c.id 
  WHERE s.id = ? AND s.admin_id = ?");
$stmt->bind_param("ii", $id, $admin_id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sale) {
    echo "Sale not found or access denied.";
    exit();
}

// Get sale items with GST info
$items_stmt = $conn->prepare("
  SELECT si.*, pr.name as product_name, pr.has_mrp, pr.mrp,
         c.name as category_name, c.gst_rate
  FROM sale_items si
  JOIN products pr ON si.product_id = pr.id
  LEFT JOIN categories c ON pr.category_id = c.id
  WHERE si.sale_id = ?
");
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items = $items_stmt->get_result();
$items_stmt->close();

// Calculate totals with GST
$subtotal = 0;
$total_gst = 0;
$items_array = [];
while($item = $items->fetch_assoc()) {
    $item_subtotal = $item['subtotal'];
    $gst_rate = $item['gst_rate'] ?? 0;
    $gst_amount = ($item_subtotal * $gst_rate) / (100 + $gst_rate); // Reverse calculate GST from inclusive price
    
    $item['gst_amount'] = $gst_amount;
    $item['amount_before_gst'] = $item_subtotal - $gst_amount;
    
    $subtotal += $item['amount_before_gst'];
    $total_gst += $gst_amount;
    $items_array[] = $item;
}
$grand_total = $subtotal + $total_gst;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice #<?= $sale['id'] ?></title>
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
            border-bottom: 3px solid #27ae60;
        }
        
        .company-details h1 {
            color: #27ae60;
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
            color: #27ae60;
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
            background: #27ae60;
            color: white;
        }
        
        table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
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
            padding: 12px 8px;
            font-size: 13px;
            color: #333;
        }
        
        .item-name {
            font-weight: 600;
            color: #27ae60;
        }
        
        .item-category {
            font-size: 11px;
            color: #999;
            display: block;
            margin-top: 3px;
        }
        
        .badge-mrp {
            background: #ff9800;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
            float: right;  
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
            border-top: 2px solid #27ae60;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .gst-breakdown {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        
        .gst-breakdown h4 {
            color: #856404;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .gst-breakdown p {
            font-size: 12px;
            color: #856404;
            margin: 5px 0;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        
        .thank-you {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #d4edda;
            border-radius: 8px;
            color: #155724;
            font-size: 16px;
            font-weight: 600;
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
            background: #27ae60;
            color: white;
        }
        
        .btn-print:hover {
            background: #229954;
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
                font-size: 11px;
            }
            
            table th,
            table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button class="btn-print" onclick="window.print()">🖨️ Print Invoice</button>
        <button class="btn-back" onclick="window.location.href='sales.php'">← Back to Sales</button>
    </div>

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-details">
                <h1><?= htmlspecialchars($admin_data['shop_name'] ?? 'My Shop') ?></h1>
                <p><strong>Owner:</strong> <?= htmlspecialchars($admin_data['fullname']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($admin_data['email']) ?></p>
            </div>
            <div class="invoice-title">
                <h2>SALES INVOICE</h2>
                <p><strong>Invoice #:</strong> INV-<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?></p>
                <p><strong>Date:</strong> <?= date('F d, Y', strtotime($sale['sale_date'])) ?></p>
            </div>
        </div>

        <div class="invoice-meta">
            <div class="meta-box">
                <h3>Bill To</h3>
                <p><strong>Customer:</strong> <?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer') ?></p>
                <?php if($sale['street_address']): ?>
                <p><strong>Address:</strong> <?= htmlspecialchars($sale['street_address']) ?></p>
                <p><?= htmlspecialchars($sale['city']) ?>, <?= htmlspecialchars($sale['state']) ?> - <?= htmlspecialchars($sale['pin_code']) ?></p>
                <?php endif; ?>
                <?php if($sale['phone']): ?>
                <p><strong>Phone:</strong> <?= htmlspecialchars($sale['phone']) ?></p>
                <?php endif; ?>
                <?php if($sale['email']): ?>
                <p><strong>Email:</strong> <?= htmlspecialchars($sale['email']) ?></p>
                <?php endif; ?>
            </div>
            
            <div class="meta-box">
                <h3>Invoice Details</h3>
                <p><strong>Invoice ID:</strong> #<?= $sale['id'] ?></p>
                <p><strong>Sale Date:</strong> <?= date('M d, Y', strtotime($sale['sale_date'])) ?></p>
                <p><strong>Generated:</strong> <?= date('M d, Y H:i', strtotime($sale['created_at'])) ?></p>
                <p><strong>Status:</strong> <span style="color: #27ae60; font-weight: 600;">PAID</span></p>
            </div>
        </div>

        <?php if($total_gst > 0): ?>
        <div class="gst-breakdown">
            <h4>📋 GST Information</h4>
            <p>This invoice includes GST as per applicable rates. Total GST: ₹<?= number_format($total_gst, 2) ?></p>
        </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Product Details</th>
                    <th style="width: 80px; text-align: center;">Qty</th>
                    <th style="width: 100px; text-align: right;">Price</th>
                    <th style="width: 80px; text-align: center;">GST %</th>
                    <th style="width: 100px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $row_num = 1;
                foreach($items_array as $item): 
                ?>
                <tr>
                    <td><?= $row_num++ ?></td>
                    <td>
                        <span class="item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                        <?php if($item['has_mrp'] && $item['mrp'] > 0): ?>
                            <span class="badge-mrp">MRP ₹<?= number_format($item['mrp'], 2) ?></span>
                        <?php endif; ?>
                        <span class="item-category"><?= htmlspecialchars($item['category_name'] ?? '') ?></span>
                    </td>
                    <td style="text-align: center;"><?= $item['quantity'] ?></td>
                    <td style="text-align: right;">₹<?= number_format($item['unit_price'], 2) ?></td>
                    <td style="text-align: center;"><?= number_format($item['gst_rate'], 2) ?>%</td>
                    <td style="text-align: right;"><strong>₹<?= number_format($item['subtotal'], 2) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal (before GST):</span>
                    <span>₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <?php if($total_gst > 0): ?>
                <div class="total-row">
                    <span>Total GST:</span>
                    <span>₹<?= number_format($total_gst, 2) ?></span>
                </div>
                <?php endif; ?>
                <div class="total-row grand-total">
                    <span>TOTAL AMOUNT:</span>
                    <span>₹<?= number_format($sale['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <div class="thank-you">
            🎉 Thank you for your business! We appreciate your patronage.
        </div>

        <div class="footer">
            <p>This is a computer-generated invoice from <?= htmlspecialchars($admin_data['shop_name'] ?? 'Easy Inventory') ?></p>
            <p>Generated on <?= date('F d, Y \a\t H:i') ?></p>
            <p style="margin-top: 10px; font-size: 11px;">For any queries, please contact us at <?= htmlspecialchars($admin_data['email']) ?></p>
        </div>
    </div>
</body>
</html>