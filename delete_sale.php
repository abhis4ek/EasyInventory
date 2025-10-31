<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo 'Not authenticated';
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

$id = (int)($_POST['id'] ?? 0);
if (!$id) { 
    echo 'invalid'; 
    exit;
}

$conn->begin_transaction();
try {
    // Verify sale belongs to this user
    $verify = $conn->prepare('SELECT id FROM sales WHERE id = ? AND admin_id = ?');
    $verify->bind_param('ii', $id, $admin_id);
    $verify->execute();
    if ($verify->get_result()->num_rows === 0) {
        throw new Exception('Sale not found or access denied');
    }
    $verify->close();
    
    // Get sale items to reverse stock
    $items = $conn->prepare('SELECT product_id, quantity FROM sale_items WHERE sale_id = ?');
    $items->bind_param('i', $id);
    $items->execute();
    $items_res = $items->get_result();
    
    // Reverse stock changes (add back quantities that were sold)
    $updateStock = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ? AND admin_id = ?');
    while($item = $items_res->fetch_assoc()) {
        $updateStock->bind_param('iii', $item['quantity'], $item['product_id'], $admin_id);
        if (!$updateStock->execute()) {
            throw new Exception('Failed to update stock');
        }
    }
    $items->close();
    $updateStock->close();
    
    // Delete sale items
    $deleteItems = $conn->prepare('DELETE FROM sale_items WHERE sale_id = ?');
    $deleteItems->bind_param('i', $id);
    if (!$deleteItems->execute()) {
        throw new Exception('Failed to delete sale items');
    }
    $deleteItems->close();
    
    // Delete sale
    $deleteSale = $conn->prepare('DELETE FROM sales WHERE id = ? AND admin_id = ?');
    $deleteSale->bind_param('ii', $id, $admin_id);
    if (!$deleteSale->execute()) {
        throw new Exception('Failed to delete sale');
    }
    $deleteSale->close();
    
    $conn->commit();
    echo 'success';
} catch (Exception $e) {
    $conn->rollback();
    echo 'error: ' . $e->getMessage();
}
?>