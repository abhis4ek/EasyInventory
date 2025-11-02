<?php
// get_low_stock_count.php - Returns count of low stock items
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

// Count products with stock <= 20
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE admin_id = ? AND stock > 0 AND stock <= 20");
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo json_encode(['count' => $result['count']]);
?>