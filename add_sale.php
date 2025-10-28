<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

// Expected POST: customer_id (optional), sale_date (optional), product_id[] , quantity[] , unit_price[]
$customer_id = isset($_POST['customer_id']) && $_POST['customer_id'] !== '' ? (int)$_POST['customer_id'] : null;
$sale_date = $_POST['sale_date'] ?? date('Y-m-d');
$product_ids = $_POST['product_id'] ?? [];
$qtys = $_POST['quantity'] ?? [];
$unit_prices = $_POST['unit_price'] ?? [];

if (empty($product_ids)) { 
    echo 'invalid'; 
    exit; 
}

// ✅ Verify customer belongs to this user (if provided)
if ($customer_id) {
    $verify = $conn->prepare('SELECT id FROM customers WHERE id = ? AND admin_id = ?');
    $verify->bind_param('ii', $customer_id, $admin_id);
    $verify->execute();
    if ($verify->get_result()->num_rows === 0) {
        echo 'invalid customer';
        exit;
    }
    $verify->close();
}

$conn->begin_transaction();
try {
    // compute total
    $total = 0;
    $items = [];
    for ($i=0;$i<count($product_ids);$i++){
        $pid = (int)$product_ids[$i];
        $q = (int)$qtys[$i];
        $up = (float)$unit_prices[$i];
        if ($pid<=0 || $q<=0) throw new Exception('Invalid product/quantity');
        
        // ✅ Check stock AND verify product belongs to this user
        $stmtCheck = $conn->prepare('SELECT stock FROM products WHERE id = ? AND admin_id = ? FOR UPDATE');
        $stmtCheck->bind_param('ii', $pid, $admin_id);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();
        if (!$res) throw new Exception('Invalid product access for product ID '.$pid);
        if ($res['stock'] < $q) throw new Exception('Insufficient stock for product ID '.$pid);
        
        $subtotal = $q * $up;
        $total += $subtotal;
        $items[] = ['pid'=>$pid,'q'=>$q,'up'=>$up,'subtotal'=>$subtotal];
    }
    
    // ✅ Fixed: Added admin_id to INSERT
    $stmt = $conn->prepare('INSERT INTO sales (admin_id, customer_id, sale_date, total_amount) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iisd', $admin_id, $customer_id, $sale_date, $total);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    $sale_id = $stmt->insert_id;
    $stmt->close();

    // insert items and update stock (decrease)
    $stmtItem = $conn->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
    
    // ✅ Fixed: Added admin_id to UPDATE to ensure user can only update their own products
    $stmtUpdate = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND admin_id = ?');

    foreach ($items as $it){
        $stmtItem->bind_param('iiidd', $sale_id, $it['pid'], $it['q'], $it['up'], $it['subtotal']);
        if (!$stmtItem->execute()) throw new Exception($stmtItem->error);
        
        // ✅ Fixed: Added admin_id parameter
        $stmtUpdate->bind_param('iii', $it['q'], $it['pid'], $admin_id);
        if (!$stmtUpdate->execute()) throw new Exception($stmtUpdate->error);
    }
    
    $conn->commit();
    echo 'success';
} catch (Exception $e) {
    $conn->rollback();
    echo 'error: '.$e->getMessage();
}
?>