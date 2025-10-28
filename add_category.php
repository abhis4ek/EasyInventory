<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','msg'=>'Not authenticated']);
    exit;
}

require 'db.php';
$admin_id = $_SESSION['admin_id'];
$name = trim($_POST['name'] ?? '');

if ($name === '') { 
    echo 'invalid'; 
    exit; 
}

$stmt = $conn->prepare("INSERT INTO categories (admin_id, name, description) VALUES (?, ?, '')");
$stmt->bind_param('is', $admin_id, $name);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success','id'=>$stmt->insert_id,'name'=>$name]);
} else {
    echo json_encode(['status'=>'error','msg'=>$stmt->error]);
}
?>