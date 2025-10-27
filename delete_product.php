<?php
require 'db.php';
$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo 'invalid'; exit;}
$stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
$stmt->bind_param('i',$id);
if ($stmt->execute()) echo 'success'; else echo 'error';
?>
