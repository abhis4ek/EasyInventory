<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo 'Not authenticated';
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

// Expected POST: id, name, category_id, price, stock, description (optional), supplier_id (optional)
$id = (int)($_POST['id'] ?? 0);
$name = $_POST['name'] ?? '';
$category_id = (int)($_POST['category_id'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$supplier_id = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;

if (!$id || !$name || !$category_id) { 
    echo 'Invalid input'; 
    exit; 
}

// ✅ Verify category belongs to this user
$verify = $conn->prepare('SELECT id FROM categories WHERE id = ? AND admin_id = ?');
$verify->bind_param('ii', $category_id, $admin_id);
$verify->execute();
if ($verify->get_result()->num_rows === 0) {
    echo 'Invalid category';
    exit;
}
$verify->close();

// ✅ Verify supplier belongs to this user (if provided)
if ($supplier_id) {
    $verify = $conn->prepare('SELECT id FROM suppliers WHERE id = ? AND admin_id = ?');
    $verify->bind_param('ii', $supplier_id, $admin_id);
    $verify->execute();
    if ($verify->get_result()->num_rows === 0) {
        echo 'Invalid supplier';
        exit;
    }
    $verify->close();
}

// ✅ Added admin_id filter to UPDATE - only update user's own products
$stmt = $conn->prepare('UPDATE products SET name=?, category_id=?, price=?, stock=?, description=?, supplier_id=? WHERE id=? AND admin_id=?');
$stmt->bind_param('sidisiii', $name, $category_id, $price, $stock, $description, $supplier_id, $id, $admin_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Error: ' . $stmt->error;
}
$stmt->close();
$conn->close();
?>