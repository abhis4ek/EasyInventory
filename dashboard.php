<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo '<script>parent.location.href="login.php";</script>';
    exit();
}
require 'db.php';

// Indian currency formatting function (PHP)
function formatIndianCurrency($number) {
    $number = number_format($number, 2, '.', '');
    $parts = explode('.', $number);
    $integerPart = $parts[0];
    $decimalPart = $parts[1];
    
    $lastThree = substr($integerPart, -3);
    $otherNumbers = substr($integerPart, 0, -3);
    
    if ($otherNumbers != '') {
        $lastThree = ',' . $lastThree;
    }
    
    $result = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $otherNumbers) . $lastThree;
    
    return '₹' . $result . '.' . $decimalPart;
}

$admin_id = $_SESSION['admin_id'];

// --- DATA FETCHING (Unchanged from your original code) ---
$sql_total = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE admin_id = ?";
$stmt = $conn->prepare($sql_total);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$total_products = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$sql_instock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE admin_id = ? AND stock > 20";
$stmt = $conn->prepare($sql_instock);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$in_stock = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$sql_lowstock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE admin_id = ? AND stock > 0 AND stock <= 20";
$stmt = $conn->prepare($sql_lowstock);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$low_stock = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$sql_outstock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE admin_id = ? AND stock = 0";
$stmt = $conn->prepare($sql_outstock);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$out_of_stock = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- ORIGINAL DASHBOARD STYLES (Restored) --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f7fa; color: #333; line-height: 1.6; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #e0e6ed; }
        .page-title { font-size: 1.8rem; color: #2c3e50; }
        .content-section { background: white; border-radius: 10px; padding: 25px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .section-title { font-size: 1.3rem; color: #2c3e50; }
        
        /* Original Buttons & Tables */
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .btn-primary { background-color: #3498db; color: white; }
        .btn-primary:hover { background-color: #2980b9; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 15px; text-align: left; border-bottom: 1px solid #e0e6ed; }
        table th { background-color: #f8f9fa; font-weight: 600; color: #2c3e50; }
        table tr:hover { background-color: #f8f9fa; }
        
        /* Original Status Badges */
        .status { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-instock { background-color: #e8f5e9; color: #388e3c; }
        .status-lowstock { background-color: #fff8e1; color: #f57c00; }
        .status-outstock { background-color: #ffebee; color: #d32f2f; }
        .action-btn { border: none; background: none; cursor: pointer; margin-right: 10px; font-size: 1.1rem; transition: transform 0.2s; }
        .action-btn:hover { transform: scale(1.2); }
        .stats-btn { color: #9b59b6; }
        .edit-btn { color: #3498db; }
        .delete-btn { color: #e74c3c; }
        .badge-mrp { background: #ff9800; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-left: 8px; float: right; }

        /* Original Dashboard Cards */
        .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); display: flex; align-items: center; transition: transform 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); }
        .card-icon { width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-right: 15px; }
        .card-info h3 { font-size: 1.8rem; margin-bottom: 5px; }
        .card-info p { color: #7f8c8d; font-size: 0.9rem; }
        .bg-primary { background-color: #e3f2fd; color: #1976d2; }
        .bg-success { background-color: #e8f5e9; color: #388e3c; }
        .bg-warning { background-color: #fff8e1; color: #f57c00; }
        .bg-danger { background-color: #ffebee; color: #d32f2f; }

        /* Search Filter */
        .search-filter { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .search-filter input, .search-filter select { padding: 10px 15px; border: 1px solid #e0e6ed; border-radius: 5px; font-size: 0.95rem; }
        .search-filter input { flex: 1; min-width: 250px; }

        /* --- NEW MODAL STYLES (The "New" Design you requested) --- */
        :root {
            --modal-primary: #4f46e5;
            --modal-border: #e2e8f0;
            --modal-bg: #ffffff;
            --modal-text: #1e293b;
        }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(2px); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: var(--modal-bg); border-radius: 16px; width: 650px; max-width: 90%; padding: 0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; font-family: 'Inter', sans-serif; max-height: 90vh; display: flex; flex-direction: column; }
        
        .modal-header { padding: 20px 25px; border-bottom: 1px solid var(--modal-border); display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .modal-title { font-size: 1.25rem; font-weight: 700; color: var(--modal-text); }
        .close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; }
        .close-btn:hover { color: #ef4444; }
        
        .modal-body { padding: 25px; overflow-y: auto; }
        .modal-footer { padding: 20px 25px; border-top: 1px solid var(--modal-border); background: #f8fafc; text-align: right; }

        /* New Form Grid */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-full { grid-column: 1 / -1; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--modal-text); }
        .new-form-control { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; transition: border 0.2s; font-family: 'Inter', sans-serif; }
        .new-form-control:focus { outline: none; border-color: var(--modal-primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

        /* Pricing Card */
        .pricing-card { background: #f8fafc; border: 1px solid var(--modal-border); border-radius: 12px; padding: 20px; grid-column: 1 / -1; margin-top: 5px; }
        .pricing-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .pricing-header h4 { font-size: 0.95rem; font-weight: 700; color: var(--modal-text); margin: 0; }
        .pricing-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        .calculation-display { grid-column: 1 / -1; background: white; border-radius: 8px; padding: 15px; margin-top: 15px; display: flex; justify-content: space-between; align-items: center; border: 1px dashed #cbd5e1; }
        .calc-item { text-align: center; flex: 1; }
        .calc-label { font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 4px; }
        .calc-value { font-weight: 700; font-size: 1.1rem; color: var(--modal-text); }
        .calc-value.profit { color: #10b981; }
        .calc-value.final { color: var(--modal-primary); font-size: 1.3rem; }

        /* New Button Styles inside Modal */
        .btn-secondary { background-color: white; color: #64748b; border: 1px solid #cbd5e1; }
        .btn-secondary:hover { background-color: #f1f5f9; }
        
        /* --- SIMPLE STATS DESIGN --- */
        .simple-stats-container { font-family: 'Inter', sans-serif; }
        .simple-stats-header { text-align: center; margin-bottom: 25px; }
        .simple-summary-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .simple-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: center; }
        .simple-card-label { color: #64748b; font-size: 0.85rem; text-transform: uppercase; font-weight: 600; margin-bottom: 5px; }
        .simple-card-value { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
        
        .simple-section-title { font-size: 1rem; font-weight: 600; color: #1e293b; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; }
        .simple-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .simple-table th { text-align: left; padding: 10px; background: #f8fafc; color: #64748b; font-weight: 600; }
        .simple-table td { padding: 10px; border-bottom: 1px solid #f1f5f9; color: #333; }
        .text-green { color: #10b981; font-weight: 600; }
        .text-red { color: #ef4444; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h2 class="page-title">Dashboard</h2>
    </div>

    <div class="dashboard-cards">
        <div class="card">
            <div class="card-icon bg-primary"><i class="fas fa-box"></i></div>
            <div class="card-info">
                <h3><?php echo $total_products; ?></h3>
                <p>Total Products</p>
            </div>
        </div>
        <div class="card">
            <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
            <div class="card-info">
                <h3><?php echo $in_stock; ?></h3>
                <p>In Stock</p>
            </div>
        </div>
        <div class="card">
            <div class="card-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="card-info">
                <h3><?php echo $low_stock; ?></h3>
                <p>Low Stock</p>
            </div>
        </div>
        <div class="card">
            <div class="card-icon bg-danger"><i class="fas fa-times-circle"></i></div>
            <div class="card-info">
                <h3><?php echo $out_of_stock; ?></h3>
                <p>Out of Stock</p>
            </div>
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
                    ORDER BY CASE WHEN p.stock > 20 THEN 1 WHEN p.stock > 0 THEN 2 ELSE 3 END, p.name
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
                        <td><?php echo formatIndianCurrency($product['cost_price']); ?></td>
                        <td>
                            <?php echo formatIndianCurrency($product['selling_price']); ?>
                            <?php if($product['has_mrp'] && $product['mrp'] > 0): ?>
                                <br><small style="color:#6c757d; font-size:0.8rem;">MRP: <?php echo formatIndianCurrency($product['mrp']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="color: <?php echo $profit > 0 ? '#388e3c' : '#d32f2f'; ?>">
                            <?php echo formatIndianCurrency($profit); ?>
                        </td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><span class="status status-<?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <button class="action-btn stats-btn" onclick="openStatsModal(<?php echo $product['id']; ?>)" title="View Stats"><i class="fas fa-chart-bar"></i></button>
                            <button class="action-btn edit-btn" onclick='openEditModal(<?php echo json_encode($product); ?>)'><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)"><i class="fas fa-trash"></i></button>
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

    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Product</h3>
                <button class="close-btn" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label for="productName">Product Name *</label>
                        <input type="text" id="productName" class="new-form-control" placeholder="e.g. Britannia Cookies">
                    </div>
                    
                    <div class="form-group">
                        <label for="categorySelect">Category *</label>
                        <div style="display: flex; gap: 8px;">
                            <select id="categorySelect" class="new-form-control" style="flex: 1;">
                                <option value="">Select category</option>
                            </select>
                            <button type="button" class="btn btn-secondary" onclick="openAddCategoryModal()">+</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="productStock">Initial Stock *</label>
                        <input type="number" id="productStock" class="new-form-control" placeholder="0" min="0">
                    </div>
                    
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h4>Pricing & Profit</h4>
                            <label style="display:flex; align-items:center; font-size:0.9rem; cursor:pointer;">
                                <input type="checkbox" id="hasMrpCheckbox" style="margin-right:8px;"> Has MRP
                            </label>
                        </div>
                        
                        <div class="pricing-grid">
                            <div class="form-group" style="margin:0;">
                                <label style="font-size:0.8rem;">Cost Price *</label>
                                <input type="number" id="productCostPrice" class="new-form-control" placeholder="₹0.00" step="0.01" min="0">
                            </div>
                            
                            <div class="form-group" id="mrpInputGroup" style="margin:0; display:none;">
                                <label style="font-size:0.8rem;">MRP (Max)</label>
                                <input type="number" id="productMrp" class="new-form-control" placeholder="₹0.00" step="0.01" min="0">
                            </div>

                            <div class="form-group" style="margin:0;">
                                <label style="font-size:0.8rem;">Selling Price *</label>
                                <input type="number" id="productSellingPrice" class="new-form-control" placeholder="₹0.00" step="0.01" min="0">
                            </div>
                        </div>

                        <div class="calculation-display">
                            <div class="calc-item">
                                <div class="calc-label">Cost</div>
                                <div class="calc-value" id="displayCostPrice">₹0.00</div>
                            </div>
                            <div class="calc-item">
                                <div class="calc-label">Profit</div>
                                <div class="calc-value profit" id="displayProfit">₹0.00</div>
                            </div>
                            <div class="calc-item">
                                <div class="calc-label">Selling</div>
                                <div class="calc-value final" id="displaySellingPrice">₹0.00</div>
                            </div>
                        </div>
                        <div id="mrpWarning" style="display: none; color: #ef4444; font-size: 0.8rem; margin-top: 8px; text-align: center; font-weight: 600;">
                            ⚠️ Selling price exceeds MRP!
                        </div>
                    </div>
                    
                    <div class="form-group form-full">
                        <label for="productDescription">Description (Optional)</label>
                        <textarea id="productDescription" class="new-form-control" rows="2" placeholder="Product details..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelBtn" style="margin-right:10px;">Cancel</button>
                <button class="btn btn-primary" id="saveProductBtn">Save Product</button>
            </div>
        </div>
    </div>

    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Product</h3>
                <button class="close-btn" id="closeEditModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editProductId">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Product Name *</label>
                        <input type="text" id="editProductName" class="new-form-control">
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select id="editCategorySelect" class="new-form-control"></select>
                    </div>
                    <div class="form-group">
                        <label>Stock *</label>
                        <input type="number" id="editProductStock" class="new-form-control" min="0">
                    </div>
                    
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h4>Update Pricing</h4>
                            <label style="display:flex; align-items:center; font-size:0.9rem; cursor:pointer;">
                                <input type="checkbox" id="editHasMrpCheckbox" style="margin-right:8px;"> Has MRP
                            </label>
                        </div>
                        
                        <div class="pricing-grid">
                            <div class="form-group" style="margin:0;">
                                <label style="font-size:0.8rem;">Cost Price</label>
                                <input type="number" id="editProductCostPrice" class="new-form-control" step="0.01">
                            </div>
                            <div class="form-group" id="editMrpInputGroup" style="margin:0; display:none;">
                                <label style="font-size:0.8rem;">MRP</label>
                                <input type="number" id="editProductMrp" class="new-form-control" step="0.01">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label style="font-size:0.8rem;">Selling Price</label>
                                <input type="number" id="editProductSellingPrice" class="new-form-control" step="0.01">
                            </div>
                        </div>

                        <div class="calculation-display">
                            <div class="calc-item">
                                <div class="calc-label">Cost</div>
                                <div class="calc-value" id="editDisplayCostPrice">₹0.00</div>
                            </div>
                            <div class="calc-item">
                                <div class="calc-label">Profit</div>
                                <div class="calc-value profit" id="editDisplayProfit">₹0.00</div>
                            </div>
                            <div class="calc-item">
                                <div class="calc-label">Selling</div>
                                <div class="calc-value final" id="editDisplaySellingPrice">₹0.00</div>
                            </div>
                        </div>
                        <div id="editMrpWarning" style="display: none; color: #ef4444; font-size: 0.8rem; margin-top: 8px; text-align: center;">
                            ⚠️ Exceeds MRP
                        </div>
                    </div>
                    
                    <div class="form-group form-full">
                        <label>Description</label>
                        <textarea id="editProductDescription" class="new-form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelEditBtn" style="margin-right:10px;">Cancel</button>
                <button class="btn btn-primary" id="saveEditProductBtn">Save Changes</button>
            </div>
        </div>
    </div>

    <div id="statsModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Statistics</h3>
                <button class="close-btn" onclick="closeStatsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="statsLoading" style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                    <p style="margin-top: 10px;">Loading data...</p>
                </div>
                
                <div id="statsContent" class="simple-stats-container" style="display: none;">
                    <h3 id="statsProductName" class="simple-stats-header">Product Name</h3>
                    
                    <div class="simple-summary-grid">
                        <div class="simple-card">
                            <div class="simple-card-label">Current Stock</div>
                            <div class="simple-card-value" id="statsCurrentStock">0</div>
                        </div>
                        <div class="simple-card">
                            <div class="simple-card-label">Total Sold</div>
                            <div class="simple-card-value" id="statsTotalSold">0</div>
                        </div>
                        <div class="simple-card">
                            <div class="simple-card-label">Revenue</div>
                            <div class="simple-card-value" id="statsTotalRevenue" style="color: #10b981;">₹0</div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <h4 class="simple-section-title">Recent Sales (Monthly)</h4>
                        <div id="monthlySalesContent"></div>
                    </div>

                    <div>
                        <h4 class="simple-section-title">Stock History</h4>
                        <div id="stockHistoryContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="addCategoryModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">New Category</h3>
                <button class="close-btn" onclick="closeAddCategoryModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" id="categoryName" class="new-form-control" placeholder="e.g. Electronics">
                </div>
                <div class="form-group">
                    <label>GST Rate (%)</label>
                    <input type="number" id="categoryGstRate" class="new-form-control" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAddCategoryModal()" style="margin-right:10px;">Cancel</button>
                <button class="btn btn-primary" onclick="saveCategoryFromModal()">Create</button>
            </div>
        </div>
    </div>

    <script>
    // Utility: Format Currency
    function formatIndianCurrency(number) {
        const num = parseFloat(number);
        if (isNaN(num)) return '₹0.00';
        const parts = num.toFixed(2).split('.');
        let integerPart = parts[0];
        const decimalPart = parts[1];
        const isNegative = integerPart.startsWith('-');
        if (isNegative) integerPart = integerPart.substring(1);
        let lastThree = integerPart.substring(integerPart.length - 3);
        let otherNumbers = integerPart.substring(0, integerPart.length - 3);
        if (otherNumbers !== '') lastThree = ',' + lastThree;
        let result = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree;
        if (isNegative) result = '-' + result;
        return '₹' + result + '.' + decimalPart;
    }

    // 1. Search & Filter Logic (Same as original)
    function filterTable() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const categoryValue = document.getElementById('categoryFilter').value;
        const statusValue = document.getElementById('statusFilter').value;
        const rows = document.getElementById('productTable').getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const name = row.cells[0]?.textContent.toLowerCase() || '';
            const category = row.getAttribute('data-category') || '';
            const status = row.getAttribute('data-status') || '';

            const matchesSearch = name.includes(searchValue);
            const matchesCategory = !categoryValue || category === categoryValue;
            const matchesStatus = !statusValue || status === statusValue;

            row.style.display = (matchesSearch && matchesCategory && matchesStatus) ? '' : 'none';
        }
    }

    // 2. Add Product Logic (Updated for New Modal)
    const addModal = document.getElementById('addProductModal');
    const hasMrpCheckbox = document.getElementById('hasMrpCheckbox');
    
    document.getElementById('addProductBtn').addEventListener('click', () => {
        addModal.style.display = 'flex';
        loadCategories();
    });

    document.getElementById('closeModalBtn').addEventListener('click', () => addModal.style.display = 'none');
    document.getElementById('cancelBtn').addEventListener('click', () => addModal.style.display = 'none');
    
    hasMrpCheckbox.addEventListener('change', function() {
        const mrpGroup = document.getElementById('mrpInputGroup');
        const priceGrid = document.querySelector('.pricing-grid');
        if (this.checked) {
            mrpGroup.style.display = 'block';
            priceGrid.style.gridTemplateColumns = '1fr 1fr 1fr'; 
        } else {
            mrpGroup.style.display = 'none';
            priceGrid.style.gridTemplateColumns = '1fr 1fr';
            document.getElementById('productMrp').value = '';
        }
        calculatePricing();
    });

    function calculatePricing() {
        const cost = parseFloat(document.getElementById('productCostPrice').value) || 0;
        const selling = parseFloat(document.getElementById('productSellingPrice').value) || 0;
        const mrp = parseFloat(document.getElementById('productMrp').value) || 0;
        const hasMrp = hasMrpCheckbox.checked;
        const profit = selling - cost;

        document.getElementById('displayCostPrice').textContent = formatIndianCurrency(cost);
        document.getElementById('displaySellingPrice').textContent = formatIndianCurrency(selling);
        document.getElementById('displayProfit').textContent = formatIndianCurrency(profit);
        
        const profitEl = document.getElementById('displayProfit');
        profitEl.style.color = profit >= 0 ? '#10b981' : '#ef4444';

        const warning = document.getElementById('mrpWarning');
        if (hasMrp && mrp > 0 && selling > mrp) warning.style.display = 'block';
        else warning.style.display = 'none';
    }

    ['productCostPrice', 'productSellingPrice', 'productMrp'].forEach(id => {
        document.getElementById(id).addEventListener('input', calculatePricing);
    });

    function loadCategories() {
        fetch('get_categories.php')
        .then(res => res.json())
        .then(data => {
            const sel = document.getElementById('categorySelect');
            sel.innerHTML = '<option value="">Select category</option>';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                sel.appendChild(opt);
            });
        });
    }

    document.getElementById('saveProductBtn').addEventListener('click', () => {
        const data = new FormData();
        data.append('name', document.getElementById('productName').value.trim());
        data.append('category_id', document.getElementById('categorySelect').value);
        data.append('cost_price', document.getElementById('productCostPrice').value.trim());
        data.append('selling_price', document.getElementById('productSellingPrice').value.trim());
        data.append('stock', document.getElementById('productStock').value.trim());
        data.append('description', document.getElementById('productDescription').value.trim());
        data.append('has_mrp', hasMrpCheckbox.checked ? 1 : 0);
        if(hasMrpCheckbox.checked) data.append('mrp', document.getElementById('productMrp').value.trim());
        
        if(!data.get('name') || !data.get('category_id') || !data.get('cost_price') || !data.get('selling_price')) {
            alert('Please fill all required fields'); return;
        }

        fetch('add_product.php', { method: 'POST', body: data })
        .then(r => r.text())
        .then(res => {
            if(res.trim() === 'success') location.reload();
            else alert(res);
        });
    });

    // 3. Edit Product Logic (Updated for New Modal)
    const editModal = document.getElementById('editProductModal');
    const editHasMrpCheckbox = document.getElementById('editHasMrpCheckbox');

    function openEditModal(p) {
        editModal.style.display = 'flex';
        document.getElementById('editProductId').value = p.id;
        document.getElementById('editProductName').value = p.name;
        document.getElementById('editProductStock').value = p.stock;
        document.getElementById('editProductCostPrice').value = p.cost_price;
        document.getElementById('editProductSellingPrice').value = p.selling_price;
        document.getElementById('editProductDescription').value = p.description;
        
        editHasMrpCheckbox.checked = p.has_mrp == 1;
        const mrpGroup = document.getElementById('editMrpInputGroup');
        const priceGrid = editModal.querySelector('.pricing-grid');
        
        if(p.has_mrp == 1) {
            mrpGroup.style.display = 'block';
            document.getElementById('editProductMrp').value = p.mrp;
            priceGrid.style.gridTemplateColumns = '1fr 1fr 1fr';
        } else {
            mrpGroup.style.display = 'none';
            priceGrid.style.gridTemplateColumns = '1fr 1fr';
        }
        
        loadEditCategories(p.category_id);
        calculateEditPricing();
    }

    function loadEditCategories(selectedId) {
        fetch('get_categories.php').then(r => r.json()).then(data => {
            const sel = document.getElementById('editCategorySelect');
            sel.innerHTML = '<option value="">Select category</option>';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id; opt.textContent = c.name;
                if(c.id == selectedId) opt.selected = true;
                sel.appendChild(opt);
            });
        });
    }

    function calculateEditPricing() {
        const cost = parseFloat(document.getElementById('editProductCostPrice').value) || 0;
        const selling = parseFloat(document.getElementById('editProductSellingPrice').value) || 0;
        const mrp = parseFloat(document.getElementById('editProductMrp').value) || 0;
        const profit = selling - cost;
        
        document.getElementById('editDisplayCostPrice').textContent = formatIndianCurrency(cost);
        document.getElementById('editDisplaySellingPrice').textContent = formatIndianCurrency(selling);
        const profitEl = document.getElementById('editDisplayProfit');
        profitEl.textContent = formatIndianCurrency(profit);
        profitEl.style.color = profit >= 0 ? '#10b981' : '#ef4444';
        
        const warning = document.getElementById('editMrpWarning');
        if (editHasMrpCheckbox.checked && mrp > 0 && selling > mrp) warning.style.display = 'block';
        else warning.style.display = 'none';
    }

    document.getElementById('closeEditModalBtn').addEventListener('click', () => editModal.style.display = 'none');
    document.getElementById('cancelEditBtn').addEventListener('click', () => editModal.style.display = 'none');
    ['editProductCostPrice', 'editProductSellingPrice', 'editProductMrp'].forEach(id => {
        document.getElementById(id).addEventListener('input', calculateEditPricing);
    });
    editHasMrpCheckbox.addEventListener('change', function() {
        const mrpGroup = document.getElementById('editMrpInputGroup');
        const priceGrid = editModal.querySelector('.pricing-grid');
        if(this.checked) {
            mrpGroup.style.display = 'block';
            priceGrid.style.gridTemplateColumns = '1fr 1fr 1fr';
        } else {
            mrpGroup.style.display = 'none';
            priceGrid.style.gridTemplateColumns = '1fr 1fr';
        }
        calculateEditPricing();
    });

    document.getElementById('saveEditProductBtn').addEventListener('click', () => {
        const data = new FormData();
        data.append('id', document.getElementById('editProductId').value);
        data.append('name', document.getElementById('editProductName').value);
        data.append('category_id', document.getElementById('editCategorySelect').value);
        data.append('cost_price', document.getElementById('editProductCostPrice').value);
        data.append('selling_price', document.getElementById('editProductSellingPrice').value);
        data.append('stock', document.getElementById('editProductStock').value);
        data.append('description', document.getElementById('editProductDescription').value);
        data.append('has_mrp', editHasMrpCheckbox.checked ? 1 : 0);
        if(editHasMrpCheckbox.checked) data.append('mrp', document.getElementById('editProductMrp').value);

        fetch('edit_product.php', { method: 'POST', body: data })
        .then(r => r.text())
        .then(res => { if(res.trim() === 'success') location.reload(); else alert(res); });
    });

    // 4. Delete Logic
    function deleteProduct(id) {
        if(confirm("Delete this product?")) {
            const data = new FormData(); data.append('id', id);
            fetch('delete_product.php', { method: 'POST', body: data })
            .then(r => r.text()).then(res => { if(res.trim() === 'success') location.reload(); else alert(res); });
        }
    }

    // 5. SIMPLE STATS MODAL LOGIC
    function openStatsModal(id) {
        const modal = document.getElementById('statsModal');
        modal.style.display = 'flex';
        document.getElementById('statsLoading').style.display = 'block';
        document.getElementById('statsContent').style.display = 'none';
        
        const data = new FormData(); data.append('product_id', id);
        fetch('get_product_stats.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
            if(d.success) displaySimpleStats(d);
            else { alert('Error loading stats'); modal.style.display = 'none'; }
        })
        .catch(() => { modal.style.display = 'none'; });
    }

    function closeStatsModal() {
        document.getElementById('statsModal').style.display = 'none';
    }

    function displaySimpleStats(data) {
        document.getElementById('statsLoading').style.display = 'none';
        document.getElementById('statsContent').style.display = 'block';
        
        document.getElementById('statsProductName').textContent = data.product_name;
        document.getElementById('statsCurrentStock').textContent = data.current_stock;
        document.getElementById('statsTotalSold').textContent = data.total_sold;
        document.getElementById('statsTotalRevenue').textContent = formatIndianCurrency(data.total_revenue);
        
        // Simple Monthly Sales Table
        const mSales = document.getElementById('monthlySalesContent');
        if(data.monthly_sales.length === 0) mSales.innerHTML = '<p style="color:#777; font-style:italic;">No sales recorded.</p>';
        else {
            let h = '<table class="simple-table"><thead><tr><th>Month</th><th style="text-align:right">Qty</th><th style="text-align:right">Revenue</th></tr></thead><tbody>';
            data.monthly_sales.forEach(s => {
                const m = new Date(s.month + '-01').toLocaleDateString('en-US', {month:'short', year:'numeric'});
                h += `<tr>
                        <td>${m}</td>
                        <td style="text-align:right">${s.total_quantity}</td>
                        <td style="text-align:right; color:#10b981;">${formatIndianCurrency(s.total_amount)}</td>
                      </tr>`;
            });
            mSales.innerHTML = h + '</tbody></table>';
        }

        // Simple History List
        const sHist = document.getElementById('stockHistoryContent');
        if(data.stock_history.length === 0) sHist.innerHTML = '<p style="color:#777; font-style:italic;">No stock history.</p>';
        else {
            let h = '<table class="simple-table"><thead><tr><th>Date</th><th>Activity</th><th style="text-align:right"></th></tr></thead><tbody>';
            data.stock_history.forEach(item => {
                const isPur = item.type === 'purchase';
                const sign = isPur ? '+' : '';
                const colorClass = isPur ? 'text-green' : 'text-red';
                const date = new Date(item.date).toLocaleDateString('en-IN', {day:'numeric', month:'short'});
                
                h += `<tr>
                        <td>${date}</td>
                        <td>${item.description}</td>
                        <td style="text-align:right" class="${colorClass}">${sign}${Math.abs(item.quantity)}</td>
                      </tr>`;
            });
            sHist.innerHTML = h + '</tbody></table>';
        }
    }

    // 6. Category Modal Helper
    function openAddCategoryModal() { document.getElementById('addCategoryModal').style.display = 'flex'; }
    function closeAddCategoryModal() { document.getElementById('addCategoryModal').style.display = 'none'; }
    function saveCategoryFromModal() {
        const data = new FormData();
        data.append('name', document.getElementById('categoryName').value);
        data.append('gst_rate', document.getElementById('categoryGstRate').value);
        
        fetch('add_category.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if(res.status === 'success') {
                closeAddCategoryModal();
                loadCategories();
                setTimeout(() => document.getElementById('categorySelect').value = res.id, 500);
            } else alert(res.msg);
        });
    }
    
    // Global Click to close modals
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });
    </script>
</body>
</html>