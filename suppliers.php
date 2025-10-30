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
    $street_address = trim($_POST['street_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $pin_code = trim($_POST['pin_code'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) $errors[] = "Name is required";
    if (empty($street_address)) $errors[] = "Street Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($pin_code)) $errors[] = "Pin Code is required";
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
        $errors[] = "Pin Code must be exactly 6 digits";
    }
    
    if (empty($errors)) {
        // Convert empty email to NULL
        $email = $email ?: null;
        
        $stmt = $conn->prepare('INSERT INTO suppliers (admin_id, name, street_address, city, pin_code, state, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssssss', $admin_id, $name, $street_address, $city, $pin_code, $state, $phone, $email);
        
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: suppliers.php?success=1');
            exit;
        } else {
            $message = 'Error: ' . $stmt->error;
            $stmt->close();
        }
    } else {
        $message = 'Error: ' . implode(', ', $errors);
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare('DELETE FROM suppliers WHERE id = ? AND admin_id = ?');
    $stmt->bind_param('ii', $id, $admin_id);
    $stmt->execute();
    $stmt->close();
    
    header('Location: suppliers.php');
    exit;
}

// Success message after redirect
if (isset($_GET['success'])) {
    $message = 'Supplier added successfully!';
}

$stmt = $conn->prepare('SELECT * FROM suppliers WHERE admin_id = ? ORDER BY id');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers Management</title>
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
            max-width: 1400px;
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
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        
        label .required {
            color: #dc3545;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        input.invalid {
            border-color: #dc3545;
        }
        
        .info-text {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        
        .error-text {
            font-size: 12px;
            color: #dc3545;
            margin-top: 4px;
            display: none;
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
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
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
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 700px;
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
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Suppliers Management</h2>
        
        <?php if($message): ?>
            <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="suppliers.php" id="supplierForm" novalidate>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="name">Supplier Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required placeholder="Enter supplier name">
                    <div class="error-text" id="name_error">Name is required</div>
                </div>
                <div class="form-group full-width">
                    <label for="street_address">Street Address <span class="required">*</span></label>
                    <input type="text" id="street_address" name="street_address" required placeholder="Enter street address">
                    <div class="error-text" id="street_error">Street address is required</div>
                </div>
                <div class="form-group">
                    <label for="city">City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" required placeholder="Enter city">
                    <div class="error-text" id="city_error">City is required</div>
                </div>
                <div class="form-group">
                    <label for="pin_code">Pin Code <span class="required">*</span></label>
                    <input type="text" id="pin_code" name="pin_code" required placeholder="6 digits" maxlength="6" pattern="\d{6}">
                    <div class="info-text">Must be 6 digits</div>
                    <div class="error-text" id="pin_error">Pin code must be exactly 6 digits</div>
                </div>
                <div class="form-group">
                    <label for="state">State <span class="required">*</span></label>
                    <input type="text" id="state" name="state" required placeholder="Enter state">
                    <div class="error-text" id="state_error">State is required</div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" required placeholder="10 digits" maxlength="10" pattern="[6-9]\d{9}">
                    <div class="info-text">10-digit Indian mobile number (starts with 6-9)</div>
                    <div class="error-text" id="phone_error">Phone must be a valid 10-digit number starting with 6-9</div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter email address">
                    <div class="info-text">Optional</div>
                    <div class="error-text" id="email_error">Invalid email format</div>
                </div>
            </div>
            <button type="submit">Add Supplier</button>
        </form>
        
        <h3>Existing Suppliers</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Street Address</th>
                        <th>City</th>
                        <th>Pin Code</th>
                        <th>State</th>
                        <th>Phone</th>
                        <th>Email</th>
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
                            <td><?= htmlspecialchars($r['street_address'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['city'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['pin_code'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['state'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                            <td>
                                <a href="#" 
                                   class="action-link edit"
                                   onclick="openEditModal(<?= $r['id'] ?>, '<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['street_address'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['city'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['pin_code'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['state'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['phone'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($r['email'] ?? '', ENT_QUOTES) ?>'); return false;">
                                   Edit
                                </a>
                                <a href="suppliers.php?delete=<?= $r['id'] ?>" 
                                   class="action-link"
                                   onclick="return confirm('Delete this supplier?')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">No suppliers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Supplier</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editForm" novalidate>
                <input type="hidden" id="edit_id" name="id">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="edit_name">Supplier Name <span class="required">*</span></label>
                        <input type="text" id="edit_name" name="name" required>
                        <div class="error-text" id="edit_name_error">Name is required</div>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_street_address">Street Address <span class="required">*</span></label>
                        <input type="text" id="edit_street_address" name="street_address" required>
                        <div class="error-text" id="edit_street_error">Street address is required</div>
                    </div>
                    <div class="form-group">
                        <label for="edit_city">City <span class="required">*</span></label>
                        <input type="text" id="edit_city" name="city" required>
                        <div class="error-text" id="edit_city_error">City is required</div>
                    </div>
                    <div class="form-group">
                        <label for="edit_pin_code">Pin Code <span class="required">*</span></label>
                        <input type="text" id="edit_pin_code" name="pin_code" required maxlength="6" pattern="\d{6}">
                        <div class="error-text" id="edit_pin_error">Pin code must be exactly 6 digits</div>
                    </div>
                    <div class="form-group">
                        <label for="edit_state">State <span class="required">*</span></label>
                        <input type="text" id="edit_state" name="state" required>
                        <div class="error-text" id="edit_state_error">State is required</div>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone <span class="required">*</span></label>
                        <input type="tel" id="edit_phone" name="phone" required maxlength="10" pattern="[6-9]\d{9}">
                        <div class="error-text" id="edit_phone_error">Phone must be a valid 10-digit number</div>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email">
                        <div class="error-text" id="edit_email_error">Invalid email format</div>
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validation function
        function validateField(field, errorElement, validationFn, errorMsg) {
            const value = field.value.trim();
            const isValid = validationFn(value);
            
            if (!isValid) {
                field.classList.add('invalid');
                errorElement.style.display = 'block';
                if (errorMsg) errorElement.textContent = errorMsg;
            } else {
                field.classList.remove('invalid');
                errorElement.style.display = 'none';
            }
            
            return isValid;
        }

        // Validation patterns
        const phonePattern = /^[6-9]\d{9}$/;
        const pinPattern = /^\d{6}$/;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Add form validation
        document.getElementById('supplierForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            
            // Name validation
            isValid &= validateField(
                document.getElementById('name'),
                document.getElementById('name_error'),
                (v) => v.length > 0,
                'Name is required'
            );
            
            // Street address validation
            isValid &= validateField(
                document.getElementById('street_address'),
                document.getElementById('street_error'),
                (v) => v.length > 0,
                'Street address is required'
            );
            
            // City validation
            isValid &= validateField(
                document.getElementById('city'),
                document.getElementById('city_error'),
                (v) => v.length > 0,
                'City is required'
            );
            
            // Pin code validation
            isValid &= validateField(
                document.getElementById('pin_code'),
                document.getElementById('pin_error'),
                (v) => pinPattern.test(v),
                'Pin code must be exactly 6 digits'
            );
            
            // State validation
            isValid &= validateField(
                document.getElementById('state'),
                document.getElementById('state_error'),
                (v) => v.length > 0,
                'State is required'
            );
            
            // Phone validation
            isValid &= validateField(
                document.getElementById('phone'),
                document.getElementById('phone_error'),
                (v) => phonePattern.test(v),
                'Phone must be a valid 10-digit number starting with 6-9'
            );
            
            // Email validation (optional but must be valid if provided)
            const emailField = document.getElementById('email');
            const emailValue = emailField.value.trim();
            if (emailValue.length > 0) {
                isValid &= validateField(
                    emailField,
                    document.getElementById('email_error'),
                    (v) => emailPattern.test(v),
                    'Invalid email format'
                );
            }
            
            if (isValid) {
                this.submit();
            }
        });

        // Real-time validation for phone
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 10);
        });

        document.getElementById('edit_phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 10);
        });

        // Real-time validation for pin code
        document.getElementById('pin_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });

        document.getElementById('edit_pin_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });

        function openEditModal(id, name, street, city, pin, state, phone, email) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_street_address').value = street;
            document.getElementById('edit_city').value = city;
            document.getElementById('edit_pin_code').value = pin;
            document.getElementById('edit_state').value = state;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_email').value = email;
            
            // Clear any previous validation errors
            document.querySelectorAll('#editForm .error-text').forEach(el => el.style.display = 'none');
            document.querySelectorAll('#editForm input').forEach(el => el.classList.remove('invalid'));
            
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

        // Handle edit form submission with validation
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            
            // Validate all fields in edit form
            isValid &= validateField(
                document.getElementById('edit_name'),
                document.getElementById('edit_name_error'),
                (v) => v.length > 0,
                'Name is required'
            );
            
            isValid &= validateField(
                document.getElementById('edit_street_address'),
                document.getElementById('edit_street_error'),
                (v) => v.length > 0,
                'Street address is required'
            );
            
            isValid &= validateField(
                document.getElementById('edit_city'),
                document.getElementById('edit_city_error'),
                (v) => v.length > 0,
                'City is required'
            );
            
            isValid &= validateField(
                document.getElementById('edit_pin_code'),
                document.getElementById('edit_pin_error'),
                (v) => pinPattern.test(v),
                'Pin code must be exactly 6 digits'
            );
            
            isValid &= validateField(
                document.getElementById('edit_state'),
                document.getElementById('edit_state_error'),
                (v) => v.length > 0,
                'State is required'
            );
            
            isValid &= validateField(
                document.getElementById('edit_phone'),
                document.getElementById('edit_phone_error'),
                (v) => phonePattern.test(v),
                'Phone must be a valid 10-digit number'
            );
            
            // Email validation (optional)
            const editEmailField = document.getElementById('edit_email');
            const editEmailValue = editEmailField.value.trim();
            if (editEmailValue.length > 0) {
                isValid &= validateField(
                    editEmailField,
                    document.getElementById('edit_email_error'),
                    (v) => emailPattern.test(v),
                    'Invalid email format'
                );
            }
            
            if (!isValid) return;
            
            const formData = new FormData(this);
            
            fetch('update_supplier.php', {
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
                alert('Error updating supplier');
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>