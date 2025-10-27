<?php
require 'db.php';
$message = '';
// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'] ?? '';
    $stmt = $conn->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
    $stmt->bind_param('ss', $name, $desc);
    if ($stmt->execute()) {
        $message = 'Category added.';
    } else {
        $message = 'Error: '.$stmt->error;
    }
    $stmt->close();
}
// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $stmt->close();
    header('Location: categories.php');
    exit;
}

$res = $conn->query('SELECT * FROM categories ORDER BY id');
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Categories</title></head><body>
<h2>Categories</h2>
<?php if($message) echo '<p>'.htmlspecialchars($message).'</p>'; ?>
<form method="POST">
<label>Name: <input name="name" required></label><br>
<label>Description: <input name="description"></label><br>
<button type="submit">Add Category</button>
</form>
<table border="1" cellpadding="5" cellspacing="0">
<tr><th>ID</th><th>Name</th><th>Description</th><th>Action</th></tr>
<?php while($r=$res->fetch_assoc()): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= htmlspecialchars($r['name']) ?></td>
<td><?= htmlspecialchars($r['description']) ?></td>
<td><a href="categories.php?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>
</body></html>
