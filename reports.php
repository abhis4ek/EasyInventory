<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';

// Indian Number Formatting Function
function formatIndianNumber($num) {
    $num = number_format($num, 2, '.', '');
    $parts = explode('.', $num);
    $integer = $parts[0];
    $decimal = isset($parts[1]) ? $parts[1] : '00';
    
    // Indian system: last 3 digits, then groups of 2
    $lastThree = substr($integer, -3);
    $otherNumbers = substr($integer, 0, -3);
    
    if ($otherNumbers != '') {
        $lastThree = ',' . $lastThree;
    }
    
    $result = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $otherNumbers) . $lastThree;
    
    return $result . '.' . $decimal;
}

$admin_id = $_SESSION['admin_id'];

// Get date range filter
$filter = $_GET['filter'] ?? 'month';
$date_from = '';
$date_to = date('Y-m-d');

switch($filter) {
    case 'month':
        $date_from = date('Y-m-d', strtotime('-1 month'));
        break;
    case 'six_months':
        $date_from = date('Y-m-d', strtotime('-6 months'));
        break;
    case 'year':
        $date_from = date('Y-m-d', strtotime('-1 year'));
        break;
    case 'all':
        $date_from = '2000-01-01';
        break;
}

// --- DATA FOR DASHBOARD CARDS ---

// Total Sales
$sql_sales = "SELECT COUNT(*) as total_transactions, COALESCE(SUM(total_amount), 0) as total_sales 
              FROM sales 
              WHERE admin_id = ? AND sale_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_sales);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$sales_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Total Purchases
$sql_purchases = "SELECT COUNT(*) as total_transactions, COALESCE(SUM(total_amount), 0) as total_purchases 
                  FROM purchases 
                  WHERE admin_id = ? AND purchase_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_purchases);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$purchases_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Card values
$total_sales = $sales_data['total_transactions'];
$total_sales_amount = $sales_data['total_sales'];
$total_purchases = $purchases_data['total_transactions'];
$total_purchases_amount = $purchases_data['total_purchases'];
$profit_loss = $total_sales_amount - $total_purchases_amount;

//  FIXED: Inventory value (not filtered by date)
$sql_inventory = "SELECT COALESCE(SUM(selling_price * stock), 0) AS inventory_value FROM products WHERE admin_id = ?";
$stmt = $conn->prepare($sql_inventory);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$inventory_value = $stmt->get_result()->fetch_assoc()['inventory_value'];
$stmt->close();

// --- DATA FOR CHARTS AND TABLES ---

// Monthly breakdown for chart
$monthly_sql = "
    SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        SUM(sales) as sales,
        SUM(purchases) as purchases,
        SUM(sales) - SUM(purchases) as profit
    FROM (
        SELECT sale_date as date, total_amount as sales, 0 as purchases FROM sales
        WHERE admin_id = ? AND sale_date BETWEEN ? AND ?
        UNION ALL
        SELECT purchase_date as date, 0 as sales, total_amount as purchases FROM purchases
        WHERE admin_id = ? AND purchase_date BETWEEN ? AND ?
    ) combined
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
";
$stmt = $conn->prepare($monthly_sql);
$stmt->bind_param('ississ', $admin_id, $date_from, $date_to, $admin_id, $date_from, $date_to);
$stmt->execute();
$monthly_data = $stmt->get_result();
$stmt->close();

// Top products
$all_products_sql = "
    SELECT p.name AS Product, SUM(si.quantity) as QuantitySold, SUM(si.subtotal) as TotalRevenue
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE s.admin_id = ? AND s.sale_date BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY TotalRevenue DESC
";
$stmt = $conn->prepare($all_products_sql);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$all_products_result = $stmt->get_result();
$all_products_data = $all_products_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recent sales
$recent_sales_sql = "
    SELECT s.id, s.sale_date, s.total_amount, c.name as customer_name
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.id
    WHERE s.admin_id = ? AND s.sale_date BETWEEN ? AND ?
    ORDER BY s.sale_date DESC
    LIMIT 10
";
$stmt = $conn->prepare($recent_sales_sql);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$recent_sales = $stmt->get_result();
$stmt->close();

// Recent purchases
$recent_purchases_sql = "
    SELECT p.id, p.purchase_date, p.total_amount, s.name as supplier_name
    FROM purchases p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.admin_id = ? AND p.purchase_date BETWEEN ? AND ?
    ORDER BY p.purchase_date DESC
    LIMIT 10
";
$stmt = $conn->prepare($recent_purchases_sql);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$recent_purchases = $stmt->get_result();
$stmt->close();

// ALL sales (for Excel export)
$export_sales_sql = "
    SELECT 
        s.id AS SaleID, 
        s.sale_date AS Date, 
        c.name as Customer, 
        s.total_amount AS Amount
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.id
    WHERE s.admin_id = ? AND s.sale_date BETWEEN ? AND ?
    ORDER BY s.sale_date DESC
";
$stmt = $conn->prepare($export_sales_sql);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$export_sales_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ALL purchases (for Excel export)
$export_purchases_sql = "
    SELECT 
        p.id AS PurchaseID, 
        p.purchase_date AS Date, 
        s.name as Supplier, 
        p.total_amount AS Amount
    FROM purchases p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.admin_id = ? AND p.purchase_date BETWEEN ? AND ?
    ORDER BY p.purchase_date DESC
";
$stmt = $conn->prepare($export_purchases_sql);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$export_purchases_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Easy Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }
    
    body {
        background-color: #f7faff;
        color: #2D3748;
        padding: 20px;
    }
    
    .container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .page-title {
        font-size: 2.2rem;
        color: #2D3748;
        font-weight: 700;
    }

    .page-subtitle {
        color: #718096;
        margin-top: 5px;
        font-weight: 400;
    }
    
    .filter-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 10px 20px;
        border: 2px solid #5A67D8;
        background: white;
        color: #5A67D8;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .filter-btn:hover, .filter-btn.active {
        background: #5A67D8;
        color: white;
        box-shadow: 0 4px 12px rgba(90, 103, 216, 0.2);
    }

    .export-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .export-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .export-btn.excel {
        background: #38A169;
        color: white;
    }

    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    
    .chart-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.07), 0 5px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
    }
    
    .section-title {
        font-size: 1.3rem;
        color: #2D3748;
        font-weight: 600;
        margin-bottom: 25px;
        border-left: 4px solid #5A67D8;
        padding-left: 15px;
    }
    
    .two-column {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    table th, table td {
        padding: 14px 12px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        color: #4A5568;
    }
    
    table th {
        background-color: #f8f9fc;
        font-weight: 600;
        color: #718096;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    table tbody tr:nth-child(even) {
        background-color: #fdfdff;
    }
    
    table tr:hover {
        background-color: #f0f5ff;
    }

    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.07), 0 5px 10px -5px rgba(0, 0, 0, 0.04);
        min-width: 0;
    }
    
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px -7px rgba(0, 0, 0, 0.1), 0 6px 15px -7px rgba(0, 0, 0, 0.05);
    }
    
    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-right: 20px;
        flex-shrink: 0;
    }
    
    .card-info {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }
    
    .card-info h3 {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 5px;
        line-height: 1.3;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    
    .card-info p {
        color: #4A5568;
        font-size: 0.9rem;
        font-weight: 500;
        line-height: 1.4;
    }
    
    .card-info small {
        display: block;
        color: #718096;
        font-size: 0.75rem;
        margin-top: 3px;
        line-height: 1.3;
    }
    
    .bg-info { background-color: #ebf4ff; color: #5A67D8; }
    .bg-success { background-color: #f0fff4; color: #38A169; }
    .bg-warning { background-color: #fffaf0; color: #DD6B20; }
    .bg-danger { background-color: #fff5f5; color: #E53E3E; }
    .bg-purple { background-color: #faf5ff; color: #805AD5; }
    .bg-teal { background-color: #e6fffa; color: #319795; }
    
    .profit-text { color: #38A169; }
    .loss-text { color: #E53E3E; }

    @media (max-width: 1200px) {
        .card-info h3 {
            font-size: 1.4rem;
        }
    }

    @media (max-width: 768px) {
        .two-column {
            grid-template-columns: 1fr;
        }
        .filter-buttons {
            flex-wrap: wrap;
        }
        .header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        .dashboard-cards {
            grid-template-columns: 1fr;
        }
        .card-info h3 {
            font-size: 1.5rem;
        }
        .card-info h3,
        .card-info p,
        .card-info small {
            white-space: normal;
            word-break: break-word;
        }
    }

    @media (max-width: 480px) {
        .card {
            padding: 15px;
        }
        .card-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        .card-info h3 {
            font-size: 1.3rem;
        }
    }

    @media print {
        .filter-buttons, .export-buttons {
            display: none !important;
        }
        body {
            padding: 0;
        }
        .chart-section, .card {
            box-shadow: none;
            border: 1px solid #ccc;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="page-title">Financial Reports</h1>
                <p class="page-subtitle">
                    Period: <?= date('M d, Y', strtotime($date_from)) ?> to <?= date('M d, Y', strtotime($date_to)) ?>
                </p>
            </div>
            <div class="filter-buttons">
                <a href="?filter=month" class="filter-btn <?= $filter === 'month' ? 'active' : '' ?>">1 Month</a>
                <a href="?filter=six_months" class="filter-btn <?= $filter === 'six_months' ? 'active' : '' ?>">6 Months</a>
                <a href="?filter=year" class="filter-btn <?= $filter === 'year' ? 'active' : '' ?>">1 Year</a>
                <a href="?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">All Time</a>
            </div>
        </div>

        <div class="export-buttons">
            <button class="export-btn excel" onclick="exportExcel()">
                <i class="fas fa-file-excel"></i> Export as Excel
            </button>
        </div>

        <div id="reportContent">
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon bg-info">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="card-info">
                        <h3>₹<?php echo formatIndianNumber($inventory_value); ?></h3>
                        <p>Inventory Retail Value</p>
                        <small>Total stock at retail price</small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon bg-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="card-info">
                        <h3>₹<?php echo formatIndianNumber($total_sales_amount); ?></h3>
                        <p>Total Sales</p>
                        <small><?php echo $total_sales; ?> transactions</small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon bg-purple">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="card-info">
                        <h3>₹<?php echo formatIndianNumber($total_purchases_amount); ?></h3>
                        <p>Total Purchases</p>
                        <small><?php echo $total_purchases; ?> transactions</small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon <?php echo $profit_loss >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card-info">
                        <h3 class="<?php echo $profit_loss >= 0 ? 'profit-text' : 'loss-text'; ?>">
                            ₹<?php echo formatIndianNumber(abs($profit_loss)); ?>
                        </h3>
                        <p><?php echo $profit_loss >= 0 ? 'Profit' : 'Loss'; ?></p>
                        <small>
                            <?php 
                            if($total_purchases_amount > 0) {
                                $margin = ($profit_loss / $total_purchases_amount) * 100;
                                echo number_format($margin, 2) . '% margin';
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icon bg-teal">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="card-info">
                        <h3>
                            <?php 
                            if($total_sales_amount > 0) {
                                $net_margin = ($profit_loss / $total_sales_amount) * 100;
                                echo number_format($net_margin, 2) . '%';
                            } else {
                                echo '0%';
                            }
                            ?>
                        </h3>
                        <p>Net Profit Margin</p>
                        <small>Revenue efficiency</small>
                    </div>
                </div>
            </div>
                
            <div class="two-column">
                <div class="chart-section">
                    <h3 class="section-title">Recent Sales</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_sales->num_rows > 0): ?>
                                <?php while($row = $recent_sales->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $row['id'] ?></td>
                                    <td><?= date('M d, Y', strtotime($row['sale_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['customer_name'] ?? 'Walk-in') ?></td>
                                    <td>₹<?= formatIndianNumber($row['total_amount']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center;">No sales in this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-section">
                    <h3 class="section-title">Recent Purchases</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_purchases->num_rows > 0): ?>
                                <?php while($row = $recent_purchases->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $row['id'] ?></td>
                                    <td><?= date('M d, Y', strtotime($row['purchase_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['supplier_name'] ?? 'Unknown') ?></td>
                                    <td>₹<?= formatIndianNumber($row['total_amount']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center;">No purchases in this period</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="chart-section">
                <h3 class="section-title">Monthly Performance</h3>
                <canvas id="monthlyChart" height="80"></canvas>
            </div>

            <div class="chart-section">
                <h3 class="section-title">Monthly Profit/Loss Breakdown</h3>
                <table id="monthlyTable">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Sales</th>
                            <th>Purchases</th>
                            <th>Profit/Loss</th>
                            <th>Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $monthly_data->data_seek(0);
                        if($monthly_data->num_rows > 0): 
                            while($row = $monthly_data->fetch_assoc()): 
                                $month_profit = $row['profit'];
                                $month_margin = $row['sales'] > 0 ? ($month_profit / $row['sales']) * 100 : 0;
                        ?>
                        <tr>
                            <td><strong><?= date('F Y', strtotime($row['month'].'-01')) ?></strong></td>
                            <td class="profit-text" style="font-weight: 500;">₹<?= formatIndianNumber($row['sales']) ?></td>
                            <td class="loss-text" style="font-weight: 500;">₹<?= formatIndianNumber($row['purchases']) ?></td>
                            <td class="<?= $month_profit >= 0 ? 'profit-text' : 'loss-text' ?>" style="font-weight: 600;">
                                <?= $month_profit >= 0 ? '+' : '' ?>₹<?= formatIndianNumber($month_profit) ?>
                            </td>
                            <td style="font-weight: 500;"><?= number_format($month_margin, 2) ?>%</td>
                        </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr><td colspan="5" style="text-align:center;">No data available for this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="two-column">
                <div class="chart-section">
                    <h3 class="section-title">Top 5 Products by Revenue</h3>
                    <table id="topProductsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($all_products_data) > 0): ?>
                                <?php foreach(array_slice($all_products_data, 0, 5) as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['Product']) ?></td>
                                    <td><?= $row['QuantitySold'] ?></td>
                                    <td style="font-weight: 500;">₹<?= formatIndianNumber($row['TotalRevenue']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align:center;">No data available</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-section">
                    <h3 class="section-title">Profit Distribution</h3>
                    <canvas id="profitChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Indian Numbering System Formatter
        function formatIndianCurrency(num) {
            const n = parseFloat(num);
            if (isNaN(n)) return '₹0';
            
            const numStr = Math.abs(n).toFixed(2);
            const [integer, decimal] = numStr.split('.');
            
            // Indian system: last 3 digits, then groups of 2
            let lastThree = integer.substring(integer.length - 3);
            let otherNumbers = integer.substring(0, integer.length - 3);
            
            if (otherNumbers !== '') {
                lastThree = ',' + lastThree;
            }
            
            let result = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
            
            return (n < 0 ? '-₹' : '₹') + result + '.' + decimal;
        }

        Chart.defaults.font.family = "'Poppins', sans-serif";

        const monthlyData = <?php 
            $months = [];
            $sales = [];
            $purchases = [];
            $profits = [];
            $monthly_data->data_seek(0);
            while($row = $monthly_data->fetch_assoc()) {
                array_unshift($months, $row['month']);
                array_unshift($sales, $row['sales']);
                array_unshift($purchases, $row['purchases']);
                array_unshift($profits, $row['profit']);
            }
            echo json_encode([
                'months' => $months,
                'sales' => $sales,
                'purchases' => $purchases,
                'profits' => $profits
            ]);
        ?>;

        const allSalesData = <?php echo json_encode($export_sales_data); ?>;
        const allPurchasesData = <?php echo json_encode($export_purchases_data); ?>;
        const allProductsData = <?php echo json_encode($all_products_data); ?>;

        const ctx1 = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: monthlyData.months,
                datasets: [
                    {
                        label: 'Sales',
                        data: monthlyData.sales,
                        borderColor: '#38A169',
                        backgroundColor: 'rgba(56, 161, 105, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Purchases',
                        data: monthlyData.purchases,
                        borderColor: '#E53E3E',
                        backgroundColor: 'rgba(229, 62, 62, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Profit',
                        data: monthlyData.profits,
                        borderColor: '#5A67D8',
                        backgroundColor: 'rgba(90, 103, 216, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 14 } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return formatIndianCurrency(value); }
                        }
                    }
                }
            }
        });

        const ctx2 = document.getElementById('profitChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Sales Revenue', 'Purchase Costs', 'Net Profit'],
                datasets: [{
                    data: [
                        <?= $total_sales_amount ?>,
                        <?= $total_purchases_amount ?>,
                        <?= max(0, $profit_loss) ?>
                    ],
                    backgroundColor: ['#38A169', '#E53E3E', '#5A67D8'],
                    borderWidth: 4,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 14 }, padding: 20 } }
                },
                cutout: '70%'
            }
        });

        function exportExcel() {
            const wb = XLSX.utils.book_new();
            
            const summaryData = [
                ['Financial Report Summary'],
                ['Period', '<?= date("M d, Y", strtotime($date_from)) ?> to <?= date("M d, Y", strtotime($date_to)) ?>'],
                [],
                ['Metric', 'Value'],
                ['Total Sales', '₹<?= formatIndianNumber($total_sales_amount) ?>'],
                ['Total Purchases', '₹<?= formatIndianNumber($total_purchases_amount) ?>'],
                ['Profit/Loss', '₹<?= formatIndianNumber($profit_loss) ?>'],
                ['Net Profit Margin', '<?= $total_sales_amount > 0 ? number_format(($profit_loss / $total_sales_amount) * 100, 2) : 0 ?>%'],
                ['Inventory Retail Value', '₹<?= formatIndianNumber($inventory_value) ?>']
            ];
            const ws1 = XLSX.utils.aoa_to_sheet(summaryData);
            ws1['!cols'] = [{ wch: 25 }, { wch: 20 }];
            XLSX.utils.book_append_sheet(wb, ws1, 'Summary');

            const monthlyTable = document.getElementById('monthlyTable');
            const ws2 = XLSX.utils.table_to_sheet(monthlyTable);
            ws2['!cols'] = [{ wch: 20 }, { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 10 }];
            XLSX.utils.book_append_sheet(wb, ws2, 'Monthly Breakdown');

            const ws3 = XLSX.utils.json_to_sheet(allProductsData);
            ws3['!cols'] = [{ wch: 30 }, { wch: 15 }, { wch: 20 }];
            XLSX.utils.book_append_sheet(wb, ws3, 'All Products by Revenue');

            const ws4 = XLSX.utils.json_to_sheet(allSalesData);
            ws4['!cols'] = [{ wch: 10 }, { wch: 20 }, { wch: 25 }, { wch: 15 }];
            XLSX.utils.book_append_sheet(wb, ws4, 'Detailed Sales');

            const ws5 = XLSX.utils.json_to_sheet(allPurchasesData);
            ws5['!cols'] = [{ wch: 10 }, { wch: 20 }, { wch: 25 }, { wch: 15 }];
            XLSX.utils.book_append_sheet(wb, ws5, 'Detailed Purchases');

            XLSX.writeFile(wb, 'financial_report_<?= $filter ?>_<?= date("Y-m-d") ?>.xlsx');
        }
    </script>
</body>
</html>