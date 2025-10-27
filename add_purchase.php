<?php
require 'db.php';

// Expected POST: supplier_id, purchase_date (optional), product_id[] , quantity[] , unit_price[]
$supplier_id = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
$purchase_date = $_POST['purchase_date'] ?? date('Y-m-d');
$product_ids = $_POST['product_id'] ?? [];
$qtys = $_POST['quantity'] ?? [];
$unit_prices = $_POST['unit_price'] ?? [];

if (!$supplier_id || empty($product_ids)) { echo 'invalid'; exit; }

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
        $subtotal = $q * $up;
        $total += $subtotal;
        $items[] = ['pid'=>$pid,'q'=>$q,'up'=>$up,'subtotal'=>$subtotal];
    }
    // insert purchases meta
    $stmt = $conn->prepare('INSERT INTO purchases (supplier_id, purchase_date, total_amount) VALUES (?, ?, ?)');
    $stmt->bind_param('isd', $supplier_id, $purchase_date, $total);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    $purchase_id = $stmt->insert_id;
    $stmt->close();

    // insert items and update stock (increase)
    $stmtItem = $conn->prepare('INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
    $stmtUpdate = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');

    foreach ($items as $it){
        $stmtItem->bind_param('iiidd', $purchase_id, $it['pid'], $it['q'], $it['up'], $it['subtotal']);
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
