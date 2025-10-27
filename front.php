<?php
require 'db.php';

// Total products
$sql_total = "SELECT COUNT(DISTINCT id) AS total FROM products";
$total_result = $conn->query($sql_total);
$total_products = $total_result->fetch_assoc()['total'];

// In stock (>20)
$sql_instock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE stock > 20";
$instock_result = $conn->query($sql_instock);
$in_stock = $instock_result->fetch_assoc()['total'];

// Low stock (1-20)
$sql_lowstock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE stock > 0 AND stock <= 20";
$lowstock_result = $conn->query($sql_lowstock);
$low_stock = $lowstock_result->fetch_assoc()['total'];

// Out of stock (0)
$sql_outstock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE stock = 0";
$outstock_result = $conn->query($sql_outstock);
$out_of_stock = $outstock_result->fetch_assoc()['total'];

// total purchases
$sql = "SELECT COUNT(*) AS total FROM purchases";
$r = $conn->query($sql)->fetch_assoc(); $total_purchases = $r['total'];

// total sales
$sql = "SELECT COUNT(*) AS total FROM sales";
$r = $conn->query($sql)->fetch_assoc(); $total_sales = $r['total'];

               


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo h1 {
            font-size: 1.8rem;
        }
        
        .logo span {
            color: #3498db;
        }
        
        .menu {
            list-style: none;
            padding: 0 15px;
        }
        
        .menu-item {
            margin-bottom: 5px;
        }
        
        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .menu-link:hover, .menu-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .menu-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e6ed;
        }
        
        .page-title {
            font-size: 1.8rem;
            color: #2c3e50;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-right: 15px;
        }
        
        .card-info h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .card-info p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .bg-primary { background-color: #e3f2fd; color: #1976d2; }
        .bg-success { background-color: #e8f5e9; color: #388e3c; }
        .bg-warning { background-color: #fff8e1; color: #f57c00; }
        .bg-danger { background-color: #ffebee; color: #d32f2f; }
        
        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 1.3rem;
            color: #2c3e50;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e6ed;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-instock {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .status-lowstock {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .status-outstock {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .edit-btn { color: #3498db; }
        .delete-btn { color: #e74c3c; }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e6ed;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
            padding: 25px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e6ed;
        }
        
        .modal-title {
            font-size: 1.3rem;
            color: #2c3e50;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-info {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h1>Easy<span>Inventory</span></h1>
            </div>
            <ul class="menu">
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                 <li class="menu-item">
                    <a href="#" class="menu-link">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>

                <li class="menu-item">
                    <a href="categories.php" class="menu-link">
                        <i class="fas fa-boxes"></i> Categories
                    </a>
                </li>
                <li class="menu-item">
                    <a href="suppliers.php" class="menu-link">
                        <i class="fas fa-users"></i> Suppliers
                    </a>
                </li>
                <!-- inside .menu -->
<li class="menu-item">
  <a href="customers.php" class="menu-link">
    <i class="fas fa-users"></i> Customers
  </a>
</li>

                <li class="menu-item">
                     <a href="purchases.php" class="menu-link">
                        <i class="fas fa-truck-loading"></i> Purchases
                     </a>
                </li>

                <li class="menu-item">
                      <a href="sales.php" class="menu-link">
                           <i class="fas fa-shopping-bag"></i> Sales
                      </a>
                </li>

            
                <li class="menu-item">
                    <a href="reports.html" class="menu-link">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <li class="menu-item">
                   <a href="logout.php" class="menu-link" onclick="return confirm('Are you sure you want to logout?');">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2 class="page-title">Dashboard</h2>
                <div class="user-info">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=3498db&color=fff" alt="User">
                    <span>Admin User</span>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
    <div class="card-icon bg-primary">
        <i class="fas fa-box"></i>
    </div>
    <div class="card-info">
        <h3><?php echo $total_products; ?></h3>
        <p>Total Products</p>
    </div>
</div>

<div class="card">
    <div class="card-icon bg-success">
        <i class="fas fa-check-circle"></i>
    </div>
    <div class="card-info">
        <h3><?php echo $in_stock; ?></h3>
        <p>In Stock</p>
    </div>
</div>

<div class="card">
    <div class="card-icon bg-warning">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="card-info">
        <h3><?php echo $low_stock; ?></h3>
        <p>Low Stock</p>
    </div>
</div>

<div class="card">
    <div class="card-icon bg-danger">
        <i class="fas fa-times-circle"></i>
    </div>
    <div class="card-info">
        <h3><?php echo $out_of_stock; ?></h3>
        <p>Out of Stock</p>
    </div>
</div>

                
            </div>

            <!-- Products Section -->
            <div class="content-section">
                <div class="section-header">
                    <h3 class="section-title">Products</h3>
                    <button class="btn btn-primary" id="addProductBtn">Add Product</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                           <?php
                           // Fetch products along with category name
                           $stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, p.stock, p.description, p.category_id, c.name AS category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY 
        CASE 
            WHEN p.stock > 20 THEN 1
            WHEN p.stock > 0 THEN 2
            ELSE 3
        END, p.name
");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($product = $result->fetch_assoc()) { ?>
        <tbody>
        <tr>
            <td><?php echo $product['name']; ?></td>
            <td><?php echo $product['category_name']; ?></td>
            <td><?php echo $product['price']; ?></td>
            <td><?php echo $product['stock']; ?></td>
            <td>
                <span class="status <?php
                    if($product['stock'] > 20) echo 'status-instock';
                    elseif($product['stock'] > 0) echo 'status-lowstock';
                    else echo 'status-outstock';
                ?>">
                <?php
                    if($product['stock'] > 20) echo 'In Stock';
                    elseif($product['stock'] > 0) echo 'Low Stock';
                    else echo 'Out of Stock';
                ?>
                </span>
            </td>
            <td>
                <button class="action-btn edit-btn" 
                    onclick='openEditModal(<?php echo json_encode($product); ?>)'>
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                    <i class="fas fa-trash"></i>
                </button>


            </td>
         </tr>
         </tbody>
<?php }
} else {
    echo "<tbody><tr><td colspan='6' style='text-align:center;'>No products found</td></tr></tbody>";
}

?>
                    </table>
                </div>
            </div>

           
    <!-- Add Product Modal -->
<div class="modal" id="addProductModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Product</h3>
            <button class="close-btn" id="closeModalBtn">&times;</button>
        </div>

        <div class="modal-body">
            <div class="form-group">
                <label for="productName">Product Name</label>
                <input type="text" class="form-control" id="productName" placeholder="Enter product name" required>
            </div>

            <div class="form-group">
                <label for="categorySelect">Category</label>
                <select id="categorySelect" name="category_id" required>
                    <option value="">Loading...</option>
                </select>
            </div>

            <div class="form-group">
                <label for="productPrice">Price (₹)</label>
                <input type="number" class="form-control" id="productPrice" placeholder="Enter price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="productStock">Stock Quantity</label>
                <input type="number" class="form-control" id="productStock" placeholder="Enter stock quantity" required>
            </div>

            <div class="form-group">
                <label for="productDescription">Description</label>
                <textarea class="form-control" id="productDescription" rows="3" placeholder="Enter product description"></textarea>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" id="cancelBtn" style="margin-right: 10px;">Cancel</button>
            <button type="button" class="btn btn-primary" id="saveProductBtn">Save Product</button>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal" id="editProductModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Product</h3>
            <button class="close-btn" id="closeEditModalBtn">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editProductId"> <!-- product id -->
            <div class="form-group">
                <label for="editProductName">Product Name</label>
                <input type="text" class="form-control" id="editProductName">
            </div>
            <div class="form-group">
                <label for="editCategorySelect">Category</label>
                <select id="editCategorySelect"></select>
            </div>
            <div class="form-group">
                <label for="editProductPrice">Price (₹)</label>
                <input type="number" class="form-control" id="editProductPrice" step="0.01">
            </div>
            <div class="form-group">
                <label for="editProductStock">Stock Quantity</label>
                <input type="number" class="form-control" id="editProductStock">
            </div>
            <div class="form-group">
                <label for="editProductDescription">Description</label>
                <textarea class="form-control" id="editProductDescription" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" id="cancelEditBtn" style="margin-right: 10px;">Cancel</button>
            <button class="btn btn-primary" id="saveEditProductBtn">Save Changes</button>
        </div>
    </div>
</div>



<script>
  // Modal elements
  const modal = document.getElementById('addProductModal');
  const addProductBtn = document.getElementById('addProductBtn');
  const closeBtn = document.getElementById('closeModalBtn'); // close (×) button
  const cancelBtn = document.getElementById('cancelBtn');
  const saveProductBtn = document.getElementById('saveProductBtn');

  // Function to fetch categories dynamically
  function loadCategories() {
    fetch('get_categories.php')
      .then(response => response.json())
      .then(categories => {
        const categorySelect = document.getElementById('categorySelect');
        categorySelect.innerHTML = '<option value="">Select category</option>'; // default option
        categories.forEach(cat => {
          const option = document.createElement('option');
          option.value = cat.id;
          option.textContent = cat.name;
          categorySelect.appendChild(option);
        });
      })
      .catch(error => {
        console.error("Error loading categories:", error);
        document.getElementById('categorySelect').innerHTML =
          '<option value="">Error loading categories</option>';
      });
  }

  // Open modal
  addProductBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
    loadCategories(); // load categories each time modal opens
  });

  // Close modal (× button)
  closeBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Close modal (Cancel button)
  cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  // Close modal if clicking outside content
  window.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none';
  });

  // Save Product button logic
  saveProductBtn.addEventListener('click', () => {
    const name = document.getElementById('productName').value.trim();
    const category_id = document.getElementById('categorySelect').value;
    const price = document.getElementById('productPrice').value.trim();
    const stock = document.getElementById('productStock').value.trim();
    const description = document.getElementById('productDescription').value.trim();

    // Basic validation
    if (!name || !category_id || !price || !stock) {
      alert('Please fill in all required fields.');
      return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('category_id', category_id);
    formData.append('price', price);
    formData.append('stock', stock);
    formData.append('description', description);

    fetch('add_product.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.text())
    .then(result => {
      if (result.trim() === 'success') {
        alert('Product added successfully!');
        modal.style.display = 'none';
        // Clear form fields
        document.getElementById('productName').value = '';
        document.getElementById('productPrice').value = '';
        document.getElementById('productStock').value = '';
        document.getElementById('productDescription').value = '';
        // Reload the page to show updated product list
        location.reload();
      } else {
        alert('Error adding product: ' + result);
      }
    })
    .catch(error => {
      alert('Fetch error: ' + error);
    });
  });
</script>
<script>
  // ================= Edit Product Modal Script =================
  const editModal = document.getElementById('editProductModal');
const closeEditBtn = document.getElementById('closeEditModalBtn');
const cancelEditBtn = document.getElementById('cancelEditBtn');
const saveEditProductBtn = document.getElementById('saveEditProductBtn');

// Function to fetch categories dynamically for Edit Modal
function loadEditCategories(selectedId = null) {
  fetch('get_categories.php')
    .then(response => response.json())
    .then(categories => {
      const categorySelect = document.getElementById('editCategorySelect');
      categorySelect.innerHTML = '<option value="">Select category</option>';
      categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name;
        if (cat.id == selectedId) option.selected = true;
        categorySelect.appendChild(option);
      });
    })
    .catch(error => {
      console.error("Error loading categories:", error);
      document.getElementById('editCategorySelect').innerHTML =
        '<option value="">Error loading categories</option>';
    });
}

// Function to open Edit Modal and fill data
function openEditModal(product) {
  editModal.style.display = 'flex';
  document.getElementById('editProductId').value = product.id;
  document.getElementById('editProductName').value = product.name;
  document.getElementById('editProductPrice').value = product.price;
  document.getElementById('editProductStock').value = product.stock;
  document.getElementById('editProductDescription').value = product.description;
  loadEditCategories(product.category_id);
}

// Close modal
closeEditBtn.addEventListener('click', () => { editModal.style.display = 'none'; });
cancelEditBtn.addEventListener('click', () => { editModal.style.display = 'none'; });
window.addEventListener('click', (e) => { if (e.target === editModal) editModal.style.display = 'none'; });

// Save edited product
saveEditProductBtn.addEventListener('click', () => {
  const id = document.getElementById('editProductId').value;
  const name = document.getElementById('editProductName').value.trim();
  const category_id = document.getElementById('editCategorySelect').value;
  const price = document.getElementById('editProductPrice').value.trim();
  const stock = document.getElementById('editProductStock').value.trim();
  const description = document.getElementById('editProductDescription').value.trim();

  if (!name || !category_id || !price || !stock) {
    alert('Please fill in all required fields.');
    return;
  }

  const formData = new FormData();
  formData.append('id', id);
  formData.append('name', name);
  formData.append('category_id', category_id);
  formData.append('price', price);
  formData.append('stock', stock);
  formData.append('description', description);

  fetch('edit_product.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(result => {
    if (result.trim() === 'success') {
      alert('Product updated successfully!');
      editModal.style.display = 'none';
      location.reload();
    } else {
      alert('Error updating product: ' + result);
    }
  })
  .catch(error => { alert('Fetch error: ' + error); });
});
</script>

<script>
function deleteProduct(id) {
    if (!confirm("Are you sure you want to delete this product?")) {
        return;
    }

    const formData = new FormData();
    formData.append('id', id);

    fetch('delete_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        if (result.trim() === 'success') {
            alert('Product deleted successfully!');
            location.reload();
        } else {
            alert('Error deleting product: ' + result);
        }
    })
    .catch(error => {
        alert('Fetch error: ' + error);
    });
}
</script>

</body>
</html>