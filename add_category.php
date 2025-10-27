<?php
require 'db.php';
$name = trim($_POST['name'] ?? '');
if ($name === '') { echo 'invalid'; exit; }
$stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, '')");
$stmt->bind_param('s', $name);
if ($stmt->execute()) {
  echo json_encode(['status'=>'success','id'=>$stmt->insert_id,'name'=>$name]);
} else {
  echo json_encode(['status'=>'error','msg'=>$stmt->error]);
}
?>
