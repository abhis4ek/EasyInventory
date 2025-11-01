<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo '<script>parent.location.href="login.php";</script>';
    exit();
}
require 'db.php';

$admin_id = $_SESSION['admin_id'];
$fullname = $_SESSION['fullname'] ?? 'Admin User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
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
            padding: 20px;
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
            transition: transform 0.2s;
        }
        
        .action-btn:hover {
            transform: scale(1.2);
        }
        
        .edit-btn { color: #3498db; }
        .delete-btn { color: #e74c3c; }
        
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
            max-height: 90vh;
            overflow-y: auto;
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
        
        .close-btn:hover {
            color: #e74c3c;
        }
        
        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
            margin-right: 10px;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .text-muted {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .pricing-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .pricing-section h4 {
            font-size: 1rem;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .price-display {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            border-left: 4px solid #27ae60;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .price-row.total {
            padding-top: 8px;
            border-top: 2px solid #eee;
        }

        .badge-mrp {
            background: #ff9800;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-left: 8px;
        }

        .search-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-filter input,
        .search-filter select {
            padding: 10px 15px;
            border: 1px solid #e0e6ed;
            border-radius: 5px;
            font-size: 0.95rem;
        }

        .search-filter input {
            flex: 1;
            min-width: 250px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 class="page-title">Inventory Management</h2>
        <div class="user-info">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullname) ?>&background=3498db&color=fff" alt="User">
            <span><?= htmlspecialchars($fullname) ?></span>
        </div>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h3 class="section-title">All Products</h3>
            <button class="btn btn-primary" id="addProductBtn"><i class="fas fa-plus"></i> Add Product</button>
        </div>

        <div class="search-filter">
            <input type="text" id="searchInput" placeholder="🔍 Search products..." onkeyup="filterTable()">
            <select id="categoryFilter" onchange="filterTable()">
                <option value="">All Categories</option>
                <?php
                $cat_stmt = $conn->prepare("SELECT id, name FROM categories WHERE admin_id = ? ORDER BY name");
                $cat_stmt->bind_param('i', $admin_id);
                $cat_stmt->execute();
                $cat_result = $cat_stmt->get_result();
                while($cat = $cat_result->fetch_assoc()) {
                    echo '<option value="'.$cat['id'].'">' . htmlspecialchars($cat['name']) . '</option>';
                }
                $cat_stmt->close();
                ?>
            </select>
            <select id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="instock">In Stock</option>
                <option value="lowstock">Low Stock</option>
                <option value="outstock">Out of Stock</option>
            </select>
        </div>

        <div class="table-responsive">
            <table id="productTable">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Profit/Unit</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $conn->prepare("
                    SELECT p.id, p.name, p.cost_price, p.has_mrp, p.mrp, p.profit_margin, 
                           p.selling_price, p.stock, p.description, p.category_id, c.name AS category_name
                    FROM products p
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.admin_id = ?
                    ORDER BY 
                        CASE 
                            WHEN p.stock > 20 THEN 1
                            WHEN p.stock > 0 THEN 2
                            ELSE 3
                        END, p.name
                ");
                $stmt->bind_param('i', $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($product = $result->fetch_assoc()) { 
                        $profit = $product['selling_price'] - $product['cost_price'];
                        $status_class = $product['stock'] > 20 ? 'instock' : ($product['stock'] > 0 ? 'lowstock' : 'outstock');
                        $status_text = $product['stock'] > 20 ? 'In Stock' : ($product['stock'] > 0 ? 'Low Stock' : 'Out of Stock');
                        ?>
                    <tr data-category="<?php echo $product['category_id']; ?>" data-status="<?php echo $status_class; ?>">
                        <td>
                            <?php echo htmlspecialchars($product['name']); ?>
                            <?php if($product['has_mrp']): ?>
                                <span class="badge-mrp">MRP</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td>₹<?php echo number_format($product['cost_price'], 2); ?></td>
                        <td>
                            ₹<?php echo number_format($product['selling_price'], 2); ?>
                            <?php if($product['has_mrp'] && $product['mrp'] > 0): ?>
                                <br><small class="text-muted">MRP: ₹<?php echo number_format($product['mrp'], 2); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="color: <?php echo $profit > 0 ? '#388e3c' : '#d32f2f'; ?>">
                            ₹<?php echo number_format($profit, 2); ?>
                        </td>
                        <td><?php echo $product['stock']; ?></td>
                        <td>
                            <span class="status status-<?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
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
                    <?php }
                } else {
                    echo "<tr><td colspan='8' style='text-align:center;'>No products found</td></tr>";
                }
                $stmt->close();
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content" style="max-width: 650px;">
            <div class="modal-header">
                <h3 class="modal-title">Add New Product</h3>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="productName">Product Name *</label>
                    <input type="text" id="productName" class="form-control" placeholder="Enter product name">
                </div>
                
                <div class="form-group">
                    <label for="categorySelect">Category *</label>
                    <div style="display: flex; gap: 10px;">
                        <select id="categorySelect" class="form-control" style="flex: 1;">
                            <option value="">Select category</option>
                        </select>
                        <button type="button" class="btn btn-secondary" onclick="openAddCategoryModal()" style="white-space: nowrap;">
                            + New
                        </button>
                    </div>
                </div>
                
                <div class="pricing-section">
                    <h4>💰 Pricing Setup</h4>
                    
                    <div class="form-group">
                        <label for="productCostPrice">Cost Price (What You Paid) *</label>
                        <input type="number" id="productCostPrice" class="form-control" placeholder="₹100" step="0.01" min="0">
                        <small class="text-muted">Amount paid to supplier (GST inclusive)</small>
                    </div>
                    
                    <div class="form-group" style="background: white; padding: 12px; border-radius: 6px;">
                        <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                            <input type="checkbox" id="hasMrpCheckbox" style="width: 18px; height: 18px; margin-right: 10px;">
                            <span style="font-weight: 600;">This product has MRP printed on package</span>
                        </label>
                        <small class="text-muted" style="margin-left: 28px; display: block; margin-top: 5px;">
                            Check this for packaged goods (chips, biscuits, bottles, etc.)
                        </small>
                    </div>
                    
                    <div class="form-group" id="mrpInputGroup" style="display: none;">
                        <label for="productMrp">Maximum Retail Price (MRP) *</label>
                        <input type="number" id="productMrp" class="form-control" placeholder="₹120" step="0.01" min="0">
                        <small class="text-muted">Price printed on the package</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="productSellingPrice">Your Selling Price *</label>
                        <input type="number" id="productSellingPrice" class="form-control" placeholder="₹110" step="0.01" min="0">
                        <small class="text-muted" id="sellingPriceHint">What you charge customers</small>
                    </div>
                    
                    <div style="text-align: center; margin: 10px 0; color: #999; font-weight: 600;">OR</div>
                    
                    <div class="form-group">
                        <label for="productProfitMargin">Profit Margin (Optional)</label>
                        <input type="number" id="productProfitMargin" class="form-control" placeholder="₹10" step="0.01" min="0">
                        <small class="text-muted">Your profit per unit (auto-calculates selling price)</small>
                    </div>
                    
                    <div class="price-display">
                        <div class="price-row">
                            <span style="color: #666;">Cost Price:</span>
                            <strong id="displayCostPrice" style="color: #2c3e50;">₹0.00</strong>
                        </div>
                        <div id="displayMrpRow" class="price-row" style="display: none;">
                            <span style="color: #666;">MRP (Max):</span>
                            <strong id="displayMrp" style="color: #e67e22;">₹0.00</strong>
                        </div>
                        <div class="price-row">
                            <span style="color: #666;">Profit:</span>
                            <strong id="displayProfit" style="color: #3498db;">₹0.00</strong>
                        </div>
                        <div class="price-row total">
                            <span style="font-weight: 600; color: #2c3e50;">Selling Price:</span>
                            <strong id="displaySellingPrice" style="color: #27ae60; font-size: 1.3rem;">₹0.00</strong>
                        </div>
                        <div id="mrpWarning" style="display: none; color: #e74c3c; font-size: 0.85rem; margin-top: 8px; font-weight: 600;">
                            ⚠️ Warning: Selling price exceeds MRP!
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="productStock">Initial Stock *</label>
                    <input type="number" id="productStock" class="form-control" placeholder="Enter stock quantity" min="0">
                </div>
                
                <div class="form-group">
                    <label for="productDescription">Description</label>
                    <textarea id="productDescription" class="form-control" rows="3" placeholder="Enter product description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-primary" id="saveProductBtn">Save Product</button>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content" style="max-width: 650px;">
            <div class="modal-header">
                <h3 class="modal-title">Edit Product</h3>
                <button class="close-btn" id="closeEditModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editProductId">
                <div class="form-group">
                    <label for="editProductName">Product Name *</label>
                    <input type="text" id="editProductName" class="form-control">
                </div>
                <div class="form-group">
                    <label for="editCategorySelect">Category *</label>
                    <select id="editCategorySelect" class="form-control">
                        <option value="">Select category</option>
                    </select>
                </div>
                
                <div class="pricing-section">
                    <h4>💰 Pricing</h4>
                    
                    <div class="form-group">
                        <label for="editProductCostPrice">Cost Price *</label>
                        <input type="number" id="editProductCostPrice" class="form-control" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group" style="background: white; padding: 12px; border-radius: 6px;">
                        <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                            <input type="checkbox" id="editHasMrpCheckbox" style="width: 18px; height: 18px; margin-right: 10px;">
                            <span style="font-weight: 600;">Has MRP printed</span>
                        </label>
                    </div>
                    
                    <div class="form-group" id="editMrpInputGroup" style="display: none;">
                        <label for="editProductMrp">MRP *</label>
                        <input type="number" id="editProductMrp" class="form-control" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="editProductSellingPrice">Selling Price *</label>
                        <input type="number" id="editProductSellingPrice" class="form-control" step="0.01" min="0">
                    </div>
                    
                    <div class="price-display">
                        <div class="price-row">
                            <span style="color: #666;">Cost Price:</span>
                            <strong id="editDisplayCostPrice" style="color: #2c3e50;">₹0.00</strong>
                        </div>
                        <div id="editDisplayMrpRow" class="price-row" style="display: none;">
                            <span style="color: #666;">MRP (Max):</span>
                            <strong id="editDisplayMrp" style="color: #e67e22;">₹0.00</strong>
                        </div>
                        <div class="price-row">
                            <span style="color: #666;">Profit:</span>
                            <strong id="editDisplayProfit" style="color: #3498db;">₹0.00</strong>
                        </div>
                        <div class="price-row total">
                            <span style="font-weight: 600; color: #2c3e50;">Selling Price:</span>
                            <strong id="editDisplaySellingPrice" style="color: #27ae60; font-size: 1.3rem;">₹0.00</strong>
                        </div>
                        <div id="editMrpWarning" style="display: none; color: #e74c3c; font-size: 0.85rem; margin-top: 8px; font-weight: 600;">
                            ⚠️ Warning: Selling price exceeds MRP!
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editProductStock">Stock *</label>
                    <input type="number" id="editProductStock" class="form-control" min="0">
                </div>
                <div class="form-group">
                    <label for="editProductDescription">Description</label>
                    <textarea id="editProductDescription" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
                <button class="btn btn-primary" id="saveEditProductBtn">Update Product</button>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Add New Category</h3>
                <button class="close-btn" onclick="closeAddCategoryModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="categoryName">Category Name *</label>
                    <input type="text" id="categoryName" class="form-control" placeholder="e.g., Electronics">
                </div>
                <div class="form-group">
                    <label for="categoryDescription">Description</label>
                    <input type="text" id="categoryDescription" class="form-control" placeholder="Brief description (optional)">
                </div>
                <div class="form-group">
                    <label for="categoryGstRate">GST Rate (%) *</label>
                    <input type="number" id="categoryGstRate" class="form-control" placeholder="e.g., 18" step="0.01" min="0" max="100" value="0">
                    <small class="text-muted">Enter rate between 0-100%</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAddCategoryModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveCategoryFromModal()">Save Category</button>
            </div>
        </div>
    </div>

    <script>
// Filter functionality
function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const categoryValue = document.getElementById('categoryFilter').value;
    const statusValue = document.getElementById('statusFilter').value;
    const table = document.getElementById('productTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const name = row.cells[0]?.textContent.toLowerCase() || '';
        const category = row.getAttribute('data-category') || '';
        const status = row.getAttribute('data-status') || '';

        const matchesSearch = name.includes(searchValue);
        const matchesCategory = !categoryValue || category === categoryValue;
        const matchesStatus = !statusValue || status === statusValue;

        if (matchesSearch && matchesCategory && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

const modal = document.getElementById('addProductModal');
const addProductBtn = document.getElementById('addProductBtn');
const closeBtn = document.getElementById('closeModalBtn');
const cancelBtn = document.getElementById('cancelBtn');
const saveProductBtn = document.getElementById('saveProductBtn');

const hasMrpCheckbox = document.getElementById('hasMrpCheckbox');
const mrpInputGroup = document.getElementById('mrpInputGroup');
const displayMrpRow = document.getElementById('displayMrpRow');
const sellingPriceHint = document.getElementById('sellingPriceHint');

hasMrpCheckbox.addEventListener('change', function() {
    if (this.checked) {
        mrpInputGroup.style.display = 'block';
        displayMrpRow.style.display = 'flex';
        sellingPriceHint.textContent = 'Must be ≤ MRP';
    } else {
        mrpInputGroup.style.display = 'none';
        displayMrpRow.style.display = 'none';
        sellingPriceHint.textContent = 'What you charge customers';
        document.getElementById('productMrp').value = '';
    }
    calculatePricing();
});

function calculatePricing() {
    const costPrice = parseFloat(document.getElementById('productCostPrice').value) || 0;
    const hasMrp = hasMrpCheckbox.checked;
    const mrp = hasMrp ? (parseFloat(document.getElementById('productMrp').value) || 0) : 0;
    const sellingPrice = parseFloat(document.getElementById('productSellingPrice').value) || 0;
    const profitMargin = parseFloat(document.getElementById('productProfitMargin').value) || 0;
    
    document.getElementById('displayCostPrice').textContent = '₹' + costPrice.toFixed(2);
    
    if (hasMrp) {
        document.getElementById('displayMrp').textContent = '₹' + mrp.toFixed(2);
    }
    
    let finalSellingPrice = sellingPrice;
    let finalProfit = 0;
    
    if (sellingPrice > 0) {
        finalProfit = sellingPrice - costPrice;
        document.getElementById('productProfitMargin').value = finalProfit.toFixed(2);
    } else if (profitMargin > 0) {
        finalSellingPrice = costPrice + profitMargin;
        document.getElementById('productSellingPrice').value = finalSellingPrice.toFixed(2);
        finalProfit = profitMargin;
    }
    
    document.getElementById('displayProfit').textContent = '₹' + finalProfit.toFixed(2);
    document.getElementById('displaySellingPrice').textContent = '₹' + finalSellingPrice.toFixed(2);
    
    const mrpWarning = document.getElementById('mrpWarning');
    if (hasMrp && mrp > 0 && finalSellingPrice > mrp) {
        mrpWarning.style.display = 'block';
    } else {
        mrpWarning.style.display = 'none';
    }
}

document.getElementById('productCostPrice').addEventListener('input', calculatePricing);
document.getElementById('productMrp').addEventListener('input', calculatePricing);
document.getElementById('productSellingPrice').addEventListener('input', calculatePricing);
document.getElementById('productProfitMargin').addEventListener('input', calculatePricing);

function loadCategories() {
    fetch('get_categories.php')
    .then(response => response.json())
    .then(categories => {
        const categorySelect = document.getElementById('categorySelect');
        categorySelect.innerHTML = '<option value="">Select category</option>';
        categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            categorySelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error("Error loading categories:", error);
        document.getElementById('categorySelect').innerHTML = '<option value="">Error loading categories</option>';
    });
}

addProductBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
    loadCategories();
});

closeBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === modal) modal.style.display = 'none';
});

saveProductBtn.addEventListener('click', () => {
    const name = document.getElementById('productName').value.trim();
    const category_id = document.getElementById('categorySelect').value;
    const cost_price = document.getElementById('productCostPrice').value.trim();
    const has_mrp = hasMrpCheckbox.checked ? 1 : 0;
    const mrp = has_mrp ? document.getElementById('productMrp').value.trim() : '';
    const profit_margin = document.getElementById('productProfitMargin').value.trim() || 0;
    const selling_price = document.getElementById('productSellingPrice').value.trim();
    const stock = document.getElementById('productStock').value.trim();
    const description = document.getElementById('productDescription').value.trim();

    if (!name || !category_id || !cost_price || !selling_price || !stock) {
        alert('Please fill in all required fields (Name, Category, Cost Price, Selling Price, Stock).');
        return;
    }

    if (has_mrp && !mrp) {
        alert('Please enter MRP for packaged product.');
        return;
    }

    if (has_mrp && parseFloat(selling_price) > parseFloat(mrp)) {
        if (!confirm('Warning: Selling price exceeds MRP! This may be illegal in India.\n\nDo you want to proceed anyway?')) {
            return;
        }
    }

    if (parseFloat(selling_price) <= parseFloat(cost_price)) {
        alert('Selling price must be greater than cost price!');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('category_id', category_id);
    formData.append('cost_price', cost_price);
    formData.append('has_mrp', has_mrp);
    if (has_mrp) formData.append('mrp', mrp);
    formData.append('profit_margin', profit_margin);
    formData.append('selling_price', selling_price);
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
            document.getElementById('productName').value = '';
            document.getElementById('productCostPrice').value = '';
            document.getElementById('productMrp').value = '';
            document.getElementById('productProfitMargin').value = '';
            document.getElementById('productSellingPrice').value = '';
            document.getElementById('productStock').value = '';
            document.getElementById('productDescription').value = '';
            hasMrpCheckbox.checked = false;
            mrpInputGroup.style.display = 'none';
            calculatePricing();
            location.reload();
        } else {
            alert('Error adding product: ' + result);
        }
    })
    .catch(error => {
        alert('Fetch error: ' + error);
    });
});

// Edit Modal Logic
const editModal = document.getElementById('editProductModal');
const closeEditBtn = document.getElementById('closeEditModalBtn');
const cancelEditBtn = document.getElementById('cancelEditBtn');
const saveEditProductBtn = document.getElementById('saveEditProductBtn');
const editHasMrpCheckbox = document.getElementById('editHasMrpCheckbox');
const editMrpInputGroup = document.getElementById('editMrpInputGroup');
const editDisplayMrpRow = document.getElementById('editDisplayMrpRow');

editHasMrpCheckbox.addEventListener('change', function() {
    if (this.checked) {
        editMrpInputGroup.style.display = 'block';
        editDisplayMrpRow.style.display = 'flex';
    } else {
        editMrpInputGroup.style.display = 'none';
        editDisplayMrpRow.style.display = 'none';
        document.getElementById('editProductMrp').value = '';
    }
    calculateEditPricing();
});

function calculateEditPricing() {
    const costPrice = parseFloat(document.getElementById('editProductCostPrice').value) || 0;
    const hasMrp = editHasMrpCheckbox.checked;
    const mrp = hasMrp ? (parseFloat(document.getElementById('editProductMrp').value) || 0) : 0;
    const sellingPrice = parseFloat(document.getElementById('editProductSellingPrice').value) || 0;
    
    document.getElementById('editDisplayCostPrice').textContent = '₹' + costPrice.toFixed(2);
    
    if (hasMrp) {
        document.getElementById('editDisplayMrp').textContent = '₹' + mrp.toFixed(2);
    }
    
    const profit = sellingPrice - costPrice;
    
    document.getElementById('editDisplayProfit').textContent = '₹' + profit.toFixed(2);
    document.getElementById('editDisplaySellingPrice').textContent = '₹' + sellingPrice.toFixed(2);
    
    const mrpWarning = document.getElementById('editMrpWarning');
    if (hasMrp && mrp > 0 && sellingPrice > mrp) {
        mrpWarning.style.display = 'block';
    } else {
        mrpWarning.style.display = 'none';
    }
}

document.getElementById('editProductCostPrice').addEventListener('input', calculateEditPricing);
document.getElementById('editProductMrp').addEventListener('input', calculateEditPricing);
document.getElementById('editProductSellingPrice').addEventListener('input', calculateEditPricing);

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
    });
}

function openEditModal(product) {
    editModal.style.display = 'flex';
    document.getElementById('editProductId').value = product.id;
    document.getElementById('editProductName').value = product.name;
    document.getElementById('editProductCostPrice').value = product.cost_price;
    document.getElementById('editProductSellingPrice').value = product.selling_price;
    document.getElementById('editProductStock').value = product.stock;
    document.getElementById('editProductDescription').value = product.description;
    
    editHasMrpCheckbox.checked = product.has_mrp == 1;
    if (product.has_mrp == 1) {
        editMrpInputGroup.style.display = 'block';
        editDisplayMrpRow.style.display = 'flex';
        document.getElementById('editProductMrp').value = product.mrp;
    } else {
        editMrpInputGroup.style.display = 'none';
        editDisplayMrpRow.style.display = 'none';
    }
    
    loadEditCategories(product.category_id);
    calculateEditPricing();
}

closeEditBtn.addEventListener('click', () => { 
    editModal.style.display = 'none'; 
});

cancelEditBtn.addEventListener('click', () => { 
    editModal.style.display = 'none'; 
});

window.addEventListener('click', (e) => { 
    if (e.target === editModal) editModal.style.display = 'none'; 
});

saveEditProductBtn.addEventListener('click', () => {
    const id = document.getElementById('editProductId').value;
    const name = document.getElementById('editProductName').value.trim();
    const category_id = document.getElementById('editCategorySelect').value;
    const cost_price = document.getElementById('editProductCostPrice').value.trim();
    const has_mrp = editHasMrpCheckbox.checked ? 1 : 0;
    const mrp = has_mrp ? document.getElementById('editProductMrp').value.trim() : '';
    const selling_price = document.getElementById('editProductSellingPrice').value.trim();
    const stock = document.getElementById('editProductStock').value.trim();
    const description = document.getElementById('editProductDescription').value.trim();

    if (!name || !category_id || !cost_price || !selling_price || !stock) {
        alert('Please fill in all required fields.');
        return;
    }

    if (has_mrp && !mrp) {
        alert('Please enter MRP for packaged product.');
        return;
    }

    if (has_mrp && parseFloat(selling_price) > parseFloat(mrp)) {
        if (!confirm('Warning: Selling price exceeds MRP!\n\nDo you want to proceed anyway?')) {
            return;
        }
    }

    const profit_margin = parseFloat(selling_price) - parseFloat(cost_price);

    const formData = new FormData();
    formData.append('id', id);
    formData.append('name', name);
    formData.append('category_id', category_id);
    formData.append('cost_price', cost_price);
    formData.append('has_mrp', has_mrp);
    if (has_mrp) formData.append('mrp', mrp);
    formData.append('profit_margin', profit_margin);
    formData.append('selling_price', selling_price);
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
    .catch(error => { 
        alert('Fetch error: ' + error); 
    });
});

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

function openAddCategoryModal() {
    document.getElementById('addCategoryModal').style.display = 'flex';
}

function closeAddCategoryModal() {
    document.getElementById('addCategoryModal').style.display = 'none';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('categoryGstRate').value = '0';
}

function saveCategoryFromModal() {
    const name = document.getElementById('categoryName').value.trim();
    const description = document.getElementById('categoryDescription').value.trim();
    const gst_rate = document.getElementById('categoryGstRate').value.trim();

    if (!name) {
        alert('Please enter category name.');
        return;
    }

    if (!gst_rate || parseFloat(gst_rate) < 0 || parseFloat(gst_rate) > 100) {
        alert('Please enter valid GST rate (0-100).');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('description', description);
    formData.append('gst_rate', gst_rate);

    fetch('add_category.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Category added successfully!');
            closeAddCategoryModal();
            loadCategories();
            setTimeout(() => {
                document.getElementById('categorySelect').value = result.id;
            }, 200);
        } else {
            alert('Error: ' + (result.msg || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Fetch error: ' + error);
    });
}

window.addEventListener('click', (e) => {
    const categoryModal = document.getElementById('addCategoryModal');
    if (e.target === categoryModal) {
        closeAddCategoryModal();
    }
});
</script>
</body>
</html>