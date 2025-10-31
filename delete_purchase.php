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
    // Verify purchase belongs to this user
    $verify = $conn->prepare('SELECT id FROM purchases WHERE id = ? AND admin_id = ?');
    $verify->bind_param('ii', $id, $admin_id);
    $verify->execute();
    if ($verify->get_result()->num_rows === 0) {
        throw new Exception('Purchase not found or access denied');
    }
    $verify->close();
    
    // Get purchase items to reverse stock
    $items = $conn->prepare('SELECT product_id, quantity FROM purchase_items WHERE purchase_id = ?');
    $items->bind_param('i', $id);
    $items->execute();
    $items_res = $items->get_result();
    
    // Reverse stock changes (subtract quantities that were added)
    $updateStock = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND admin_id = ?');
    while($item = $items_res->fetch_assoc()) {
        $updateStock->bind_param('iii', $item['quantity'], $item['product_id'], $admin_id);
        if (!$updateStock->execute()) {
            throw new Exception('Failed to update stock');
        }
    }
    $items->close();
    $updateStock->close();
    
    // Delete purchase items
    $deleteItems = $conn->prepare('DELETE FROM purchase_items WHERE purchase_id = ?');
    $deleteItems->bind_param('i', $id);
    if (!$deleteItems->execute()) {
        throw new Exception('Failed to delete purchase items');
    }
    $deleteItems->close();
    
    // Delete purchase
    $deletePurchase = $conn->prepare('DELETE FROM purchases WHERE id = ? AND admin_id = ?');
    $deletePurchase->bind_param('ii', $id, $admin_id);
    if (!$deletePurchase->execute()) {
        throw new Exception('Failed to delete purchase');
    }
    $deletePurchase->close();
    
    $conn->commit();
    echo 'success';
} catch (Exception $e) {
    $conn->rollback();
    echo 'error: ' . $e->getMessage();
}
?>