<?php
require 'db.php';

// Expected POST: id, name, category_id, price, stock, description (optional), supplier_id (optional)
$id = (int)($_POST['id'] ?? 0);
$name = $_POST['name'] ?? '';
$category_id = (int)($_POST['category_id'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$supplier_id = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;

if (!$id || !$name || !$category_id) { echo 'Invalid input'; exit; }

$stmt = $conn->prepare('UPDATE products SET name=?, category_id=?, price=?, stock=?, description=?, supplier_id=? WHERE id=?');
$stmt->bind_param('sidisii', $name, $category_id, $price, $stock, $description, $supplier_id, $id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Error: ' . $stmt->error;
}
$stmt->close();
$conn->close();
?>
