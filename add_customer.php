<?php
require 'db.php';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
if ($name === '') { echo json_encode(['status'=>'invalid']); exit; }
$stmt = $conn->prepare("INSERT INTO customers (name,email,phone,address) VALUES (?,?,?,?)");
$stmt->bind_param('ssss',$name,$email,$phone,$address);
if ($stmt->execute()) echo json_encode(['status'=>'success','id'=>$stmt->insert_id,'name'=>$name]);
else echo json_encode(['status'=>'error','msg'=>$stmt->error]);
?>
