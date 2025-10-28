<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

if ($name === '') { 
    echo json_encode(['status'=>'invalid']); 
    exit; 
}

// ✅ Fixed: Correct parameter order
$stmt = $conn->prepare("INSERT INTO suppliers (admin_id, name, email, phone, address) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('issss', $admin_id, $name, $email, $phone, $address);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success', 'id'=>$stmt->insert_id, 'name'=>$name]);
} else {
    echo json_encode(['status'=>'error', 'msg'=>$stmt->error]);
}
?>