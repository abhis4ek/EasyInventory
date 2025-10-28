<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

$id = (int)($_GET['id'] ?? 0);

// ✅ Filter purchase by admin_id - only view user's own purchases
$stmt = $conn->prepare("
  SELECT p.id, p.purchase_date, p.total_amount, s.name supplier_name
  FROM purchases p 
  LEFT JOIN suppliers s ON p.supplier_id = s.id 
  WHERE p.id = ? AND p.admin_id = ?");
$stmt->bind_param("ii", $id, $admin_id);
$stmt->execute();
$meta = $stmt->get_result()->fetch_assoc();

// ✅ Check if purchase exists and belongs to this user
if (!$meta) {
    echo "Purchase not found or access denied.";
    exit();
}

// ✅ Filter purchase items - verify products belong to this user
$items = $conn->prepare("
  SELECT pi.*, pr.name as product_name
  FROM purchase_items pi
  JOIN products pr ON pi.product_id = pr.id
  WHERE pi.purchase_id = ? AND pr.admin_id = ?
");
$items->bind_param("ii", $id, $admin_id);
$items->execute();
$items_res = $items->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Details</title>
</head>
<body>
    <h2>Purchase #<?= $meta['id'] ?> — <?= htmlspecialchars($meta['supplier_name']) ?></h2>
    <p>Date: <?= $meta['purchase_date'] ?> — Total: ₹<?= number_format($meta['total_amount'], 2) ?></p>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Subtotal</th>
        </tr>
        <?php while($it = $items_res->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($it['product_name']) ?></td>
            <td><?= $it['quantity'] ?></td>
            <td>₹<?= number_format($it['unit_price'], 2) ?></td>
            <td>₹<?= number_format($it['subtotal'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="purchases.php">← Back to Purchases</a>
</body>
</html>