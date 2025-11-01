<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo 'Not authenticated';
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

$id = (int)($_POST['id'] ?? 0);
$name = $_POST['name'] ?? '';
$category_id = (int)($_POST['category_id'] ?? 0);
$cost_price = (float)($_POST['cost_price'] ?? 0);
$has_mrp = isset($_POST['has_mrp']) ? (int)$_POST['has_mrp'] : 0;
$mrp = ($has_mrp && isset($_POST['mrp'])) ? (float)$_POST['mrp'] : null;
$profit_margin = (float)($_POST['profit_margin'] ?? 0);
$selling_price = (float)($_POST['selling_price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$supplier_id = isset($_POST['supplier_id']) && $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;

if (!$id || !$name || !$category_id) { 
    echo 'Invalid input'; 
    exit; 
}

if ($cost_price <= 0) {
    echo 'Please provide valid cost price.';
    exit;
}

if ($selling_price <= 0) {
    echo 'Please provide valid selling price.';
    exit;
}

if ($selling_price <= $cost_price) {
    echo 'Selling price must be greater than cost price.';
    exit;
}

if ($has_mrp) {
    if (!$mrp || $mrp <= 0) {
        echo 'Please provide valid MRP for packaged product.';
        exit;
    }
    if ($selling_price > $mrp) {
        echo 'Selling price (₹' . number_format($selling_price, 2) . ') cannot exceed MRP (₹' . number_format($mrp, 2) . ')';
        exit;
    }
}

$verify = $conn->prepare('SELECT id FROM categories WHERE id = ? AND admin_id = ?');
$verify->bind_param('ii', $category_id, $admin_id);
$verify->execute();
if ($verify->get_result()->num_rows === 0) {
    echo 'Invalid category';
    exit;
}
$verify->close();

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

$stmt = $conn->prepare('UPDATE products SET name=?, category_id=?, cost_price=?, has_mrp=?, mrp=?, profit_margin=?, selling_price=?, stock=?, description=?, supplier_id=? WHERE id=? AND admin_id=?');
$stmt->bind_param('siddddiisiii', $name, $category_id, $cost_price, $has_mrp, $mrp, $profit_margin, $selling_price, $stock, $description, $supplier_id, $id, $admin_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Error: ' . $stmt->error;
}
$stmt->close();
$conn->close();
?>