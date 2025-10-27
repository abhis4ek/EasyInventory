<?php
require 'db.php';
$res = $conn->query("
  SELECT p.id, p.purchase_date, p.total_amount, s.name AS supplier_name
  FROM purchases p
  LEFT JOIN suppliers s ON p.supplier_id = s.id
  ORDER BY p.purchase_date DESC
");
?>
<table>
  <thead><tr><th>#</th><th>Date</th><th>Supplier</th><th>Total</th><th>Action</th></tr></thead>
  <tbody>
  <?php while($row = $res->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= $row['purchase_date'] ?></td>
      <td><?= htmlspecialchars($row['supplier_name']) ?></td>
      <td><?= $row['total_amount'] ?></td>
      <td><a href="purchase_view.php?id=<?= $row['id'] ?>">View</a></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
