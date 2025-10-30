<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','msg'=>'Not authenticated']);
    exit;
}

require 'db.php';
$admin_id = $_SESSION['admin_id'];
$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$gst_rate = floatval($_POST['gst_rate'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status'=>'error','msg'=>'Invalid category ID']);
    exit;
}

if ($name === '') { 
    echo json_encode(['status'=>'error','msg'=>'Category name is required']);
    exit; 
}

// Validate GST rate
if ($gst_rate < 0 || $gst_rate > 100) {
    echo json_encode(['status'=>'error','msg'=>'GST rate must be between 0 and 100']);
    exit;
}

// Convert empty description to NULL
$description = $description ?: null;

// Update only if category belongs to the admin
$stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, gst_rate = ? WHERE id = ? AND admin_id = ?");
$stmt->bind_param('ssdii', $name, $description, $gst_rate, $id, $admin_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'status'=>'success',
            'id'=>$id,
            'name'=>$name,
            'description'=>$description,
            'gst_rate'=>$gst_rate
        ]);
    } else {
        echo json_encode(['status'=>'error','msg'=>'Category not found or no changes made']);
    }
} else {
    echo json_encode(['status'=>'error','msg'=>$stmt->error]);
}
$stmt->close();
?>