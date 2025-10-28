<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

// ✅ Filter purchases by admin_id - only show user's own purchases
$stmt = $conn->prepare("
  SELECT p.id, p.purchase_date, p.total_amount, s.name AS supplier_name
  FROM purchases p
  LEFT JOIN suppliers s ON p.supplier_id = s.id
  WHERE p.admin_id = ?
  ORDER BY p.purchase_date DESC
");
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchases List</title>
</head>
<body>
    <h2>Purchases</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if($res->num_rows > 0): ?>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['purchase_date'] ?></td>
                <td><?= htmlspecialchars($row['supplier_name'] ?? 'N/A') ?></td>
                <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                <td><a href="purchase_view.php?id=<?= $row['id'] ?>">View</a></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No purchases found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <br>
    <a href="purchases.php">← Back</a>
</body>
</html>