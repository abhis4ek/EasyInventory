<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

// Expected POST: name, category_id, cost_price, has_mrp, mrp, profit_margin, selling_price, stock, description, supplier_id
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

// Validation
if (!$name || !$category_id) { 
    echo 'Please provide product name and category.'; 
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

// MRP validation - if product has MRP, validate it
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

// Profit validation - selling must be greater than cost
if ($selling_price <= $cost_price) {
    echo 'Selling price must be greater than cost price.';
    exit;
}

// Insert product with new pricing fields
$stmt = $conn->prepare('INSERT INTO products (admin_id, name, category_id, cost_price, has_mrp, mrp, profit_margin, selling_price, stock, description, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('isiddddidsi', $admin_id, $name, $category_id, $cost_price, $has_mrp, $mrp, $profit_margin, $selling_price, $stock, $description, $supplier_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Error: ' . $stmt->error;
}
$stmt->close();
$conn->close();
?>