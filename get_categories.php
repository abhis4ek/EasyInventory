<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([]);
    exit();
}
require 'db.php';
header('Content-Type: application/json');

$admin_id = $_SESSION['admin_id'];

// ✅ Filter categories by admin_id - only return user's own categories
$stmt = $conn->prepare("SELECT id, name FROM categories WHERE admin_id = ? ORDER BY name");
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = $r;
}

echo json_encode($out);
$stmt->close();
?>