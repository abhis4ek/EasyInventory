<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

// Expected POST: name, category_id, price, stock, description (optional), supplier_id (optional)
$name = $_POST['name'] ?? '';
$category_id = (int)($_POST['category_id'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$supplier_id = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;

if (!$name || !$category_id) { 
    echo 'Please provide product name and category.'; 
    exit; 
}

// ✅ Fixed: Added 7th placeholder (?) and corrected bind_param types
$stmt = $conn->prepare('INSERT INTO products (admin_id, name, category_id, price, stock, description, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('isidisi', $admin_id, $name, $category_id, $price, $stock, $description, $supplier_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Error: ' . $stmt->error;
}
$stmt->close();
$conn->close();
?>