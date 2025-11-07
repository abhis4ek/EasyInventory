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
  <title>Sales Management</title>
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
      border-left: 4px solid #27ae60;
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

    .sale-row {
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .sale-row:hover {
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

    .btn-add-sale {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      border: none;
      padding: 12px 25px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
    }

    .btn-add-sale:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(17, 153, 142, 0.4);
    }

    .status-badge {
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .status-paid {
      background: #d4edda;
      color: #155724;
    }

    .customer-badge {
      background: #e3f2fd;
      color: #1976d2;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .walk-in-badge {
      background: #fff3cd;
      color: #856404;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: 600;
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
        <h2><i class="fas fa-shopping-cart me-2"></i>Sales Management</h2>
        <p class="text-muted mb-0">Record and track all your sales transactions</p>
      </div>
      <button class="btn btn-success btn-add-sale" data-bs-toggle="modal" data-bs-target="#addSaleModal">
        <i class="fas fa-plus me-2"></i>New Sale
      </button>
    </div>
  </div>

  <!-- Statistics Cards -->
  <?php
  // Get sales statistics
  $stats_stmt = $conn->prepare("
    SELECT 
      COUNT(*) as total_sales,
      COALESCE(SUM(total_amount), 0) as total_revenue,
      COUNT(CASE WHEN sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as sales_this_month,
      AVG(total_amount) as avg_sale_value
    FROM sales 
    WHERE admin_id = ?
  ");
  $stats_stmt->bind_param('i', $admin_id);
  $stats_stmt->execute();
  $stats = $stats_stmt->get_result()->fetch_assoc();
  $stats_stmt->close();

  // Get unique customers count
  $customers_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT customer_id) as unique_customers 
    FROM sales 
    WHERE admin_id = ? AND customer_id IS NOT NULL
  ");
  $customers_stmt->bind_param('i', $admin_id);
  $customers_stmt->execute();
  $customers_count = $customers_stmt->get_result()->fetch_assoc()['unique_customers'];
  $customers_stmt->close();

  // Get today's sales
  $today_stmt = $conn->prepare("
    SELECT COUNT(*) as today_sales, COALESCE(SUM(total_amount), 0) as today_revenue
    FROM sales 
    WHERE admin_id = ? AND sale_date = CURDATE()
  ");
  $today_stmt->bind_param('i', $admin_id);
  $today_stmt->execute();
  $today = $today_stmt->get_result()->fetch_assoc();
  $today_stmt->close();
  ?>

  <div class="stats-cards">
    <div class="stat-card">
      <h6>Total Sales</h6>
      <div class="stat-value"><?= number_format($stats['total_sales']) ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #27ae60;">
      <h6>Total Revenue</h6>
      <div class="stat-value">₹<?= number_format($stats['total_revenue'], 2) ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #3498db;">
      <h6>This Month</h6>
      <div class="stat-value"><?= number_format($stats['sales_this_month']) ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #e74c3c;">
      <h6>Today's Sales</h6>
      <div class="stat-value"><?= $today['today_sales'] ?></div>
      <small class="text-muted">₹<?= number_format($today['today_revenue'], 2) ?></small>
    </div>
    <div class="stat-card" style="border-left-color: #f39c12;">
      <h6>Avg Sale Value</h6>
      <div class="stat-value">₹<?= number_format($stats['avg_sale_value'], 2) ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #9b59b6;">
      <h6>Active Customers</h6>
      <div class="stat-value"><?= number_format($customers_count) ?></div>
    </div>
  </div>

  <!-- Sales table -->
  <div class="content-card">
    <div class="table-actions">
      <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Sales Transactions</h5>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter-section">
      <input type="text" id="searchInput" placeholder="🔍 Search by ID or customer..." onkeyup="filterTable()">
      <select id="customerFilter" onchange="filterTable()">
        <option value="">All Customers</option>
        <option value="walk-in">Walk-in Customers</option>
        <?php
        $cust_stmt = $conn->prepare("
          SELECT DISTINCT c.id, c.name 
          FROM customers c
          INNER JOIN sales s ON c.id = s.customer_id
          WHERE c.admin_id = ?
          ORDER BY c.name
        ");
        $cust_stmt->bind_param('i', $admin_id);
        $cust_stmt->execute();
        $cust_res = $cust_stmt->get_result();
        while($cust = $cust_res->fetch_assoc()) {
          echo '<option value="'.$cust['id'].'">'.htmlspecialchars($cust['name']).'</option>';
        }
        $cust_stmt->close();
        ?>
      </select>
      <input type="date" id="dateFromFilter" onchange="filterTable()">
      <input type="date" id="dateToFilter" onchange="filterTable()">
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle" id="salesTable">
        <thead class="table-light">
          <tr>
            <th style="width: 50px;"></th>
            <th style="width: 80px;">ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th style="width: 200px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $stmt = $conn->prepare("
            SELECT s.id, s.sale_date, s.total_amount, s.customer_id,
                   c.name AS customer_name,
                   COUNT(si.id) as item_count
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            LEFT JOIN sale_items si ON s.id = si.sale_id
            WHERE s.admin_id = ?
            GROUP BY s.id
            ORDER BY s.sale_date DESC, s.id DESC
          ");
          $stmt->bind_param('i', $admin_id);
          $stmt->execute();
          $res = $stmt->get_result();
          
          if($res->num_rows > 0):
            while($row = $res->fetch_assoc()):
              $sale_id = $row['id'];
              
              $items_stmt = $conn->prepare("
                SELECT si.*, pr.name as product_name, pr.has_mrp, pr.mrp
                FROM sale_items si
                JOIN products pr ON si.product_id = pr.id
                WHERE si.sale_id = ?
              ");
              $items_stmt->bind_param('i', $sale_id);
              $items_stmt->execute();
              $items_res = $items_stmt->get_result();
              $items = [];
              while($item = $items_res->fetch_assoc()) {
                $items[] = $item;
              }
              $items_stmt->close();
              
              $customer_name = $row['customer_name'] ?? 'Walk-in Customer';
              $is_walk_in = empty($row['customer_id']);
          ?>
          <tr class="sale-row" onclick="toggleDetails(<?= $sale_id ?>)" 
              data-customer="<?= $row['customer_id'] ?? 'walk-in' ?>"
              data-date="<?= $row['sale_date'] ?>">
            <td class="text-center">
              <i class="fas fa-chevron-right expand-icon" id="icon-<?= $sale_id ?>"></i>
            </td>
            <td><strong>#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
            <td><?= date('M d, Y', strtotime($row['sale_date'])) ?></td>
            <td>
              <?php if($is_walk_in): ?>
                <span class="walk-in-badge"><i class="fas fa-user me-1"></i>Walk-in</span>
              <?php else: ?>
                <span class="customer-badge"><i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($customer_name) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge bg-info"><?= $row['item_count'] ?> items</span>
            </td>
            <td><strong class="text-success">₹<?= number_format($row['total_amount'], 2) ?></strong></td>
            <td><span class="status-badge status-paid">Paid</span></td>
            <td>
              <button class="btn btn-sm btn-success action-btn" 
                      onclick="event.stopPropagation(); window.open('sale_invoice.php?id=<?= $sale_id ?>', '_blank')"
                      title="View Invoice">
                <i class="fas fa-file-invoice"></i> Invoice
              </button>
              <button class="btn btn-sm btn-danger action-btn" 
                      onclick="event.stopPropagation(); deleteSale(<?= $sale_id ?>)"
                      title="Delete Sale">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          <tr class="details-row" id="details-<?= $sale_id ?>">
            <td colspan="8">
              <div class="p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Sold Items</h6>
                  <small class="text-muted">Sale Date: <?= date('F d, Y', strtotime($row['sale_date'])) ?></small>
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
                          <span class="badge bg-warning text-dark ms-2">MRP</span>
                        <?php endif; ?>
                      </td>
                      <td><strong><?= $item['quantity'] ?></strong></td>
                      <td>₹<?= number_format($item['unit_price'], 2) ?></td>
                      <td><strong>₹<?= number_format($item['subtotal'], 2) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-active">
                      <td colspan="3" class="text-end"><strong>Total:</strong></td>
                      <td><strong class="text-success">₹<?= number_format($row['total_amount'], 2) ?></strong></td>
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
                <i class="fas fa-shopping-cart"></i>
                <h5>No Sales Yet</h5>
                <p>Start recording your first sale transaction</p>
                <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                  <i class="fas fa-plus me-2"></i>Record Sale
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

  <!-- Add Sale Modal -->
  <div class="modal fade" id="addSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form id="saleForm">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>Record New Sale</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="customerSelect" class="form-label">Select Customer</label>
                <div class="input-group">
                  <select id="customerSelect" name="customer_id" class="form-select"></select>
                  <button type="button" class="btn btn-outline-secondary" id="addCustomerBtn">
                    <i class="fas fa-plus"></i> New
                  </button>
                </div>
                <small class="text-muted">Leave blank for walk-in customers</small>
              </div>
              <div class="col-md-6">
                <label for="saleDate" class="form-label">Sale Date</label>
                <input type="date" id="saleDate" name="sale_date" class="form-control" value="<?= date('Y-m-d') ?>">
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Sale Items</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table" id="saleItemsTable">
                    <thead>
                      <tr>
                        <th style="width: 40%;">Product</th>
                        <th style="width: 15%;">Available</th>
                        <th style="width: 15%;">Quantity</th>
                        <th style="width: 15%;">Price</th>
                        <th style="width: 10%;">Subtotal</th>
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
                    <h6 class="card-title">Sale Summary</h6>
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
                      <h5 class="mb-0 text-success">₹<span id="grandTotal">0.00</span></h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-2"></i>Complete Sale
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Customer Modal -->
  <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="customerForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-12 mb-3">
                <label for="customerName" class="form-label">Customer Name <span class="text-danger">*</span></label>
                <input type="text" id="customerName" name="name" class="form-control" required>
                <div class="error-text" id="customer_name_error">Name is required</div>
              </div>
              <div class="col-12 mb-3">
                <label for="customerStreetAddress" class="form-label">Street Address <span class="text-danger">*</span></label>
                <input type="text" id="customerStreetAddress" name="street_address" class="form-control" required>
                <div class="error-text" id="customer_street_error">Street address is required</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="customerCity" class="form-label">City <span class="text-danger">*</span></label>
                <input type="text" id="customerCity" name="city" class="form-control" required>
                <div class="error-text" id="customer_city_error">City is required</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="customerPinCode" class="form-label">Pin Code <span class="text-danger">*</span></label>
                <input type="text" id="customerPinCode" name="pin_code" class="form-control" maxlength="6" required>
                <small class="text-muted">6 digits</small>
                <div class="error-text" id="customer_pin_error">Pin code must be exactly 6 digits</div>
              </div>
              <div class="col-md-4 mb-3">
                <label for="customerState" class="form-label">State <span class="text-danger">*</span></label>
                <input type="text" id="customerState" name="state" class="form-control" required>
                <div class="error-text" id="customer_state_error">State is required</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="customerPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="tel" id="customerPhone" name="phone" class="form-control" maxlength="10" required>
                <small class="text-muted">10-digit number (starts with 6-9)</small>
                <div class="error-text" id="customer_phone_error">Phone must be a valid 10-digit number</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="customerEmail" class="form-label">Email</label>
                <input type="email" id="customerEmail" name="email" class="form-control">
                <small class="text-muted">Optional</small>
                <div class="error-text" id="customer_email_error">Invalid email format</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save me-2"></i>Save Customer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  let products = [];
  let addCustomerModal, addSaleModal;

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
    const customerValue = document.getElementById('customerFilter').value;
    const dateFrom = document.getElementById('dateFromFilter').value;
    const dateTo = document.getElementById('dateToFilter').value;
    
    const table = document.getElementById('salesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
      const row = rows[i];
      if (row.classList.contains('details-row')) continue;
      
      const id = row.cells[1]?.textContent.toLowerCase() || '';
      const customer = row.cells[3]?.textContent.toLowerCase() || '';
      const customerId = row.getAttribute('data-customer') || '';
      const rowDate = row.getAttribute('data-date') || '';
      
      const matchesSearch = id.includes(searchValue) || customer.includes(searchValue);
      const matchesCustomer = !customerValue || customerId === customerValue;
      const matchesDateFrom = !dateFrom || rowDate >= dateFrom;
      const matchesDateTo = !dateTo || rowDate <= dateTo;
      
      if (matchesSearch && matchesCustomer && matchesDateFrom && matchesDateTo) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
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

  function deleteSale(id) {
    if (!confirm('Are you sure you want to delete this sale?\n\nThis will also remove all associated items and restore stock.')) {
      return;
    }
    
    $.ajax({
      url: 'delete_sale.php',
      method: 'POST',
      data: { id: id },
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Sale deleted successfully!');
          try {
            window.parent.postMessage('refreshDashboard', '*');
          } catch(e) {}
          location.reload();
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to delete sale.');
      }
    });
  }

  $(document).ready(function() {
    addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    addSaleModal = new bootstrap.Modal(document.getElementById('addSaleModal'));
  });

  $('#addSaleModal').on('show.bs.modal', function() {
    loadCustomers();

    if (products.length === 0) {
      $.getJSON('get_products.php', function(data) {
        products = data;
      });
    }
  });

  function loadCustomers() {
    $.getJSON('get_customers.php', function(data) {
      let html = '<option value="">-- Walk-in Customer --</option>';
      data.forEach(c => html += `<option value="${c.id}">${c.name}</option>`);
      $('#customerSelect').html(html);
    });
  }

  $('#addCustomerBtn').click(function() {
    $('#customerForm')[0].reset();
    $('.error-text').hide();
    $('.is-invalid').removeClass('is-invalid');
    addCustomerModal.show();
  });

  $('#customerPhone').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 10);
  });

  $('#customerPinCode').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 6);
  });

  $('#customerForm').submit(function(e) {
    e.preventDefault();
    
    let isValid = true;
    
    isValid &= validateField(
      document.getElementById('customerName'),
      document.getElementById('customer_name_error'),
      (v) => v.length > 0,
      'Name is required'
    );
    
    isValid &= validateField(
      document.getElementById('customerStreetAddress'),
      document.getElementById('customer_street_error'),
      (v) => v.length > 0,
      'Street address is required'
    );
    
    isValid &= validateField(
      document.getElementById('customerCity'),
      document.getElementById('customer_city_error'),
      (v) => v.length > 0,
      'City is required'
    );
    
    isValid &= validateField(
      document.getElementById('customerPinCode'),
      document.getElementById('customer_pin_error'),
      (v) => pinPattern.test(v),
      'Pin code must be exactly 6 digits'
    );
    
    isValid &= validateField(
      document.getElementById('customerState'),
      document.getElementById('customer_state_error'),
      (v) => v.length > 0,
      'State is required'
    );
    
    isValid &= validateField(
      document.getElementById('customerPhone'),
      document.getElementById('customer_phone_error'),
      (v) => phonePattern.test(v),
      'Phone must be a valid 10-digit number'
    );
    
    const emailField = document.getElementById('customerEmail');
    const emailValue = emailField.value.trim();
    if (emailValue.length > 0) {
      isValid &= validateField(
        emailField,
        document.getElementById('customer_email_error'),
        (v) => emailPattern.test(v),
        'Invalid email format'
      );
    }
    
    if (!isValid) return;
    
    $.ajax({
      url: 'add_customer.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.status === 'success') {
          alert('Customer added successfully!');
          $('#customerForm')[0].reset();
          addCustomerModal.hide();
          loadCustomers();
          setTimeout(() => $('#customerSelect').val(res.id), 100);
        } else {
          alert('Error: ' + (res.msg || 'Unknown error'));
        }
      },
      error: function() {
        alert('Failed to add customer.');
      }
    });
  });

  $('#addRowBtn').click(function() {
    let row = `
      <tr>
        <td>
          <select class="form-select productSelect" name="product_id[]" required>
            <option value="">Select Product</option>
            ${products.map(p => `<option value="${p.id}" data-price="${p.selling_price}" data-stock="${p.stock}">${p.name}</option>`).join('')}
          </select>
        </td>
        <td class="availableStock text-center"><span class="badge bg-secondary">-</span></td>
        <td><input type="number" name="quantity[]" class="form-control qtyInput" min="1" value="1" required></td>
        <td><input type="number" name="unit_price[]" class="form-control priceInput" min="0" step="0.01" required></td>
        <td class="subtotal">₹0.00</td>
        <td><button type="button" class="btn btn-sm btn-danger removeRow"><i class="fas fa-times"></i></button></td>
      </tr>`;
    $('#saleItemsTable tbody').append(row);
  });

  $(document).on('change', '.productSelect', function() {
    const $row = $(this).closest('tr');
    const price = $(this).find(':selected').data('price') || 0;
    const stock = $(this).find(':selected').data('stock') || 0;
    
    $row.find('.priceInput').val(price);
    
    // Update available stock display
    if (stock > 0) {
      if (stock <= 20) {
        $row.find('.availableStock').html(`<span class="badge bg-warning text-dark">${stock} left</span>`);
      } else {
        $row.find('.availableStock').html(`<span class="badge bg-success">${stock} available</span>`);
      }
    } else {
      $row.find('.availableStock').html(`<span class="badge bg-danger">Out of stock</span>`);
    }
    
    // Set max quantity
    $row.find('.qtyInput').attr('max', stock);
    
    updateTotals();
  });

  $(document).on('input', '.qtyInput', function() {
    const $row = $(this).closest('tr');
    const max = parseInt($(this).attr('max')) || 0;
    const val = parseInt($(this).val()) || 0;
    
    if (val > max) {
      alert(`Only ${max} units available in stock!`);
      $(this).val(max);
    }
    
    updateTotals();
  });

  $(document).on('input', '.priceInput', updateTotals);

  $(document).on('click', '.removeRow', function() {
    $(this).closest('tr').remove();
    updateTotals();
  });

  function updateTotals() {
    let grandTotal = 0;
    let itemCount = 0;
    let totalQty = 0;
    
    $('#saleItemsTable tbody tr').each(function() {
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

  $('#saleForm').submit(function(e) {
    e.preventDefault();
    
    if ($('#saleItemsTable tbody tr').length === 0) {
      alert('Please add at least one product to the sale.');
      return;
    }
    
    // Check stock availability for all items
    let stockError = false;
    $('#saleItemsTable tbody tr').each(function() {
      const $row = $(this);
      const productName = $row.find('.productSelect option:selected').text();
      const qty = parseInt($row.find('.qtyInput').val()) || 0;
      const max = parseInt($row.find('.qtyInput').attr('max')) || 0;
      
      if (qty > max) {
        alert(`Insufficient stock for ${productName}. Only ${max} units available.`);
        stockError = true;
        return false;
      }
    });
    
    if (stockError) return;
    
    $.ajax({
      url: 'add_sale.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(res) {
        if (res.trim() === 'success') {
          alert('Sale recorded successfully!');
          
          try {
            window.parent.postMessage('refreshDashboard', '*');
          } catch(e) {}
          
          location.reload();
        } else {
          alert('Error: ' + res);
        }
      },
      error: function() {
        alert('Failed to save sale.');
      }
    });
  });
  </script>

</body>
</html>