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

// ✅ Added admin_id filter to DELETE - only delete user's own products
$stmt = $conn->prepare('DELETE FROM products WHERE id = ? AND admin_id = ?');
$stmt->bind_param('ii', $id, $admin_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
$stmt->close();
?>