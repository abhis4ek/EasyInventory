<?php
require 'db.php';
header('Content-Type: application/json');
$sql = "SELECT p.id, p.name, p.price, p.stock, p.category_id, c.name AS category_name, p.supplier_id, s.name AS supplier_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        ORDER BY p.name";
$res = $conn->query($sql);
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
?>
