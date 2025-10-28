<?php
require 'db.php';
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    
    // Convert empty strings to NULL for optional fields
    $description = $description ?: null;
    
    $stmt = $conn->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
    $stmt->bind_param('ss', $name, $description);
    
    if ($stmt->execute()) {
        $stmt->close();
        // Redirect to prevent form resubmission
        header('Location: categories.php?success=1');
        exit;
    } else {
        $message = 'Error: ' . $stmt->error;
        $stmt->close();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: categories.php');
        exit;
    } else {
        $message = 'Error: Cannot delete category. It may be in use by products.';
        $stmt->close();
    }
}

// Success message after redirect
if (isset($_GET['success'])) {
    $message = 'Category added successfully!';
}

$res = $conn->query('SELECT * FROM categories ORDER BY id');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Categories</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .message { padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; margin-bottom: 15px; border-radius: 4px; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        form { margin-bottom: 20px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, textarea { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 80px; resize: vertical; }
        button { margin-top: 10px; padding: 8px 15px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
        button:hover { background: #0056b3; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th { background: #f8f9fa; font-weight: bold; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f5f5f5; }
        .delete-link { color: red; text-decoration: none; }
        .delete-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Categories Management</h2>
    
    <?php if($message): ?>
        <div class="message <?= strpos($message, 'Error') !== false ? 'error' : '' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="categories.php">
        <label>Name: <input name="name" required placeholder="Enter category name"></label>
        <label>Description: <textarea name="description" placeholder="Enter description (optional)"></textarea></label>
        <button type="submit">Add Category</button>
    </form>
    
    <h3>Existing Categories</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Action</th>
        </tr>
        <?php if($res && $res->num_rows > 0): ?>
            <?php while($r = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['description'] ?? '') ?></td>
                <td>
                    <a href="categories.php?delete=<?= $r['id'] ?>" 
                       class="delete-link"
                       onclick="return confirm('Delete this category? Note: Cannot delete if products exist in this category.')">
                       Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No categories found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>