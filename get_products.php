<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([]);
    exit();
}
require 'db.php';
header('Content-Type: application/json');

$admin_id = $_SESSION['admin_id'];

// ✅ Filter products by admin_id - only return user's own products
$stmt = $conn->prepare("SELECT p.id, p.name, p.price, p.stock, p.category_id, c.name AS category_name, p.supplier_id, s.name AS supplier_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.admin_id = ?
        ORDER BY p.name");
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while($r = $res->fetch_assoc()) {
    $out[] = $r;
}

echo json_encode($out);
$stmt->close();
?>