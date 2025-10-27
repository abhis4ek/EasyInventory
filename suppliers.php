<?php
require 'db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? null;
    $stmt = $conn->prepare('INSERT INTO suppliers (name, email, phone, address) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $phone, $address);
    if ($stmt->execute()) $message='Supplier added.'; else $message='Error: '.$stmt->error;
    $stmt->close();
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM suppliers WHERE id = ?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $stmt->close();
    header('Location: suppliers.php'); exit;
}
$res = $conn->query('SELECT * FROM suppliers ORDER BY id');
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Suppliers</title></head><body>
<h2>Suppliers</h2>
<?php if($message) echo '<p>'.htmlspecialchars($message).'</p>'; ?>
<form method="POST">
<label>Name: <input name="name" required></label><br>
<label>Email: <input name="email"></label><br>
<label>Phone: <input name="phone"></label><br>
<label>Address: <input name="address"></label><br>
<button type="submit">Add Supplier</button>
</form>
<table border="1" cellpadding="5" cellspacing="0">
<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Action</th></tr>
<?php while($r=$res->fetch_assoc()): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= htmlspecialchars($r['name']) ?></td>
<td><?= htmlspecialchars($r['email']) ?></td>
<td><?= htmlspecialchars($r['phone']) ?></td>
<td><?= htmlspecialchars($r['address']) ?></td>
<td><a href="suppliers.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>
</body></html>
