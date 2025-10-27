<?php
require 'db.php';

// Expected POST: customer_id (optional), sale_date (optional), product_id[] , quantity[] , unit_price[]
$customer_id = isset($_POST['customer_id']) && $_POST['customer_id'] !== '' ? (int)$_POST['customer_id'] : null;
$sale_date = $_POST['sale_date'] ?? date('Y-m-d');
$product_ids = $_POST['product_id'] ?? [];
$qtys = $_POST['quantity'] ?? [];
$unit_prices = $_POST['unit_price'] ?? [];

if (empty($product_ids)) { echo 'invalid'; exit; }

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
        // check stock
        $stmtCheck = $conn->prepare('SELECT stock FROM products WHERE id = ? FOR UPDATE');
        $stmtCheck->bind_param('i', $pid);
        $stmtCheck->execute();
        $res = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();
        if (!$res || $res['stock'] < $q) throw new Exception('Insufficient stock for product ID '.$pid);
        $subtotal = $q * $up;
        $total += $subtotal;
        $items[] = ['pid'=>$pid,'q'=>$q,'up'=>$up,'subtotal'=>$subtotal];
    }
    // insert sale meta
    $stmt = $conn->prepare('INSERT INTO sales (customer_id, sale_date, total_amount) VALUES (?, ?, ?)');
    $stmt->bind_param('isd', $customer_id, $sale_date, $total);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    $sale_id = $stmt->insert_id;
    $stmt->close();

    // insert items and update stock (decrease)
    $stmtItem = $conn->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
    $stmtUpdate = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');

    foreach ($items as $it){
        $stmtItem->bind_param('iiidd', $sale_id, $it['pid'], $it['q'], $it['up'], $it['subtotal']);
        if (!$stmtItem->execute()) throw new Exception($stmtItem->error);
        $stmtUpdate->bind_param('ii', $it['q'], $it['pid']);
        if (!$stmtUpdate->execute()) throw new Exception($stmtUpdate->error);
    }
    $conn->commit();
    echo 'success';
} catch (Exception $e) {
    $conn->rollback();
    echo 'error: '.$e->getMessage();
}
?>
