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
$cgst_total = 0;
$sgst_total = 0;
$items_array = [];
while($item = $items->fetch_assoc()) {
    $item_subtotal = $item['subtotal'];
    $gst_rate = $item['gst_rate'] ?? 0;
    $gst_amount = ($item_subtotal * $gst_rate) / (100 + $gst_rate);
    
    $item['gst_amount'] = $gst_amount;
    $item['amount_before_gst'] = $item_subtotal - $gst_amount;
    $item['cgst'] = $gst_amount / 2;
    $item['sgst'] = $gst_amount / 2;
    
    $subtotal += $item['amount_before_gst'];
    $total_gst += $gst_amount;
    $cgst_total += $item['cgst'];
    $sgst_total += $item['sgst'];
    $items_array[] = $item;
}
$grand_total = $subtotal + $total_gst;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice #<?= $sale['id'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
            font-size: 12px;
        }
        
        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 0;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            border: 2px solid #000;
            border-bottom: 1px solid #000;
        }
        
        .header-top {
            padding: 15px 20px;
            text-align: center;
            border-bottom: 1px solid #000;
        }
        
        .header-top h1 {
            font-size: 24px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .header-top p {
            font-size: 11px;
            line-height: 1.4;
        }
        
        .invoice-title {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 8px;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid #000;
        }
        
        .meta-left {
            padding: 15px 20px;
            border-right: 1px solid #000;
        }
        
        .meta-right {
            padding: 15px 20px;
        }
        
        .meta-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .meta-label {
            font-weight: bold;
            min-width: 120px;
        }
        
        .address-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 2px solid #000;
            border-top: none;
        }
        
        .address-box {
            padding: 15px 20px;
            min-height: 120px;
        }
        
        .address-box:first-child {
            border-right: 1px solid #000;
        }
        
        .address-box h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .address-box p {
            font-size: 11px;
            line-height: 1.6;
            margin-bottom: 3px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            border-top: none;
        }
        
        .items-table th {
            background: #f0f0f0;
            border: 1px solid #000;
            padding: 8px 5px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .items-table td {
            border: 1px solid #000;
            padding: 8px 5px;
            font-size: 11px;
            vertical-align: top;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table tbody tr:hover {
            background: #f9f9f9;
        }
        
        .totals-section {
            border: 2px solid #000;
            border-top: none;
        }
        
        .totals-grid {
            display: grid;
            grid-template-columns: 60% 40%;
        }
        
        .totals-left {
            padding: 15px 20px;
            border-right: 1px solid #000;
        }
        
        .totals-right {
            padding: 0;
        }
        
        .total-row {
            display: grid;
            grid-template-columns: 1fr 120px;
            border-bottom: 1px solid #000;
            font-size: 11px;
        }
        
        .total-row:last-child {
            border-bottom: none;
        }
        
        .total-row .label {
            padding: 8px 15px;
            border-right: 1px solid #000;
            font-weight: bold;
            text-align: right;
        }
        
        .total-row .value {
            padding: 8px 15px;
            text-align: right;
        }
        
        .grand-total {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 13px;
        }
        
        .amount-in-words {
            padding: 15px 20px;
            border-top: 1px solid #000;
            font-size: 11px;
        }
        
        .amount-in-words strong {
            display: block;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .terms-section {
            padding: 15px 20px;
            border: 2px solid #000;
            border-top: none;
            font-size: 10px;
        }
        
        .terms-section h4 {
            font-size: 11px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .terms-section ol {
            margin-left: 20px;
            line-height: 1.6;
        }
        
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 2px solid #000;
            border-top: none;
            min-height: 100px;
        }
        
        .signature-box {
            padding: 15px 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        
        .signature-box:first-child {
            border-right: 1px solid #000;
        }
        
        .signature-box p {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .action-buttons {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: white;
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
                max-width: 100%;
            }
            
            .action-buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button class="btn-print" onclick="window.print()">🖨️ Print Invoice</button>
        <button class="btn-back" onclick="window.location.href='front.php'">← Back to Dashboard</button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-top">
                <h1><?= htmlspecialchars($admin_data['shop_name'] ?? 'MY BUSINESS') ?></h1>
                <p>Proprietor: <?= htmlspecialchars($admin_data['fullname']) ?></p>
                <p>Email: <?= htmlspecialchars($admin_data['email']) ?></p>
                <p>GSTIN: [Your GSTIN Number]</p>
            </div>
            <div class="invoice-title">TAX INVOICE</div>
            <div class="invoice-meta">
                <div class="meta-left">
                    <div class="meta-row">
                        <span class="meta-label">Invoice Number:</span>
                        <span><?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Invoice Date:</span>
                        <span><?= date('d-m-Y', strtotime($sale['sale_date'])) ?></span>
                    </div>
                </div>
                <div class="meta-right">
                    <div class="meta-row">
                        <span class="meta-label">Payment Due Date:</span>
                        <span><?= date('d-m-Y', strtotime($sale['sale_date'])) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Mode of Payment:</span>
                        <span>CASH</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="address-section">
            <div class="address-box">
                <h3>Billing Address</h3>
                <p><strong><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer') ?></strong></p>
                <?php if($sale['street_address']): ?>
                <p><?= htmlspecialchars($sale['street_address']) ?></p>
                <p><?= htmlspecialchars($sale['city']) ?>, <?= htmlspecialchars($sale['state']) ?></p>
                <p>PIN: <?= htmlspecialchars($sale['pin_code']) ?></p>
                <?php endif; ?>
                <?php if($sale['phone']): ?>
                <p>Phone: <?= htmlspecialchars($sale['phone']) ?></p>
                <?php endif; ?>
            </div>
            
            <div class="address-box">
                <h3>Shipping Address</h3>
                <p><strong><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer') ?></strong></p>
                <?php if($sale['street_address']): ?>
                <p><?= htmlspecialchars($sale['street_address']) ?></p>
                <p><?= htmlspecialchars($sale['city']) ?>, <?= htmlspecialchars($sale['state']) ?></p>
                <p>PIN: <?= htmlspecialchars($sale['pin_code']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">S.No</th>
                    <th style="width: 35%;">Description of Goods</th>
                    <th style="width: 60px;">Qty</th>
                    <th style="width: 70px;">Rate</th>
                    <th style="width: 70px;">Disc %</th>
                    <th style="width: 90px;">Taxable Amount</th>
                    <th style="width: 50px;">GST %</th>
                    <th style="width: 70px;">CGST Amt</th>
                    <th style="width: 70px;">SGST Amt</th>
                    <th style="width: 90px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $row_num = 1;
                foreach($items_array as $item): 
                ?>
                <tr>
                    <td class="text-center"><?= $row_num++ ?></td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-right">₹<?= number_format($item['unit_price'], 2) ?></td>
                    <td class="text-center">0</td>
                    <td class="text-right">₹<?= number_format($item['amount_before_gst'], 2) ?></td>
                    <td class="text-center"><?= number_format($item['gst_rate'], 1) ?>%</td>
                    <td class="text-right">₹<?= number_format($item['cgst'], 2) ?></td>
                    <td class="text-right">₹<?= number_format($item['sgst'], 2) ?></td>
                    <td class="text-right"><strong>₹<?= number_format($item['subtotal'], 2) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-grid">
                <div class="totals-left">
                    <strong style="font-size: 11px;">THANK YOU FOR YOUR BUSINESS!</strong>
                </div>
                <div class="totals-right">
                    <div class="total-row">
                        <div class="label">Taxable Value:</div>
                        <div class="value">₹<?= number_format($subtotal, 2) ?></div>
                    </div>
                    <div class="total-row">
                        <div class="label">CGST:</div>
                        <div class="value">₹<?= number_format($cgst_total, 2) ?></div>
                    </div>
                    <div class="total-row">
                        <div class="label">SGST:</div>
                        <div class="value">₹<?= number_format($sgst_total, 2) ?></div>
                    </div>
                    <div class="total-row">
                        <div class="label">Round Off:</div>
                        <div class="value">₹0.00</div>
                    </div>
                    <div class="total-row grand-total">
                        <div class="label">TOTAL PAYABLE:</div>
                        <div class="value">₹<?= number_format($sale['total_amount'], 2) ?></div>
                    </div>
                </div>
            </div>
            
            <div class="amount-in-words">
                <strong>Amount in Words:</strong>
                <span style="text-transform: capitalize;"><?= convertNumberToWords($sale['total_amount']) ?> Rupees Only</span>
            </div>
        </div>

        <!-- Terms & Conditions -->
        <div class="terms-section">
            <h4>Terms and Conditions:</h4>
            <ol>
                <li>Goods once sold will not be taken back or exchanged</li>
                <li>All disputes are subject to local jurisdiction only</li>
                <li>Payment should be made within the due date</li>
            </ol>
        </div>

        <!-- Signature -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Customer Signature</p>
            </div>
            <div class="signature-box" style="text-align: right;">
                <p>For <?= htmlspecialchars($admin_data['shop_name'] ?? 'MY BUSINESS') ?></p>
                <br>
                <p>Authorized Signatory</p>
            </div>
        </div>
    </div>
</body>
</html>

<?php
function convertNumberToWords($number) {
    $number = (int)$number;
    $words = array(
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
        80 => 'Eighty', 90 => 'Ninety'
    );
    
    if ($number == 0) return 'Zero';
    
    if ($number < 21) {
        return $words[$number];
    } elseif ($number < 100) {
        return $words[10 * floor($number / 10)] . ' ' . $words[$number % 10];
    } elseif ($number < 1000) {
        return $words[floor($number / 100)] . ' Hundred ' . convertNumberToWords($number % 100);
    } elseif ($number < 100000) {
        return convertNumberToWords(floor($number / 1000)) . ' Thousand ' . convertNumberToWords($number % 1000);
    } elseif ($number < 10000000) {
        return convertNumberToWords(floor($number / 100000)) . ' Lakh ' . convertNumberToWords($number % 100000);
    } else {
        return convertNumberToWords(floor($number / 10000000)) . ' Crore ' . convertNumberToWords($number % 10000000);
    }
}
?>