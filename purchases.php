<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';

// Add Indian currency formatting function (Same as sales.php)
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Purchases Management - EasyInventory</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <script src="https://unpkg.com/feather-icons"></script>
  
  <style>
    /* --- 1. Global Reset & Body (from try.html) --- */
    :root {
        --main-bg: #F9FAFB;
        --card-bg: #FFFFFF;
        --border-color: #E5E7EB;
        --text-primary: #1F2937;
        --text-secondary: #6B7280;
        --brand-blue: #3B82F6;
        --brand-green: #10B981;
        --brand-red: #EF4444;
        --brand-gray: #F3F4F6;
    }
    
    * { box-sizing: border-box; }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--main-bg);
        color: var(--text-primary);
        margin: 0;
        padding: 0;
    }

    /* --- 3. Main Content Area (from try.html) --- */
    .main-content {
        background-color: var(--main-bg);
        padding: 2rem;
    }

    .main-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .main-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .btn-primary {
        background-color: var(--brand-green);
        color: white;
        border: none;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s ease;
    }
    
    .btn-primary:hover {
        background-color: #059669;
    }

    /* --- 4. KPI Cards Section (from try.html) --- */
    .kpi-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .kpi-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }
    
    .kpi-card .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .kpi-card .card-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .kpi-card .card-icon {
        color: var(--text-secondary);
    }

    .kpi-card .card-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .kpi-card .card-comparison {
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .kpi-card .card-comparison .positive { color: var(--brand-green); font-weight: 600; }
    .kpi-card .card-comparison .negative { color: var(--brand-red); font-weight: 600; }
    .kpi-card .card-comparison .neutral { color: var(--text-secondary); }

    /* --- 5. Main Chart Section (MODIFIED) --- */
    .main-chart {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    
    .main-chart .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .main-chart .chart-header h2 {
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0;
    }

    /* 'Peak Day' span was here, now removed */

    .chart-container {
        height: 250px;
        display: flex;
        align-items: flex-end;
        gap: 0.5%; /* MODIFIED: Reduced gap for wider bars */
        padding: 0;
        border-bottom: 2px solid var(--border-color);
    }
    
    .chart-bar {
        width: 100%; /* Each bar will take equal width */
        background-color: var(--brand-blue);
        border-radius: 3px 3px 0 0;
        opacity: 0.8;
        transition: opacity 0.2s ease;
        min-height: 2px; /* Show a line even for 0 */
    }
    
    /* MODIFIED: Removed .zero-value class styles */
    
    .chart-bar:hover { 
        opacity: 1; 
        background-color: #2563EB; /* Darker blue on hover */
    }

    .chart-labels {
        display: flex;
        justify-content: space-between;
        padding: 0 0.5rem;
        margin-top: 8px;
    }

    .date-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .chart-container .no-data {
        width: 100%;
        text-align: center;
        color: var(--text-secondary);
        padding-top: 90px;
        font-size: 0.875rem;
    }

    /* --- 6. Transactions Table Section (from try.html) --- */
    .transactions-section {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    .table-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .table-header h2 {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .table-filters {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .search-bar {
        flex-grow: 1;
        position: relative;
    }
    
    .search-bar .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        width: 18px;
        height: 18px;
    }
    
    .table-filters input[type="text"],
    .table-filters input[type="date"],
    .table-filters select {
        width: 100%;
        padding: 0.65rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
        background-color: white;
    }
    
    .table-filters input[type="text"] {
        padding-left: 2.5rem;
        min-width: 250px;
    }
    
    .table-filters input[type="date"],
    .table-filters select {
        min-width: 150px;
        width: auto;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
    }

    .table-hover tbody tr.transaction-row:hover {
        background-color: var(--main-bg);
    }

    .transactions-table {
        width: 100%;
        border-collapse: collapse;
    }

    .transactions-table th,
    .transactions-table td {
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.875rem;
        white-space: nowrap;
        vertical-align: middle;
    }

    .transactions-table th {
        background-color: var(--main-bg);
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    
    .transactions-table tbody tr.details-row td {
        border-bottom: 1px solid var(--border-color);
    }
    
    .supplier-name {
        font-weight: 500;
        color: var(--text-primary);
    }

    /* --- Status Pills (from try.html) --- */
    .status {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-weight: 500;
    }
    .status .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    
    .status-completed {
        background-color: #ECFDF5;
        color: #065F46;
    }
    .status-completed .status-dot { background-color: #10B981; }
    
    /* --- Badges (from try.html) --- */
    .badge-clean {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .badge-gray { background-color: var(--brand-gray); color: var(--text-secondary); }


    .table-actions {
        display: flex;
        gap: 0.75rem;
    }
    
    .action-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-secondary);
        padding: 0.25rem;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .action-btn:hover {
        color: var(--text-primary);
        background-color: var(--border-color);
    }

    .action-btn-delete:hover {
        color: var(--brand-red);
        background-color: #FEF2F2;
    }

    /* --- Expandable Row Functionality (from try.html) --- */
    .transaction-row {
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .expand-icon {
      transition: transform 0.3s;
      width: 18px;
      height: 18px;
      color: var(--text-secondary);
    }

    .expanded .expand-icon {
      transform: rotate(90deg);
    }
    
    .details-row {
      display: none;
      background-color: #FDFDFD;
    }
    
    .details-row td {
      padding: 0;
      border-bottom: 1px solid var(--border-color);
    }
    
    .details-content {
      padding: 1.5rem 2.5rem;
    }

    .details-table {
      margin: 1rem 0 0 0;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid var(--border-color);
    }
    .details-table th { background-color: var(--main-bg); }
    .details-table th, .details-table td {
        padding: 0.75rem 1rem;
    }

    /* --- Empty State (from try.html & purchases.php) --- */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }

    .empty-state .empty-icon {
      font-size: 4rem;
      color: #D1D5DB;
      margin-bottom: 20px;
      width: 60px;
      height: 60px;
    }

    .empty-state h5 {
      color: var(--text-secondary);
      margin-bottom: 10px;
    }

    .empty-state p {
      color: #9CA3AF;
    }

    /* --- Modal Re-skin (from try.html) --- */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        font-family: 'Inter', sans-serif;
    }
    .modal-header {
        background-color: var(--main-bg);
        border-bottom: 1px solid var(--border-color);
        padding: 1.25rem 1.5rem;
    }
    .modal-title {
        font-weight: 600;
        font-size: 1.125rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .modal-body {
        padding: 1.5rem;
    }
    .modal-footer {
        background-color: var(--main-bg);
        border-top: 1px solid var(--border-color);
        padding: 1rem 1.5rem;
    }

    /* Form & Input Re-skin (from try.html) */
    .form-label {
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid var(--border-color);
        padding: 0.65rem 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--brand-blue);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        outline: none;
    }
    
    .input-group .btn {
        border-radius: 0 8px 8px 0;
    }
    .input-group .form-select {
        border-radius: 8px 0 0 8px;
    }
    
    .card {
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }
    .card-header {
        background-color: var(--main-bg);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        border-color: #e5e7eb;
        color: #374151;
        font-weight: 600;
    }
    .btn-secondary:hover {
        background-color: #d1d5db;
        border-color: #d1d5db;
    }
    .btn-success {
        background-color: var(--brand-green);
        border-color: var(--brand-green);
        font-weight: 600;
    }
    .btn-success:hover {
        background-color: #059669;
        border-color: #059669;
    }
    .btn-danger { font-weight: 500; }
    
    /* Make Bootstrap icons align with text */
    .btn [data-feather] {
        width: 16px;
        height: 16px;
        margin-top: -2px;
        margin-right: 4px;
    }

    /* --- Styles from purchases.php (for modals) --- */
    .error-text {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: none;
    }

    .is-invalid {
      border-color: #dc3545 !important;
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
  </style>
</head>
<body>

  <main class="main-content">
  
    <header class="main-header">
      <div>
        <h1>Purchases Management</h1>
        <p class="text-muted mb-0">Track and manage all your purchase orders</p>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
        <i data-feather="plus" style="width:18px; height:18px;"></i>
        New Purchase
      </button>
    </header>

    <?php
    // Get purchase statistics
    $stats_stmt = $conn->prepare("
      SELECT 
        COALESCE(SUM(CASE WHEN purchase_date >= CURDATE() - INTERVAL 30 DAY THEN total_amount ELSE 0 END), 0) as total_spent_month,
        COALESCE(SUM(CASE WHEN purchase_date = CURDATE() THEN total_amount ELSE 0 END), 0) as total_spent_today,
        COUNT(CASE WHEN purchase_date = CURDATE() THEN 1 END) as orders_today,
        COUNT(CASE WHEN purchase_date >= CURDATE() - INTERVAL 30 DAY THEN 1 END) as purchases_this_month
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

    // --- NEW: Get data for the chart (for all 30 days) ---
    $chart_data_map = [];
    $max_spending = 0;

    // 1. Create an array for the last 30 days with 0 totals
    $start_date = new DateTime('-29 days');
    $end_date = new DateTime();
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start_date, $interval, $end_date->modify('+1 day')); // Include today

    foreach ($period as $date) {
        $chart_data_map[$date->format('Y-m-d')] = [
            'total' => 0,
            'label' => $date->format('M d')
        ];
    }

    // 2. Fetch data from DB
    $chart_stmt = $conn->prepare("
        SELECT 
            purchase_date, 
            SUM(total_amount) as daily_total
        FROM purchases
        WHERE admin_id = ? 
          AND purchase_date >= CURDATE() - INTERVAL 29 DAY
        GROUP BY purchase_date
    ");
    $chart_stmt->bind_param('i', $admin_id);
    $chart_stmt->execute();
    $chart_res = $chart_stmt->get_result();
    
    // 3. Populate the array with real data
    while ($row = $chart_res->fetch_assoc()) {
        if (isset($chart_data_map[$row['purchase_date']])) {
            $chart_data_map[$row['purchase_date']]['total'] = $row['daily_total'];
        }
    }
    $chart_stmt->close();
    
    // 4. Find max spending for scaling the bars
    $max_spending = max(array_column($chart_data_map, 'total'));
    if ($max_spending == 0) $max_spending = 1; // Avoid division by zero if no sales
    ?>

    <section class="kpi-cards">
        
        <div class="kpi-card">
            <div class="card-header">
                <span class="card-title">Total Spent (This Month)</span>
                <i data-feather="trending-up" class="card-icon"></i>
            </div>
            <div class="card-value"><?= formatIndianCurrency($stats['total_spent_month']) ?></div>
            <div class="card-comparison">
                <span class="neutral">
                   In the last 30 days
                </span>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="card-header">
                <span class="card-title">Today's Purchases (Value)</span>
                <i data-feather="dollar-sign" class="card-icon"></i>
            </div>
            <div class="card-value"><?= formatIndianCurrency($stats['total_spent_today']) ?></div>
            <div class="card-comparison">
                <span class="positive">
                    <i data-feather="check" style="width:16px; height:16px;"></i>
                    <?= formatIndianCurrency($stats['orders_today']) ?> Orders
                </span>
                <span class="neutral" style="margin-left: 4px;">recorded today</span>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="card-header">
                <span class="card-title">Purchases (This Month)</span>
                <i data-feather="shopping-bag" class="card-icon"></i>
            </div>
            <div class="card-value"><?= number_format($stats['purchases_this_month']) ?></div>
            <div class="card-comparison">
                <span class="neutral">Total POs in last 30 days</span>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="card-header">
                <span class="card-title">Active Suppliers</span>
                <i data-feather="truck" class="card-icon"></i>
            </div>
            <div class="card-value"><?= number_format($suppliers_count) ?></div>
            <div class="card-comparison">
                <span class="neutral">Total unique suppliers</span>
            </div>
        </div>

    </section>

    <section class="main-chart">
        <div class="chart-header">
            <h2>Spending Overview (Last 30 Days)</h2>
            </div>
        <div class="chart-container">
            <?php if (empty($chart_data_map)): ?>
                <p class="no-data">
                    <i data-feather="bar-chart-2" style="width:40px; height: 40px; margin-bottom: 10px;"></i><br>
                    No purchase data available for the last 30 days.
                </p>
            <?php else: ?>
                <?php foreach ($chart_data_map as $data): ?>
                    <?php
                        $height_percent = ($max_spending > 0) ? ($data['total'] / $max_spending) * 100 : 0;
                        $height_percent = max(0, $height_percent); 
                        // MODIFIED: $bar_class removed
                        $date_label = $data['label'];
                        $currency_value = formatIndianCurrency($data['total']);
                    ?>
                    <div class="chart-bar" 
                         style="height: <?= $height_percent ?>%;" 
                         title="<?= $date_label ?>: <?= $currency_value ?>">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="chart-labels">
            <?php
                $labels = array_values($chart_data_map);
            ?>
            <span class="date-label"><?= $labels[0]['label'] ?> (Start)</span>
            <span class="date-label"><?= $labels[count($labels) - 1]['label'] ?> (Today)</span>
        </div>
    </section>

    <section class="transactions-section">
        <div class="table-header">
          <h2>Purchase Orders</h2>
          
          <div class="table-filters">
              <div class="search-bar">
                  <i data-feather="search" class="search-icon"></i>
                  <input type="text" id="searchInput" placeholder="Search by ID or supplier..." onkeyup="filterTable()">
              </div>
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
                  // Use supplier ID for value, as in purchases.php
                  echo '<option value="'.$sup['id'].'">'.htmlspecialchars($sup['name']).'</option>';
                }
                $sup_stmt->close();
                ?>
              </select>
              <input type="date" id="dateFromFilter" title="Date From" onchange="filterTable()">
              <input type="date" id="dateToFilter" title="Date To" onchange="filterTable()">
          </div>
        </div>

        <div class="table-container">
          <table class="table table-hover align-middle transactions-table" id="purchasesTable">
            <thead>
              <tr>
                <th style="width: 50px;"></th>
                <th style="width: 80px;">ID</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Items</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th style="width: 80px;">Actions</th> 
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
              <tr class="transaction-row" onclick="toggleDetails(<?= $purchase_id ?>)"
                  data-supplier="<?= $row['supplier_id'] ?>"
                  data-date="<?= $row['purchase_date'] ?>">
                <td class="text-center">
                  <i data-feather="chevron-right" class="expand-icon" id="icon-<?= $purchase_id ?>"></i>
                </td>
                <td><strong>#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= date('M d, Y', strtotime($row['purchase_date'])) ?></td>
                <td class="supplier-name"><?= htmlspecialchars($row['supplier_name'] ?? 'N/A') ?></td>
                <td><span class="badge-clean badge-gray"><?= $row['item_count'] ?> Item<?= $row['item_count'] > 1 ? 's' : '' ?></span></td>
                <td><strong><?= formatIndianCurrency($row['total_amount']) ?></strong></td>
                <td>
                  <span class="status status-completed">
                    <span class="status-dot"></span>Completed
                  </span>
                </td>
                <td class="table-actions">
                  <button class="action-btn action-btn-delete" title="Delete PO" 
                          onclick="event.stopPropagation(); deletePurchase(<?= $purchase_id ?>)">
                    <i data-feather="trash-2"></i>
                  </button>
                </td>
              </tr>
              <tr class="details-row" id="details-<?= $purchase_id ?>">
                <td colspan="8">
                  <div class="details-content">
                    <h6 class="mb-0"><i data-feather="package" style="width:18px; margin-top:-2px; margin-right: 5px;"></i> Ordered Items</h6>
                    <table class="table table-sm details-table">
                      <thead>
                        <tr>
                          <th>Product</th>
                          <th>Quantity</th>
                          <th>Unit Cost</th>
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
                          <td><?= formatIndianCurrency($item['unit_price']) ?></td>
                          <td><strong><?= formatIndianCurrency($item['subtotal']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-light">
                          <td colspan="3" class="text-end"><strong>Total:</strong></td>
                          <td><strong><?= formatIndianCurrency($row['total_amount']) ?></strong></td>
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
                    <i data-feather="inbox" class="empty-icon"></i>
                    <h5>No Purchases Yet</h5>
                    <p>Start by creating your first purchase order</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
                      <i data-feather="plus"></i>Create Purchase Order
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
    </section>
  </main>
  
  <div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form id="purchaseForm">
          <div class="modal-header">
            <h5 class="modal-title"><i data-feather="shopping-bag"></i>New Purchase Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="supplierSelect" class="form-label">Select Supplier <span class="text-danger">*</span></label>
                <div class="input-group">
                  <select id="supplierSelect" name="supplier_id" class="form-select" required></select>
                  <button type="button" class="btn btn-outline-secondary" id="addSupplierBtn" title="Add New Supplier">
                    <i data-feather="plus" style="margin-right:0;"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6">
                <label for="purchaseDate" class="form-label">Purchase Date</label>
                <input type="date" id="purchaseDate" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>">
              </div>
            </div>

            <div class="card mb-3">
              <div class="card-header">
                <h6 class="mb-0"><i data-feather="package" style="width:16px; margin-top: -2px; margin-right: 4px;"></i>Order Items</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table" id="purchaseItemsTable">
                    <thead>
                      <tr>
                        <th style="width: 40%;">Product</th>
                        <th style="width: 15%;">Quantity</th>
                        <th style="width: 15%;">Cost Price</th>
                        <th style="width: 10%;">Subtotal</th>
                        <th style="width: 5%;"></th>
                      </tr>
                    </thead>
                    <tbody>
                        </tbody>
                  </table>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="addRowBtn">
                  <i data-feather="plus"></i> Add Product
                </button>
              </div>
            </div>

            <div class="row">
              <div class="col-md-8">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any additional notes for this purchase order..."></textarea>
              </div>
              <div class="col-md-4">
                <div class="card bg-light">
                  <div class="card-body">
                    <h6 class="card-title">Purchase Summary</h6>
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
              Save Purchase Order
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="supplierForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title"><i data-feather="truck"></i>Add New Supplier</h5>
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
            <button type="submit" class="btn btn-primary" style="background-color: var(--brand-blue); border-color: var(--brand-blue);">
              <i data-feather="save"></i>Save Supplier
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="productForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title"><i data-feather="package"></i>Add New Product</h5>
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
                  <i data-feather="plus" style="margin-right:0;"></i>
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
            <button type="submit" class="btn btn-primary" style="background-color: var(--brand-blue); border-color: var(--brand-blue);">
              <i data-feather="save"></i>Save Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="categoryForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title"><i data-feather="tag"></i>Add New Category</h5>
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
            <button type="submit" class="btn btn-primary" style="background-color: var(--brand-blue); border-color: var(--brand-blue);">
              <i data-feather="save"></i>Save Category
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
     function formatIndianCurrency(number) {
        const num = parseFloat(number);
        if (isNaN(num)) return '₹0.00';
        
        const parts = num.toFixed(2).split('.');
        let integerPart = parts[0];
        const decimalPart = parts[1];
        
        const isNegative = integerPart.startsWith('-');
        if (isNegative) {
            integerPart = integerPart.substring(1);
        }
        
        let lastThree = integerPart.substring(integerPart.length - 3);
        let otherNumbers = integerPart.substring(0, integerPart.length - 3);
        
        if (otherNumbers !== '') {
            lastThree = ',' + lastThree;
        }
        
        let result = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree;
        
        if (isNegative) {
            result = '-' + result;
        }
        
        return '₹' + result + '.' + decimalPart;
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

    // --- All other JavaScript from purchases.php ---
    let products = [];
    let addSupplierModal, addPurchaseModal, addProductModal, addCategoryModal;
    let currentProductRow = null;
    let reorderData = null;

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
          const detailsRowId = row.onclick?.toString().match(/\d+/)?.[0];
          if (detailsRowId) {
            const detailsRow = document.getElementById('details-' + detailsRowId);
            if (detailsRow) detailsRow.style.display = 'none';
          }
        }
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

    // Function to open purchase modal with pre-filled reorder data
    function openPurchaseModalWithReorder(data) {
      console.log('Opening purchase modal with reorder data:', data);
      
      if (addPurchaseModal) {
        addPurchaseModal.show();
      } else {
        $('#addPurchaseModal').modal('show');
      }
      
      if (data.supplier_id && data.supplier_id !== 'null') {
        setTimeout(function() {
          $('#supplierSelect').val(data.supplier_id);
        }, 100);
      }
      
      setTimeout(function() {
        $('#addRowBtn').click();
        
        setTimeout(function() {
          const $lastRow = $('#purchaseItemsTable tbody tr:last');
          $lastRow.find('.productSelect').val(data.product_id).trigger('change');
          $lastRow.find('.qtyInput').val(data.quantity || 10);
          $lastRow.find('.priceInput').val(data.unit_price);
          
          updateTotals();
          showNotification('Product added for reorder: ' + data.product_name, 'info');
        }, 200);
      }, 300);
    }

    // Helper function to show notifications
    function showNotification(message, type = 'info') {
      const alertClass = type === 'info' ? 'alert-info' : 
                         type === 'success' ? 'alert-success' : 
                         type === 'warning' ? 'alert-warning' : 'alert-danger';
      
      const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
          <i data-feather="info" style="width:18px; height:18px; margin-top:-2px; margin-right: 5px;"></i>${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      `);
      
      $('body').append(notification);
      feather.replace(); // Render the new icon
      
      setTimeout(function() {
        notification.alert('close');
      }, 5000);
    }

    $(document).ready(function() {
      console.log('Page loaded, initializing...');
      
      // Initialize modals
      addSupplierModal = new bootstrap.Modal(document.getElementById('addSupplierModal'));
      addPurchaseModal = new bootstrap.Modal(document.getElementById('addPurchaseModal'));
      addProductModal = new bootstrap.Modal(document.getElementById('addProductModal'));
      addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
      
      console.log('Modals initialized');
      
      // Check for reorder data from stock alerts
      const reorderDataStr = sessionStorage.getItem('reorder_data');
      
      if (reorderDataStr) {
        console.log('Reorder data found in session storage');
        try {
          reorderData = JSON.parse(reorderDataStr);
          sessionStorage.removeItem('reorder_data');
          
          setTimeout(function() {
            openPurchaseModalWithReorder(reorderData);
          }, 800);
          
        } catch (e) {
          console.error('Error parsing reorder data:', e);
        }
      }
      
      // This call will render all icons loaded by PHP and static HTML
      feather.replace();
    });

    $('#addPurchaseModal').on('show.bs.modal', function() {
      console.log('Purchase modal opening...');
      loadSuppliers();
      loadProducts();
      
      if (!reorderData) {
        $('#purchaseItemsTable tbody').empty();
        $('#supplierSelect').val('');
        $('#purchaseDate').val(new Date().toISOString().split('T')[0]);
        updateTotals();
      }
    });

    $('#addPurchaseModal').on('hidden.bs.modal', function() {
      console.log('Purchase modal closed, clearing reorder data');
      reorderData = null;
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
      isValid &= validateField(document.getElementById('supplierName'), document.getElementById('supplier_name_error'), (v) => v.length > 0, 'Name is required');
      isValid &= validateField(document.getElementById('supplierStreetAddress'), document.getElementById('supplier_street_error'), (v) => v.length > 0, 'Street address is required');
      isValid &= validateField(document.getElementById('supplierCity'), document.getElementById('supplier_city_error'), (v) => v.length > 0, 'City is required');
      isValid &= validateField(document.getElementById('supplierPinCode'), document.getElementById('supplier_pin_error'), (v) => pinPattern.test(v), 'Pin code must be exactly 6 digits');
      isValid &= validateField(document.getElementById('supplierState'), document.getElementById('supplier_state_error'), (v) => v.length > 0, 'State is required');
      isValid &= validateField(document.getElementById('supplierPhone'), document.getElementById('supplier_phone_error'), (v) => phonePattern.test(v), 'Phone must be a valid 10-digit number');
      
      const emailField = document.getElementById('supplierEmail');
      if (emailField.value.trim().length > 0) {
        isValid &= validateField(emailField, document.getElementById('supplier_email_error'), (v) => emailPattern.test(v), 'Invalid email format');
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

    // --- Purchase Items Management (MODIFIED for Feather Icons) ---
    $('#addRowBtn').click(function() {
      let row = `
        <tr>
          <td>
            <div class="input-group">
              <select class="form-select productSelect" name="product_id[]" required>
                <option value="">Select Product</option>
                ${products.map(p => `<option value="${p.id}" data-price="${p.cost_price}">${p.name}</option>`).join('')}
              </select>
              <button type="button" class="btn btn-outline-secondary btn-sm addNewProductBtn" title="Add New Product">
                <i data-feather="plus" style="width:16px; height:16px; margin:0;"></i>
              </button>
            </div>
          </td>
          <td><input type="number" name="quantity[]" class="form-control qtyInput" min="1" value="1" required></td>
          <td><input type="number" name="unit_price[]" class="form-control priceInput" min="0" step="0.01" required></td>
          <td class="subtotal" style="font-weight: 500;">₹0.00</td>
          <td>
            <button type="button" class="btn btn-sm btn-outline-danger removeRow">
                <i data-feather="x" style="width:16px; height:16px; margin:0;"></i>
            </button>
          </td>
        </tr>`;
      $('#purchaseItemsTable tbody').append(row);
      feather.replace(); // Re-run for new icons in this row
      updateTotals();
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
      // 
      $('#productStock').closest('.mb-3').hide();
      $('#productSupplier').closest('.mb-3').hide();
      $('#productStock').removeAttr('required');
      addProductModal.show();
    });

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
        // *** BUG FIX (removed extra 's') ***
        $(this).find('.subtotal').text(formatIndianCurrency(subtotal));
        grandTotal += subtotal;
        itemCount++;
        totalQty += qty;
      });
      
      $('#grandTotal').text(formatIndianCurrency(grandTotal).replace('₹', ''));
      $('#itemCount').text(itemCount);
      $('#totalQty').text(totalQty);
    }

    // --- PRODUCT FORM HANDLERS ---
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

    function calculateProductPricing() {
      const costPrice = parseFloat(document.getElementById('productCostPrice').value) || 0;
      const hasMrp = hasMrpCheckbox.checked;
      const mrp = hasMrp ? (parseFloat(document.getElementById('productMrp').value) || 0) : 0;
      const sellingPrice = parseFloat(document.getElementById('productSellingPrice').value) || 0;
      const profitMargin = parseFloat(document.getElementById('productProfitMargin').value) || 0;
      
      document.getElementById('displayCostPrice').textContent = formatIndianCurrency(costPrice);
      
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

    $('#productForm').submit(function(e) {
      e.preventDefault();
      
      const name = $('#productName').val().trim();
      const category_id = $('#productCategory').val();
      const cost_price = $('#productCostPrice').val().trim();
      const has_mrp = $('#hasMrpCheckbox').is(':checked') ? 1 : 0;
      const mrp = has_mrp ? $('#productMrp').val().trim() : '';
      const selling_price = $('#productSellingPrice').val().trim();
      const stock = $('#productStock').val().trim();

      if (!name || !category_id || !cost_price || !selling_price) {
        alert('Please fill in all required fields (Name, Category, Cost Price, Selling Price).');
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

    // --- PURCHASE FORM SUBMISSION ---
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