<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
$admin_id = $_SESSION['admin_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purchases Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    body {
      background-color: #f5f7fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .page-header {
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 25px;
    }

    .page-header h2 {
      margin: 0;
      color: #2c3e50;
      font-size: 1.8rem;
    }

    .stats-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 25px;
    }

    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      border-left: 4px solid #3498db;
    }

    .stat-card h6 {
      color: #7f8c8d;
      font-size: 0.85rem;
      margin-bottom: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stat-card .stat-value {
      font-size: 1.8rem;
      font-weight: bold;
      color: #2c3e50;
    }

    .content-card {
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .search-filter-section {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .search-filter-section input,
    .search-filter-section select {
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 0.95rem;
    }

    .search-filter-section input[type="text"] {
      flex: 1;
      min-width: 250px;
    }

    .search-filter-section select {
      min-width: 150px;
    }

    .error-text {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: none;
    }

    .is-invalid {
      border-color: #dc3545 !important;
    }

    .purchase-row {
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .purchase-row:hover {
      background-color: #f8f9fa;
    }

    .details-row {
      display: none;
      background-color: #f8f9fa;
    }

    .details-table {
      margin: 15px 0;
    }

    .action-btn {
      margin: 0 3px;
    }

    .expand-icon {
      transition: transform 0.3s;
    }

    .expanded .expand-icon {
      transform: rotate(90deg);
    }

    .pricing-section {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin: 15px 0;
    }

    .pricing-section h6 {
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

    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }

    .empty-state i {
      font-size: 4rem;
      color: #bdc3c7;
      margin-bottom: 20px;
    }

    .empty-state h5 {
      color: #7f8c8d;
      margin-bottom: 10px;
    }

    .empty-state p {
      color: #95a5a6;
    }

    .table-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .btn-add-purchase {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 12px 25px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-add-purchase:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .status-badge {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .status-completed {
      background: #d4edda;
      color: #155724;
    }

    @media (max-width: 768px) {
      .stats-cards {
        grid-template-columns: 1fr;
      }

      .search-filter-section {
        flex-direction: column;
      }

      .search-filter-section input,
      .search-filter-section select {
        width: 100%;
      }
    }
  </style>
</head>
<body class="p-3">

  <!-- Page Header -->
  <div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2><i class="fas fa-truck-loading me-2"></i>Purchases Management</h2>
        <p class="text-muted mb-0">Track and manage all your purchase orders</p>
      </div>
      <button class="btn btn-primary btn-add-purchase" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
        <i class="fas fa-plus me-2"></i>New Purchase
      </button>
    </div>
  </div>

  <!-- Statistics Cards -->
  <?php
  // Get purchase statistics
  $stats_stmt = $conn->prepare("
    SELECT 
      COUNT(*) as total_purchases,
      COALESCE(SUM(total_amount), 0) as total_spent,
      COUNT(CASE WHEN purchase_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as purchases_this_month
    FROM purchases 
    WHERE admin_id = ?
  ");
  $stats_stmt->bind_param('i', $admin_id);
  $stats_stmt->execute();
  $stats = $stats_stmt->get_result()->fetch_assoc();
  $stats_stmt->close();

  // Get unique suppliers count
  $suppliers_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT supplier_id) as unique_suppliers 
    FROM purchases 
    WHERE admin_id = ?
  ");
  $suppliers_stmt->bind_param('i', $admin_id);
  $suppliers_stmt->execute();
  $suppliers_count = $suppliers_stmt->get_result()->fetch_assoc()['unique_suppliers'];
  $suppliers_stmt->close();
  ?>

  <div class="stats-cards">
    <div class="stat-card">
      <h6>Total Purchases</h6>
      <div class="stat-value"><?= number_format($stats['total_purchases']) ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #e74c3c;">
      <h6>Total Spent</h6>
      <div class="stat-value">₹<?= number_format($stats['total_spent'], 2) ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #27ae60;">
      <h6>This Month</h6>
      <div class="stat-value"><?= number_format($stats['purchases_this_month']) ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #f39c12;">
      <h6>Active Suppliers</h6>
      <div class="stat-value"><?= number_format($suppliers_count) ?></div>
    </div>
  </div>

  <!-- Purchases table -->
  <div class="content-card">
    <div class="table-actions">
      <h5 class="mb-0"><i class="fas fa-list me-2"></i>Purchase Orders</h5>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter-section">
      <input type="text" id="searchInput" placeholder="🔍 Search by ID or supplier..." onkeyup="filterTable()">
      <select id="supplierFilter" onchange="filterTable()">
        <option value="">All Suppliers</option>
        <?php
        $sup_stmt = $conn->prepare("
          SELECT DISTINCT s.id, s.name 
          FROM suppliers s
          INNER JOIN purchases p ON s.id = p.supplier_id
          WHERE s.admin_id = ?
          ORDER BY s.name
        ");
        $sup_stmt->bind_param('i', $admin_id);
        $sup_stmt->execute();
        $sup_res = $sup_stmt->get_result();
        while($sup = $sup_res->fetch_assoc()) {
          echo '<option value="'.$sup['id'].'">'.htmlspecialchars($sup['name']).'</option>';
        }
        $sup_stmt->close();
        ?>
      </select>
      <input type="date" id="dateFromFilter" onchange="filterTable()">
      <input type="date" id="dateToFilter" onchange="filterTable()">
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle" id="purchasesTable">
        <thead class="table-light">
          <tr>
            <th style="width: 50px;"></th>
            <th style="width: 80px;">ID</th>
            <th>Date</th>
            <th>Supplier</th>
            <th>Items</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th style="width: 200px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $stmt = $conn->prepare("
            SELECT p.id, p.purchase_date, p.total_amount, p.supplier_id,
                   s.name AS supplier_name,
                   COUNT(pi.id) as item_count
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            LEFT JOIN purchase_items pi ON p.id = pi.purchase_id
            WHERE p.admin_id = ?
            GROUP BY p.id
            ORDER BY p.purchase_date DESC, p.id DESC
          ");
          $stmt->bind_param('i', $admin_id);
          $stmt->execute();
          $res = $stmt->get_result();
          
          if($res->num_rows > 0):
            while($row = $res->fetch_assoc()):
              $purchase_id = $row['id'];
              
              $items_stmt = $conn->prepare("
                SELECT pi.*, pr.name as product_name, pr.has_mrp, pr.mrp
                FROM purchase_items pi
                JOIN products pr ON pi.product_id = pr.id
                WHERE pi.purchase_id = ?
              ");
              $items_stmt->bind_param('i', $purchase_id);
              $items_stmt->execute();
              $items_res = $items_stmt->get_result();
              $items = [];
              while($item = $items_res->fetch_assoc()) {
                $items[] = $item;
              }
              $items_stmt->close();
          ?>
          <tr class="purchase-row" onclick="toggleDetails(<?= $purchase_id ?>)" 
              data-supplier="<?= $row['supplier_id'] ?>"
              data-date="<?= $row['purchase_date'] ?>">
            <td class="text-center">
              <i class="fas fa-chevron-right expand-icon" id="icon-<?= $purchase_id ?>"></i>
            </td>
            <td><strong>#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
            <td><?= date('M d, Y', strtotime($row['purchase_date'])) ?></td>
            <td>
              <strong><?= htmlspecialchars($row['supplier_name'] ?? 'N/A') ?></strong>
            </td>
            <td>
              <span class="badge bg-info"><?= $row['item_count'] ?> items</span>
            </td>
            <td><strong class="text-danger">₹<?= number_format($row['total_amount'], 2) ?></strong></td>
            <td><span class="status-badge status-completed">Completed</span></td>
            <td>
              <button class="btn btn-sm btn-success action-btn" 
                      onclick="event.stopPropagation(); window.open('purchase_invoice.php?id=<?= $purchase_id ?>', '_blank')"
                      title="View Invoice">
                <i class="fas fa-file-invoice"></i> Invoice
              </button>
              <button class="btn btn-sm btn-danger action-btn" 
                      onclick="event.stopPropagation(); deletePurchase(<?= $purchase_id ?>)"
                      title="Delete Purchase">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          <tr class="details-row" id="details-<?= $purchase_id ?>">
            <td colspan="8">
              <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0"><i class="fas fa-box me-2"></i>Purchase Items</h6>
                  <small class="text-muted">Purchase Date: <?= date('F d, Y', strtotime($row['purchase_date'])) ?></small>
                </div>
                <table class="table table-sm table-striped details-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Quantity</th>
                      <th>Unit Price</th>
                      <th>Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                      <td>
                        <?= htmlspecialchars($item['product_name']) ?>
                        <?php if($item['has_mrp'] && $item['mrp'] > 0): ?>
                          <span class="badge-mrp">MRP</span>
                        <?php endif; ?>
                      </td>
                      <td><strong><?= $item['quantity'] ?></strong></td>
                      <td>₹<?= number_format($item['unit_price'], 2) ?></td>
                      <td><strong>₹<?= number_format($item['subtotal'], 2) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-active">
                      <td colspan="3" class="text-end"><strong>Total:</strong></td>
                      <td><strong class="text-danger">₹<?= number_format($row['total_amount'], 2) ?></strong></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </td>
          </tr>
          <?php 
            endwhile;
          else:
          ?>
          <tr>
            <td colspan="8">
              <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h5>No Purchases Yet</h5>
                <p>Start by creating your first purchase order</p>
                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
                  <i class="fas fa-plus me-2"></i>Create Purchase Order
                </button>
              </div>
            </td>
          </tr>
          <?php 
          endif;
          $stmt->close();
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add Purchase Modal -->
  <div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form id="purchaseForm">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-truck-loading me-2"></i>Create New Purchase Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="supplierSelect" class="form-label">Select Supplier <span class="text-danger">*</span></label>
                <div class="input-group">
                  <select id="supplierSelect" name="supplier_id" class="form-select" required></select>
                  <button type="button" class="btn btn-outline-secondary" id="addSupplierBtn">
                    <i class="fas fa-plus"></i> New
                  </button>
                </div>
              </div>
              <div class="col-md-6">
                <label for="purchaseDate" class="form-label">Purchase Date</label>
                <input type="date" id="purchaseDate" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>">
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-box me-2"></i>Purchase Items</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table" id="purchaseItemsTable">
                    <thead>
                      <tr>
                        <th style="width: 40%;">Product</th>
                        <th style="width: 15%;">Quantity</th>
                        <th style="width: 20%;">Unit Price</th>
                        <th style="width: 20%;">Subtotal</th>
                        <th style="width: 5%;"></th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="addRowBtn">
                  <i class="fas fa-plus me-1"></i> Add Product
                </button>
              </div>
            </div>

            <div class="row">
              <div class="col-md-8">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any additional notes..."></textarea>
              </div>
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="card-title">Order Summary</h6>
                    <div class="d-flex justify-content-between mb-2">
                      <span>Items:</span>
                      <strong id="itemCount">0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                      <span>Total Quantity:</span>
                      <strong id="totalQty">0</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                      <h5 class="mb-0">Grand Total:</h5>
                      <h5 class="mb-0 text-danger">₹<span id="grandTotal">0.00</span></h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-2"></i>Save Purchase
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Supplier Modal -->
  <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="supplierForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-truck me-2"></i>Add New Supplier</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-12 mb-3">
                <label for="supplierName" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                <input type="text" id="supplierName" name="name" class="form-control" required>
                <div class="error-text" id="supplier_name_error">Name is required</div>
              </div>
              <div class="col-12 mb-3">
                <label for="supplierStreetAddress" class="form-label">Street Address <span class="text-danger">*</span></label>
                <input type="text" id="supplierStreetAddress" name="street_address" class="form-control" required>
                <div class="error-text" id="supplier_street_error">Street address is required</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="supplierCity" class="form-label">City <span class="text-danger">*</span></label>
                <input type="text" id="supplierCity" name="city" class="form-control" required>
                <div class="error-text" id="supplier_city_error">City is required</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="supplierPinCode" class="form-label">Pin Code <span class="text-danger">*</span></label>
                <input type="text" id="supplierPinCode" name="pin_code" class="form-control" maxlength="6" required>
                <small class="text-muted">6 digits</small>
                <div class="error-text" id="supplier_pin_error">Pin code must be exactly 6 digits</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="supplierState" class="form-label">State <span class="text-danger">*</span></label>
                <input type="text" id="supplierState" name="state" class="form-control" required>
                <div class="error-text" id="supplier_state_error">State is required</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="supplierPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="tel" id="supplierPhone" name="phone" class="form-control" maxlength="10" required>
                <small class="text-muted">10-digit number (starts with 6-9)</small>
                <div class="error-text" id="supplier_phone_error">Phone must be a valid 10-digit number</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="supplierEmail" class="form-label">Email</label>
                <input type="email" id="supplierEmail" name="email" class="form-control">
                <small class="text-muted">Optional</small>
                <div class="error-text" id="supplier_email_error">Invalid email format</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-2"></i>Save Supplier
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Product Modal -->
  <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="productForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-box me-2"></i>Add New Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="productName" class="form-label">Product Name <span class="text-danger">*</span></label>
              <input type="text" id="productName" name="name" class="form-control" placeholder="Enter product name" required>
            </div>
            <div class="mb-3">
              <label for="productCategory" class="form-label">Category <span class="text-danger">*</span></label>
              <div class="input-group">
                <select id="productCategory" name="category_id" class="form-select" required>
                  <option value="">-- Select Category --</option>
                </select>
                <button type="button" class="btn btn-outline-secondary" id="addCategoryBtn">
                  <i class="fas fa-plus"></i> New
                </button>
              </div>
            </div>
            
            <div class="pricing-section">
              <h6>💰 Pricing Setup</h6>
              
              <div class="mb-3">
                <label for="productCostPrice" class="form-label">Cost Price (What You Paid) <span class="text-danger">*</span></label>
                <input type="number" id="productCostPrice" name="cost_price" class="form-control" placeholder="₹100" step="0.01" min="0" required>
                <small class="text-muted">Amount paid to supplier (GST inclusive)</small>
              </div>
              
              <div class="mb-3" style="background: white; padding: 12px; border-radius: 6px;">
                <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                  <input type="checkbox" id="hasMrpCheckbox" name="has_mrp" style="width: 18px; height: 18px; margin-right: 10px;">
                  <span style="font-weight: 600;">This product has MRP printed on package</span>
                </label>
                <small class="text-muted" style="margin-left: 28px; display: block; margin-top: 5px;">
                  Check this for packaged goods (chips, biscuits, bottles, etc.)
                </small>
              </div>
              
              <div class="mb-3" id="mrpInputGroup" style="display: none;">
                <label for="productMrp" class="form-label">Maximum Retail Price (MRP) <span class="text-danger">*</span></label>
                <input type="number" id="productMrp" name="mrp" class="form-control" placeholder="₹120" step="0.01" min="0">
                <small class="text-muted">Price printed on the package</small>
              </div>
              
              <div class="mb-3">
                <label for="productSellingPrice" class="form-label">Your Selling Price <span class="text-danger">*</span></label>
                <input type="number" id="productSellingPrice" name="selling_price" class="form-control" placeholder="₹110" step="0.01" min="0" required>
                <small class="text-muted" id="sellingPriceHint">What you charge customers</small>
              </div>
              
              <div style="text-align: center; margin: 10px 0; color: #999; font-weight: 600;">OR</div>
              
              <div class="mb-3">
                <label for="productProfitMargin" class="form-label">Profit Margin (Optional)</label>
                <input type="number" id="productProfitMargin" name="profit_margin" class="form-control" placeholder="₹10" step="0.01" min="0">
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
            
            <div class="mb-3">
              <label for="productStock" class="form-label">Initial Stock <span class="text-danger">*</span></label>
              <input type="number" id="productStock" name="stock" class="form-control" placeholder="Enter stock quantity" min="0" value="0" required>
            </div>
            
            <div class="mb-3">
              <label for="productSupplier" class="form-label">Supplier (Optional)</label>
              <select id="productSupplier" name="supplier_id" class="form-select">
                <option value="">-- Select Supplier --</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label for="productDescription" class="form-label">Description</label>
              <textarea id="productDescription" name="description" class="form-control" rows="2" placeholder="Enter product description"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-2"></i>Save Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Category Modal -->
  <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="categoryForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Add New Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
              <input type="text" id="categoryName" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="categoryDescription" class="form-label">Description</label>
              <input type="text" id="categoryDescription" name="description" class="form-control">
            </div>
            <div class="mb-3">
              <label for="categoryGstRate" class="form-label">GST Rate (%) <span class="text-danger">*</span></label>
              <input type="number" id="categoryGstRate" name="gst_rate" class="form-control" step="0.01" min="0" max="100" value="0" required>
              <small class="text-muted">Enter rate between 0-100%</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-2"></i>Save Category
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  let products = [];
  let addSupplierModal, addPurchaseModal, addProductModal, addCategoryModal;
  let currentProductRow = null;

  const phonePattern = /^[6-9]\d{9}$/;
  const pinPattern = /^\d{6}$/;
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function validateField(field, errorElement, validationFn, errorMsg) {
    const value = field.value.trim();
    const isValid = validationFn(value);
    
    if (!isValid) {
      field.classList.add('is-invalid');
      if (errorElement) {
        errorElement.style.display = 'block';
        if (errorMsg) errorElement.textContent = errorMsg;
      }
    } else {
      field.classList.remove('is-invalid');
      if (errorElement) errorElement.style.display = 'none';
    }
    
    return isValid;
  }

  // Filter table function
  function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const supplierValue = document.getElementById('supplierFilter').value;
    const dateFrom = document.getElementById('dateFromFilter').value;
    const dateTo = document.getElementById('dateToFilter').value;
    
    const table = document.getElementById('purchasesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      if (row.classList.contains('details-row')) continue;
      
      const id = row.cells[1]?.textContent.toLowerCase() || '';
      const supplier = row.cells[3]?.textContent.toLowerCase() || '';
      const supplierId = row.getAttribute('data-supplier') || '';
      const rowDate = row.getAttribute('data-date') || '';
      
      const matchesSearch = id.includes(searchValue) || supplier.includes(searchValue);
      const matchesSupplier = !supplierValue || supplierId === supplierValue;
      const matchesDateFrom = !dateFrom || rowDate >= dateFrom;
      const matchesDateTo = !dateTo || rowDate <= dateTo;
      
      if (matchesSearch && matchesSupplier && matchesDateFrom && matchesDateTo) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
        // Hide corresponding details row
        const detailsRow = document.getElementById('details-' + row.onclick?.toString().match(/\d+/)?.[0]);
        if (detailsRow) detailsRow.style.display = 'none';
      }
    }
  }

  function toggleDetails(id) {
    const detailsRow = document.getElementById('details-' + id);
    const icon = document.getElementById('icon-' + id);
    const parentRow = icon.closest('tr');
    
    if (detailsRow.style.display === 'table-row') {
      detailsRow.style.display = 'none';
      parentRow.classList.remove('expanded');
    } else {
      detailsRow.style.display = 'table-row';
      parentRow.classList.add('expanded');
    }
  }

  function deletePurchase(id) {
    if (!confirm('Are you sure you want to delete this purchase?\n\nThis will also remove all associated items and reverse stock changes.')) {
      return;
    }
    
    $.ajax({
      url: 'delete_purchase.php',
      method: 'POST',
      data: { id: id },
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Purchase deleted successfully!');
          try {
            window.parent.postMessage('refreshDashboard', '*');
          } catch(e) {}
          location.reload();
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to delete purchase.');
      }
    });
  }

  $(document).ready(function() {
    addSupplierModal = new bootstrap.Modal(document.getElementById('addSupplierModal'));
    addPurchaseModal = new bootstrap.Modal(document.getElementById('addPurchaseModal'));
    addProductModal = new bootstrap.Modal(document.getElementById('addProductModal'));
    addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
  });

  $('#addPurchaseModal').on('show.bs.modal', function() {
    loadSuppliers();
    loadProducts();
  });

  function loadSuppliers() {
    $.getJSON('get_suppliers.php', function(data) {
      let html = '<option value="">-- Select Supplier --</option>';
      data.forEach(s => html += `<option value="${s.id}">${s.name}</option>`);
      $('#supplierSelect, #productSupplier').html(html);
    });
  }

  function loadProducts() {
    $.getJSON('get_products.php', function(data) {
      products = data;
    });
  }

  function loadCategories() {
    $.getJSON('get_categories.php', function(data) {
      let html = '<option value="">-- Select Category --</option>';
      data.forEach(c => html += `<option value="${c.id}">${c.name}</option>`);
      $('#productCategory').html(html);
    });
  }

  // Add Supplier Modal
  $('#addSupplierBtn').click(function() {
    $('#supplierForm')[0].reset();
    $('.error-text').hide();
    $('.is-invalid').removeClass('is-invalid');
    addSupplierModal.show();
  });

  $('#supplierPhone').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 10);
  });

  $('#supplierPinCode').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 6);
  });

  $('#supplierForm').submit(function(e) {
    e.preventDefault();
    
    let isValid = true;
    
    isValid &= validateField(
      document.getElementById('supplierName'),
      document.getElementById('supplier_name_error'),
      (v) => v.length > 0,
      'Name is required'
    );
    
    isValid &= validateField(
      document.getElementById('supplierStreetAddress'),
      document.getElementById('supplier_street_error'),
      (v) => v.length > 0,
      'Street address is required'
    );
    
    isValid &= validateField(
      document.getElementById('supplierCity'),
      document.getElementById('supplier_city_error'),
      (v) => v.length > 0,
      'City is required'
    );
    
    isValid &= validateField(
      document.getElementById('supplierPinCode'),
      document.getElementById('supplier_pin_error'),
      (v) => pinPattern.test(v),
      'Pin code must be exactly 6 digits'
    );
    
    isValid &= validateField(
      document.getElementById('supplierState'),
      document.getElementById('supplier_state_error'),
      (v) => v.length > 0,
      'State is required'
    );
    
    isValid &= validateField(
      document.getElementById('supplierPhone'),
      document.getElementById('supplier_phone_error'),
      (v) => phonePattern.test(v),
      'Phone must be a valid 10-digit number'
    );
    
    const emailField = document.getElementById('supplierEmail');
    const emailValue = emailField.value.trim();
    if (emailValue.length > 0) {
      isValid &= validateField(
        emailField,
        document.getElementById('supplier_email_error'),
        (v) => emailPattern.test(v),
        'Invalid email format'
      );
    }
    
    if (!isValid) return;
    
    $.ajax({
      url: 'add_supplier.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          alert('Supplier added successfully!');
          $('#supplierForm')[0].reset();
          addSupplierModal.hide();
          loadSuppliers();
          setTimeout(() => $('#supplierSelect').val(res.id), 100);
        } else {
          alert('Error: ' + (res.msg || 'Unknown error'));
        }
      },
      error: function() {
        alert('Failed to add supplier.');
      }
    });
  });

  // Purchase Items Management
  $('#addRowBtn').click(function() {
    let row = `
      <tr>
        <td>
          <div class="input-group">
            <select class="form-select productSelect" name="product_id[]" required>
              <option value="">Select Product</option>
              ${products.map(p => `<option value="${p.id}" data-price="${p.cost_price}">${p.name}</option>`).join('')}
            </select>
            <button type="button" class="btn btn-outline-secondary btn-sm addNewProductBtn">
              <i class="fas fa-plus"></i>
            </button>
          </div>
        </td>
        <td><input type="number" name="quantity[]" class="form-control qtyInput" min="1" value="1" required></td>
        <td><input type="number" name="unit_price[]" class="form-control priceInput" min="0" step="0.01" required></td>
        <td class="subtotal">₹0.00</td>
        <td><button type="button" class="btn btn-sm btn-danger removeRow"><i class="fas fa-times"></i></button></td>
      </tr>`;
    $('#purchaseItemsTable tbody').append(row);
  });

  $(document).on('click', '.addNewProductBtn', function() {
    currentProductRow = $(this).closest('tr');
    loadCategories();
    loadSuppliers();
    $('#productForm')[0].reset();
    $('.error-text').hide();
    $('.is-invalid').removeClass('is-invalid');
    $('#mrpInputGroup').hide();
    $('#displayMrpRow').hide();
    $('#hasMrpCheckbox').prop('checked', false);
    calculateProductPricing();
    addProductModal.show();
  });

  // MRP Checkbox Logic
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
    calculateProductPricing();
  });

  // Product Pricing Calculation
  function calculateProductPricing() {
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

  document.getElementById('productCostPrice').addEventListener('input', calculateProductPricing);
  document.getElementById('productMrp').addEventListener('input', calculateProductPricing);
  document.getElementById('productSellingPrice').addEventListener('input', calculateProductPricing);
  document.getElementById('productProfitMargin').addEventListener('input', calculateProductPricing);

  // Add Category Modal
  $('#addCategoryBtn').click(function() {
    $('#categoryForm')[0].reset();
    addCategoryModal.show();
  });

  $('#categoryForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
      url: 'add_category.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          alert('Category added successfully!');
          $('#categoryForm')[0].reset();
          addCategoryModal.hide();
          loadCategories();
          setTimeout(() => $('#productCategory').val(res.id), 100);
        } else {
          alert('Error: ' + (res.msg || 'Unknown error'));
        }
      },
      error: function() {
        alert('Failed to add category.');
      }
    });
  });

  // Submit Product Form
  $('#productForm').submit(function(e) {
    e.preventDefault();
    
    const name = $('#productName').val().trim();
    const category_id = $('#productCategory').val();
    const cost_price = $('#productCostPrice').val().trim();
    const has_mrp = $('#hasMrpCheckbox').is(':checked') ? 1 : 0;
    const mrp = has_mrp ? $('#productMrp').val().trim() : '';
    const selling_price = $('#productSellingPrice').val().trim();
    const stock = $('#productStock').val().trim();

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

    $.ajax({
      url: 'add_product.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Product added successfully!');
          $('#productForm')[0].reset();
          addProductModal.hide();
          loadProducts();
          setTimeout(() => {
            refreshProductDropdowns();
          }, 200);
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to add product.');
      }
    });
  });

  function refreshProductDropdowns() {
    $('.productSelect').each(function() {
      const currentVal = $(this).val();
      let html = '<option value="">Select Product</option>';
      products.forEach(p => {
        html += `<option value="${p.id}" data-price="${p.cost_price}">${p.name}</option>`;
      });
      $(this).html(html);
      
      if (currentVal) {
        $(this).val(currentVal);
      }
      
      if (currentProductRow && $(this).closest('tr').is(currentProductRow)) {
        if (products.length > 0) {
          const lastProduct = products[products.length - 1];
          $(this).val(lastProduct.id);
          $(this).closest('tr').find('.priceInput').val(lastProduct.cost_price);
          updateTotals();
        }
      }
    });
    currentProductRow = null;
  }

  $(document).on('change', '.productSelect', function() {
    const price = $(this).find(':selected').data('price') || 0;
    $(this).closest('tr').find('.priceInput').val(price);
    updateTotals();
  });

  $(document).on('input', '.qtyInput, .priceInput', updateTotals);

  $(document).on('click', '.removeRow', function() {
    $(this).closest('tr').remove();
    updateTotals();
  });

  function updateTotals() {
    let grandTotal = 0;
    let itemCount = 0;
    let totalQty = 0;
    
    $('#purchaseItemsTable tbody tr').each(function() {
      const qty = parseFloat($(this).find('.qtyInput').val()) || 0;
      const price = parseFloat($(this).find('.priceInput').val()) || 0;
      const subtotal = qty * price;
      $(this).find('.subtotal').text('₹' + subtotal.toFixed(2));
      grandTotal += subtotal;
      itemCount++;
      totalQty += qty;
    });
    
    $('#grandTotal').text(grandTotal.toFixed(2));
    $('#itemCount').text(itemCount);
    $('#totalQty').text(totalQty);
  }

  $('#purchaseForm').submit(function(e) {
    e.preventDefault();
    
    if ($('#purchaseItemsTable tbody tr').length === 0) {
      alert('Please add at least one product to the purchase order.');
      return;
    }
    
    $.ajax({
      url: 'add_purchase.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Purchase added successfully!');
          
          try {
            window.parent.postMessage('refreshDashboard', '*');
          } catch(e) {}
          
          location.reload();
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to save purchase.');
      }
    });
  });
  </script>

</body>
</html>