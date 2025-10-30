<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error', 'msg'=>'Not authenticated']);
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];

$id = (int)$_POST['id'];
$name = trim($_POST['name'] ?? '');
$street_address = trim($_POST['street_address'] ?? '');
$city = trim($_POST['city'] ?? '');
$pin_code = trim($_POST['pin_code'] ?? '');
$state = trim($_POST['state'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validation
$errors = [];

if (empty($name)) $errors[] = "Name is required";
if (empty($street_address)) $errors[] = "Street address is required";
if (empty($city)) $errors[] = "City is required";
if (empty($pin_code)) $errors[] = "Pin code is required";
if (empty($state)) $errors[] = "State is required";
if (empty($phone)) $errors[] = "Phone is required";

// Validate phone (10 digits, Indian format)
if (!empty($phone) && !preg_match('/^[6-9]\d{9}$/', $phone)) {
    $errors[] = "Phone must be a valid 10-digit Indian mobile number";
}

// Validate email if provided
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

// Validate pin code (6 digits)
if (!empty($pin_code) && !preg_match('/^\d{6}$/', $pin_code)) {
    $errors[] = "Pin code must be exactly 6 digits";
}

if (!empty($errors)) {
    echo json_encode(['status'=>'error', 'msg'=>implode(', ', $errors)]);
    exit();
}

// Convert empty email to NULL
$email = $email ?: null;

$stmt = $conn->prepare('UPDATE suppliers SET name = ?, street_address = ?, city = ?, pin_code = ?, state = ?, phone = ?, email = ? WHERE id = ? AND admin_id = ?');
$stmt->bind_param('sssssssii', $name, $street_address, $city, $pin_code, $state, $phone, $email, $id, $admin_id);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error', 'msg'=>$stmt->error]);
}
?>