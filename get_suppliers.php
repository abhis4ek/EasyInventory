<?php
require 'db.php';
header('Content-Type: application/json');

$res = $conn->query("SELECT id, name FROM suppliers ORDER BY name");
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
?>
