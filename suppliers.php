<?php
require 'db.php';
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Convert empty strings to NULL for optional fields
    $email = $email ?: null;
    $phone = $phone ?: null;
    $address = $address ?: null;
    
    $stmt = $conn->prepare('INSERT INTO suppliers (name, email, phone, address) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $phone, $address);
    
    if ($stmt->execute()) {
        $stmt->close();
        // Redirect to prevent form resubmission
        header('Location: suppliers.php?success=1');
        exit;
    } else {
        $message = 'Error: ' . $stmt->error;
        $stmt->close();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM suppliers WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: suppliers.php');
    exit;
}

// Success message after redirect
if (isset($_GET['success'])) {
    $message = 'Supplier added successfully!';
}

$res = $conn->query('SELECT * FROM suppliers ORDER BY id');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Suppliers</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .message { padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; margin-bottom: 15px; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        form { margin-bottom: 20px; }
        label { display: block; margin: 10px 0 5px; }
        input { padding: 5px; width: 300px; }
        button { margin-top: 10px; padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #f8f9fa; }
        th, td { padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <h2>Suppliers Management</h2>
    
    <?php if($message): ?>
        <div class="message <?= strpos($message, 'Error') !== false ? 'error' : '' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="suppliers.php">
        <label>Name: <input name="name" required></label>
        <label>Email: <input name="email" type="email"></label>
        <label>Phone: <input name="phone"></label>
        <label>Address: <input name="address"></label>
        <button type="submit">Add Supplier</button>
    </form>
    
    <h3>Existing Suppliers</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
        <?php if($res && $res->num_rows > 0): ?>
            <?php while($r = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['address'] ?? '') ?></td>
                <td>
                    <a href="suppliers.php?delete=<?= $r['id'] ?>" 
                       onclick="return confirm('Delete this supplier?')"
                       style="color: red;">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No suppliers found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>