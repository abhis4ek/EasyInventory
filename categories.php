<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $gst_rate = floatval($_POST['gst_rate'] ?? 0);
    
    // Convert empty strings to NULL for optional fields
    $description = $description ?: null;
    
    // Validate GST rate (should be between 0 and 100)
    if ($gst_rate < 0 || $gst_rate > 100) {
        $message = 'Error: GST rate must be between 0 and 100';
    } else {
        $stmt = $conn->prepare('INSERT INTO categories (admin_id, name, description, gst_rate) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('issd', $admin_id, $name, $description, $gst_rate);
        
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: categories.php?success=1');
            exit;
        } else {
            $message = 'Error: ' . $stmt->error;
            $stmt->close();
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare('DELETE FROM categories WHERE id = ? AND admin_id = ?');
    $stmt->bind_param('ii', $id, $admin_id);
    
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

$stmt = $conn->prepare('SELECT * FROM categories WHERE admin_id = ? ORDER BY id');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 40px 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .message {
            padding: 12px 16px;
            margin-bottom: 25px;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        form {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 3fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .gst-input-group {
            position: relative;
        }
        
        .gst-input-group input {
            padding-right: 30px;
        }
        
        .gst-input-group::after {
            content: '%';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-weight: 600;
        }
        
        button {
            background-color: #4a90e2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        
        button:hover {
            background-color: #357abd;
        }
        
        h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            text-align: left;
            padding: 12px;
            font-size: 13px;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .gst-badge {
            display: inline-block;
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .action-link {
            color: #dc3545;
            text-decoration: none;
            font-weight: 500;
            margin-right: 15px;
        }
        
        .action-link.edit {
            color: #28a745;
        }
        
        .action-link:hover {
            text-decoration: underline;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
        }
        
        .info-text {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }
        
        .close:hover {
            color: #000;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .modal-buttons button {
            flex: 1;
        }
        
        .btn-cancel {
            background-color: #6c757d;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            body {
                padding: 20px 10px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 20% auto;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Categories Management</h2>
        
        <?php if($message): ?>
            <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="categories.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Electronics">
                </div>
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <input type="text" id="description" name="description" placeholder="Brief description">
                </div>
                <div class="form-group">
                    <label for="gst_rate">GST Rate *</label>
                    <div class="gst-input-group">
                        <input type="number" id="gst_rate" name="gst_rate" step="0.01" min="0" max="100" value="0" required>
                    </div>
                    <div class="info-text">0-100%</div>
                </div>
            </div>
            <button type="submit">Add Category</button>
        </form>
        
        <h3>Existing Categories</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>GST Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($res && $res->num_rows > 0): ?>
                    <?php $rowNum = 1; ?>
                    <?php while($r = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $rowNum++ ?></td>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['description'] ?? '') ?></td>
                        <td>
                            <span class="gst-badge"><?= number_format($r['gst_rate'] ?? 0, 2) ?>%</span>
                        </td>
                        <td>
                            <a href="#" 
                               class="action-link edit"
                               onclick="openEditModal(<?= $r['id'] ?>, '<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['description'] ?? '', ENT_QUOTES) ?>', <?= $r['gst_rate'] ?? 0 ?>); return false;">
                               Edit
                            </a>
                            <a href="categories.php?delete=<?= $r['id'] ?>" 
                               class="action-link"
                               onclick="return confirm('Delete this category? Note: Cannot delete if products exist in this category.')">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="no-data">No categories found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Category</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_name">Category Name *</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description (Optional)</label>
                    <input type="text" id="edit_description" name="description">
                </div>
                <div class="form-group">
                    <label for="edit_gst_rate">GST Rate *</label>
                    <div class="gst-input-group">
                        <input type="number" id="edit_gst_rate" name="gst_rate" step="0.01" min="0" max="100" required>
                    </div>
                    <div class="info-text">0-100%</div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit">Update Category</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, description, gstRate) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_gst_rate').value = gstRate;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }

        // Handle edit form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('update_category.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + data.msg);
                }
            })
            .catch(error => {
                alert('Error updating category');
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>