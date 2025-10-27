<?php
require 'db.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("
  SELECT p.id, p.purchase_date, p.total_amount, s.name supplier_name
  FROM purchases p LEFT JOIN suppliers s ON p.supplier_id = s.id WHERE p.id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$meta = $stmt->get_result()->fetch_assoc();

$items = $conn->prepare("
  SELECT pi.*, pr.name as product_name
  FROM purchase_items pi
  JOIN products pr ON pi.product_id = pr.id
  WHERE pi.purchase_id = ?
");
$items->bind_param("i",$id);
$items->execute();
$items_res = $items->get_result();
?>
<h2>Purchase #<?= $meta['id'] ?> — <?= htmlspecialchars($meta['supplier_name']) ?></h2>
<p>Date: <?= $meta['purchase_date'] ?> — Total: <?= $meta['total_amount'] ?></p>
<table>
  <tr><th>Product</th><th>Qty</th><th>Unit</th><th>Subtotal</th></tr>
  <?php while($it = $items_res->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($it['product_name']) ?></td>
      <td><?= $it['quantity'] ?></td>
      <td><?= $it['unit_price'] ?></td>
      <td><?= $it['subtotal'] ?></td>
    </tr>
  <?php endwhile; ?>
</table>
