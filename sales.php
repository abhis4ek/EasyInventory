<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';
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
  <title>Sales Management - EasyInventory</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <script src="https://unpkg.com/feather-icons"></script>
  
  <style>
    /* --- 1. Global Reset & Body --- */
    :root {
        --sidebar-bg: #111827; /* Dark Blue/Gray */
        --main-bg: #F9FAFB;     /* Light Gray */
        --card-bg: #FFFFFF;
        --border-color: #E5E7EB;
        --text-primary: #1F2937;
        --text-secondary: #6B7280;
        --brand-blue: #3B82F6;
        --brand-green: #10B981;
        --brand-red: #EF4444;
        --brand-yellow: #F59E0B;
        --brand-gray: #F3F4F6;
    }
    
    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--main-bg);
        color: var(--text-primary);
        /* Removed grid layout properties */
        margin: 0;
        padding: 0;
    }

    /* --- 2. Sidebar Navigation (STYLES REMOVED AS SIDEBAR IS GONE) --- */
    /* .sidebar, .sidebar-header, .sidebar-nav, etc. removed */

    /* --- 3. Main Content Area --- */
    .main-content {
        background-color: var(--main-bg);
        /* Removed height and overflow, padding provides page spacing */
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

    /* --- 4. KPI Cards Section --- */
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

    .kpi-card .card-comparison .positive {
        color: var(--brand-green);
        font-weight: 600;
    }
    
    .kpi-card .card-comparison .negative {
        color: var(--brand-red);
        font-weight: 600;
    }
    
    .kpi-card .card-comparison .neutral {
        color: var(--text-secondary);
    }

    /* --- 5. Main Chart Section --- */
    .main-chart {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    
    .main-chart h2 {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    /* Static/Simulated Bar Chart */
    .chart-container {
        height: 250px;
        display: flex;
        align-items: flex-end;
        gap: 0.5%;
        padding: 0 1rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .chart-bar {
        width: 100%;
        background-color: var(--brand-blue);
        border-radius: 4px 4px 0 0;
        opacity: 0.8;
        transition: opacity 0.2s ease;
    }
    
    .chart-bar:hover {
        opacity: 1;
    }

    /* --- 6. Transactions Table Section --- */
    .transactions-section {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        overflow: hidden; /* For rounded corners on table */
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
        background-color: white; /* Ensure consistent background */
    }
    
    .table-filters input[type="text"] {
        padding-left: 2.5rem; /* Space for icon */
        min-width: 250px;
    }
    
    .table-filters input[type="date"],
    .table-filters select {
        min-width: 150px;
        width: auto;
    }

    .table-container {
        width: 100%;
        overflow-x: auto; /* For responsive on small screens */
    }

    /* Style for your existing .table-hover */
    .table-hover tbody tr.sale-row:hover {
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
    
    .customer-name {
        font-weight: 500;
        color: var(--text-primary);
    }

    /* --- Status Pills (Re-style of your .status-badge) --- */
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
    
    .status-paid {
        background-color: #ECFDF5; /* Green tint */
        color: #065F46; /* Dark Green */
    }
    .status-paid .status-dot { background-color: #10B981; }
    
    /* --- Badges (Re-style of your .customer-badge etc.) --- */
    .badge-clean {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .badge-blue { background-color: #EFF6FF; color: #1D4ED8; }
    .badge-gray { background-color: var(--brand-gray); color: var(--text-secondary); }
    .badge-yellow { background-color: #FFFBEB; color: #B45309; }


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

    /* --- Your Custom Functionality (Re-skinned) --- */
    .sale-row {
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

    /* --- Modal Re-skin --- */
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

    /* Form & Input Re-skin */
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
    .btn-danger {
        font-weight: 500;
    }
    
    /* Your validation error style */
    .error-text {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: none;
    }

    .is-invalid {
      border-color: #dc3545 !important;
    }
    .is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
    }
    
    /* Make Bootstrap icons align with text */
    .btn [data-feather] {
        width: 16px;
        height: 16px;
        margin-top: -2px;
        margin-right: 4px;
    }
  </style>
</head>
<body>

    <main class="main-content">
  
    <header class="main-header">
      <div>
        <h1>Sales Management</h1>
        <p class="text-muted mb-0">Record and track all your sales transactions</p>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaleModal">
        <i data-feather="plus" style="width:18px; height:18px;"></i>
        New Sale
      </button>
    </header>

    <?php
    // NEW, CONSOLIDATED QUERIES FOR THE 4-CARD DASHBOARD
    
    // Query 1: Stats for This Month (Last 30 Days)
    $month_stmt = $conn->prepare("
      SELECT 
        COALESCE(SUM(total_amount), 0) as revenue_this_month,
        COUNT(*) as sales_this_month,
        COUNT(DISTINCT customer_id) as new_customers_this_month
      FROM sales 
      WHERE admin_id = ? AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $month_stmt->bind_param('i', $admin_id);
    $month_stmt->execute();
    $month_stats = $month_stmt->get_result()->fetch_assoc();
    $month_stmt->close();

    // Query 2: Today's Sales
    $today_stmt = $conn->prepare("
      SELECT COUNT(*) as today_sales, COALESCE(SUM(total_amount), 0) as today_revenue
      FROM sales 
      WHERE admin_id = ? AND sale_date = CURDATE()
    ");
    $today_stmt->bind_param('i', $admin_id);
    $today_stmt->execute();
    $today = $today_stmt->get_result()->fetch_assoc();
    $today_stmt->close();

    // Note: 'Total Outstanding' is not calculated as it requires a 'status' column 
    // in your 'sales' table, which is a functionality change.
    // We are using "Sales (This Month)" and "Active Customers" from your original logic.
    
    // Query 3: Get unique customers count (from your original logic)
    $customers_stmt = $conn->prepare("
        SELECT COUNT(DISTINCT customer_id) as unique_customers 
        FROM sales 
        WHERE admin_id = ? AND customer_id IS NOT NULL
    ");
    $customers_stmt->bind_param('i', $admin_id);
    $customers_stmt->execute();
    $customers_count = $customers_stmt->get_result()->fetch_assoc()['unique_customers'];
    $customers_stmt->close();

    // --- Sales Overview Chart Data (Last 30 Days) ---
    $chart_data = [];
    $max_revenue = 0;
    
    // Generate dates for the last 30 days
    $dates = [];
    for ($i = 29; $i >= 0; $i--) {
        $dates[] = date('Y-m-d', strtotime("-$i days"));
    }

    // Query daily sales revenue
    $chart_stmt = $conn->prepare("
        SELECT 
            DATE(sale_date) as sale_day, 
            COALESCE(SUM(total_amount), 0) as daily_revenue
        FROM sales 
        WHERE admin_id = ? AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY sale_day
    ");
    $chart_stmt->bind_param('i', $admin_id);
    $chart_stmt->execute();
    $chart_res = $chart_stmt->get_result();
    
    $daily_sales = [];
    while($row = $chart_res->fetch_assoc()) {
        $daily_sales[$row['sale_day']] = $row['daily_revenue'];
    }
    $chart_stmt->close();

    // Consolidate data for all 30 days, filling in 0 for days with no sales and finding max
    foreach ($dates as $date) {
        $revenue = $daily_sales[$date] ?? 0;
        $chart_data[$date] = $revenue;
        if ($revenue > $max_revenue) {
            $max_revenue = $revenue;
        }
    }
    // Set a minimum max_revenue to avoid division by zero if all sales are 0
    if ($max_revenue == 0) $max_revenue = 1;
    ?>

    <section class="kpi-cards">
        
        <div class="kpi-card">
            <div class="card-header">
                <span class="card-title">Total Revenue (This Month)</span>
                <i data-feather="credit-card" class="card-icon"></i>
            </div>
            <div class="card-value"><?= formatIndianCurrency($month_stats['revenue_this_month']) ?></div>
            <div class="card-comparison">
                <span class="neutral">Last 30 days</span>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="card-header">
                <span class="card-title">Today's Sales</span>
                <i data-feather="calendar" class="card-icon"></i>
            </div>
            <div class="card-value"><?= formatIndianCurrency($today['today_revenue']) ?></div>
            <div class="card-comparison">
                <span class="neutral"><?= $today['today_sales'] ?> Transactions Today</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="card-header">
                <span class="card-title">Sales (This Month)</span>
                <i data-feather="shopping-cart" class="card-icon"></i>
            </div>
            <div class="card-value"><?= number_format($month_stats['sales_this_month']) ?></div>
            <div class="card-comparison">
                <span class="neutral">Total sales in last 30 days</span>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="card-header">
                <span class="card-title">Active Customers</span>
                <i data-feather="user-check" class="card-icon"></i>
            </div>
            <div class="card-value"><?= number_format($customers_count) ?></div>
            <div class="card-comparison">
                <span class="neutral">Total unique customers</span>
            </div>
        </div>

    </section>

    <section class="main-chart">
        <h2>Sales Overview (Last 30 Days)</h2>
        <div class="chart-container">
            <?php foreach ($chart_data as $date => $revenue): 
                $height_percent = ($revenue / $max_revenue) * 100;
                // Ensure a minimum visibility for bars with revenue > 0
                if ($revenue > 0 && $height_percent < 1) $height_percent = 1;
                // Ensure 0 revenue days show a very small bar (for date visibility)
                if ($revenue == 0) $height_percent = 0.5;
            ?>
            <div class="chart-bar" 
                 style="height: <?= round($height_percent, 1) ?>%;" 
                 title="<?= date('M d, Y', strtotime($date)) ?>: ₹<?= number_format($revenue, 2) ?>">
            </div>
            <?php endforeach; ?>
        </div>
        <div class="d-flex justify-content-between text-secondary mt-2 px-3" style="font-size: 0.75rem;">
            <span><?= date('M d', strtotime(array_key_first($chart_data))) ?></span>
            <span><?= date('M d', strtotime(array_key_last($chart_data))) ?> (Today)</span>
        </div>
    </section>

    <section class="transactions-section">
        <div class="table-header">
          <h2>Sales Transactions</h2>
          
          <div class="table-filters">
              <div class="search-bar">
                  <i data-feather="search" class="search-icon"></i>
                  <input type="text" id="searchInput" placeholder="Search by ID or customer..." onkeyup="filterTable()">
              </div>
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
              <input type="date" id="dateFromFilter" onchange="filterTable()" title="Date From">
              <input type="date" id="dateToFilter" onchange="filterTable()" title="Date To">
          </div>
        </div>

        <div class="table-container">
          <table class="table table-hover align-middle transactions-table" id="salesTable">
            <thead>
              <tr>
                <th style="width: 50px;"></th>
                <th style="width: 80px;">ID</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th style="width: 120px;">Actions</th>
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
                  <i data-feather="chevron-right" class="expand-icon" id="icon-<?= $sale_id ?>"></i>
                </td>
                <td><strong>#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= date('M d, Y', strtotime($row['sale_date'])) ?></td>
                <td>
                  <?php if($is_walk_in): ?>
                    <span class="badge-clean badge-yellow"><?= htmlspecialchars($customer_name) ?></span>
                  <?php else: ?>
                    <span class="badge-clean badge-blue"><?= htmlspecialchars($customer_name) ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge-clean badge-gray"><?= $row['item_count'] ?> items</span>
                </td>
                <td><strong>₹<?= number_format($row['total_amount'], 2) ?></strong></td>
                <td>
                  <span class="status status-paid">
                    <span class="status-dot"></span>Paid
                  </span>
                </td>
                <td class="table-actions">
                  <button class="action-btn" 
                          onclick="event.stopPropagation(); window.open('sale_invoice.php?id=<?= $sale_id ?>', '_blank')"
                          title="View Invoice">
                    <i data-feather="download"></i>
                  </button>
                  <button class="action-btn action-btn-delete" 
                          onclick="event.stopPropagation(); deleteSale(<?= $sale_id ?>)"
                          title="Delete Sale">
                    <i data-feather="trash-2"></i>
                  </button>
                </td>
              </tr>
              <tr class="details-row" id="details-<?= $sale_id ?>">
                <td colspan="8">
                  <div class="details-content">
                    <h6 class="mb-0"><i data-feather="package" style="width:18px; margin-top:-2px;"></i> Sold Items</h6>
                    <table class="table table-sm details-table">
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
                              <span class="badge-clean badge-yellow ms-2">MRP</span>
                            <?php endif; ?>
                          </td>
                          <td><strong><?= $item['quantity'] ?></strong></td>
                          <td>₹<?= number_format($item['unit_price'], 2) ?></td>
                          <td><strong>₹<?= number_format($item['subtotal'], 2) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-light">
                          <td colspan="3" class="text-end"><strong>Total:</strong></td>
                          <td><strong>₹<?= number_format($row['total_amount'], 2) ?></strong></td>
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
                    <i data-feather="shopping-cart" class="empty-icon"></i>
                    <h5>No Sales Yet</h5>
                    <p>Start recording your first sale transaction</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                      <i data-feather="plus"></i>Record Sale
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
  <div class="modal fade" id="addSaleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form id="saleForm">
          <div class="modal-header">
            <h5 class="modal-title"><i data-feather="shopping-cart"></i>Record New Sale</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="customerSelect" class="form-label">Select Customer</label>
                <div class="input-group">
                  <select id="customerSelect" name="customer_id" class="form-select"></select>
                  <button type="button" class="btn btn-outline-secondary" id="addCustomerBtn">
                    <i data-feather="plus" style="margin-right:0;"></i>
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
              <div class="card-header">
                <h6 class="mb-0"><i data-feather="package" style="width:16px; margin-top: -2px; margin-right: 4px;"></i>Sale Items</h6>
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
                  <i data-feather="plus"></i> Add Product
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
              Complete Sale
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="customerForm" novalidate>
          <div class="modal-header">
            <h5 class="modal-title"><i data-feather="user-plus"></i>Add New Customer</h5>
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
            <button type="submit" class="btn btn-primary" style="background-color: var(--brand-blue); border-color: var(--brand-blue);">
              <i data-feather="save"></i>Save Customer
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
        if (detailsRow) {
            detailsRow.style.display = 'none';
            // Also reset the expand icon
            const icon = document.getElementById('icon-' + row.onclick?.toString().match(/\d+/)?.[0]);
            if (icon) icon.closest('tr').classList.remove('expanded');
        }
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
    
    // Activate Feather Icons on initial load
    feather.replace();
  });

  $('#addSaleModal').on('show.bs.modal', function() {
    loadCustomers();

    if (products.length === 0) {
      $.getJSON('get_products.php', function(data) {
        products = data;
      });
    }
    // Reset form
    $('#saleForm')[0].reset();
    $('#saleItemsTable tbody').empty();
    updateTotals();
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
        <td class="availableStock text-center"><span class="badge-clean badge-gray">-</span></td>
        <td><input type="number" name="quantity[]" class="form-control qtyInput" min="1" value="1" required></td>
        <td><input type="number" name="unit_price[]" class="form-control priceInput" min="0" step="0.01" required></td>
        <td class="subtotal" style="font-weight: 500;">₹0.00</td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger removeRow">
            <i data-feather="x" style="width:16px; height:16px; margin:0;"></i>
          </button>
        </td>
      </tr>`;
    $('#saleItemsTable tbody').append(row);
    // Re-run Feather Icons to render the new 'x' icon
    feather.replace();
  });

  $(document).on('change', '.productSelect', function() {
    const $row = $(this).closest('tr');
    const price = $(this).find(':selected').data('price') || 0;
    const stock = $(this).find(':selected').data('stock') || 0;
    
    $row.find('.priceInput').val(price);
    
    // Update available stock display
    if (stock > 0) {
      if (stock <= 20) {
        $row.find('.availableStock').html(`<span class="badge-clean badge-yellow">${stock} left</span>`);
      } else {
        $row.find('.availableStock').html(`<span class="badge-clean badge-clean" style="background-color: #ECFDF5; color: #065F46;">${stock} available</span>`);
      }
    } else {
      $row.find('.availableStock').html(`<span class="badge-clean" style="background-color: #FEF2F2; color: #991B1B;">Out of stock</span>`);
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
      $(this).find('.subtotal').text(formatIndianCurrency(subtotal));
      grandTotal += subtotal;
      itemCount++;
      totalQty += qty;
    });
    
    $('#grandTotal').text(formatIndianCurrency(grandTotal).replace('₹', ''));
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

  // Finally, run feather.replace one more time to catch any icons
  // that might have been missed (e.g., in empty state)
  feather.replace();
  </script>

</body>
</html>