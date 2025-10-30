<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';

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

// ✅ Total Sales (filtered by user)
$sql_sales = "SELECT COUNT(*) as total_transactions, SUM(total_amount) as total_sales 
              FROM sales 
              WHERE admin_id = ? AND sale_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_sales);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$sales_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ✅ Total Purchases (filtered by user)
$sql_purchases = "SELECT COUNT(*) as total_transactions, SUM(total_amount) as total_purchases 
                  FROM purchases 
                  WHERE admin_id = ? AND purchase_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_purchases);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$purchases_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate Profit/Loss
$total_sales = $sales_data['total_sales'] ?? 0;
$total_purchases = $purchases_data['total_purchases'] ?? 0;
$profit_loss = $total_sales - $total_purchases;

// ✅ Monthly breakdown for chart (filtered by user)
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

// ✅ Top selling products (filtered by user)
$top_products_sql = "
    SELECT p.name, SUM(si.quantity) as total_qty, SUM(si.subtotal) as total_revenue
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE s.admin_id = ? AND s.sale_date BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_revenue DESC
    LIMIT 5
";
$stmt = $conn->prepare($top_products_sql);
$stmt->bind_param('iss', $admin_id, $date_from, $date_to);
$stmt->execute();
$top_products = $stmt->get_result();
$stmt->close();

// ✅ Recent sales transactions (filtered by user)
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

// ✅ Recent purchase transactions (filtered by user)
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Easy Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e6ed;
        }
        
        .page-title {
            font-size: 2rem;
            color: #2c3e50;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #3498db;
            background: white;
            color: #3498db;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: #3498db;
            color: white;
        }
        
        .chart-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
            padding-left: 15px;
        }
        
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="page-title">Financial Reports</h1>
                <p style="color: #7f8c8d; margin-top: 5px;">
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

        <!-- Recent Transactions (Moved to Top) -->
        <div class="two-column">
            <!-- Recent Sales -->
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
                                <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center;">No sales in this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Purchases -->
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
                                <td>₹<?= number_format($row['total_amount'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center;">No purchases in this period</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Monthly Chart -->
        <div class="chart-section">
            <h3 class="section-title">Monthly Performance</h3>
            <canvas id="monthlyChart" height="80"></canvas>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="chart-section">
            <h3 class="section-title">Monthly Profit/Loss Breakdown</h3>
            <table>
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
                        <td style="color: #27ae60;">₹<?= number_format($row['sales'], 2) ?></td>
                        <td style="color: #e74c3c;">₹<?= number_format($row['purchases'], 2) ?></td>
                        <td style="color: <?= $month_profit >= 0 ? '#27ae60' : '#e74c3c' ?>; font-weight: bold;">
                            <?= $month_profit >= 0 ? '+' : '' ?>₹<?= number_format($month_profit, 2) ?>
                        </td>
                        <td><?= number_format($month_margin, 2) ?>%</td>
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

        <!-- Two Column Section -->
        <div class="two-column">
            <!-- Top Products -->
            <div class="chart-section">
                <h3 class="section-title">Top 5 Products by Revenue</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($top_products->num_rows > 0): ?>
                            <?php while($row = $top_products->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= $row['total_qty'] ?></td>
                                <td>₹<?= number_format($row['total_revenue'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align:center;">No data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Profit Distribution -->
            <div class="chart-section">
                <h3 class="section-title">Profit Distribution</h3>
                <canvas id="profitChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Monthly Performance Chart
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

        const ctx1 = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: monthlyData.months,
                datasets: [
                    {
                        label: 'Sales',
                        data: monthlyData.sales,
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Purchases',
                        data: monthlyData.purchases,
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Profit',
                        data: monthlyData.profits,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Profit Distribution Pie Chart
        const ctx2 = document.getElementById('profitChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Sales Revenue', 'Purchase Costs', 'Net Profit'],
                datasets: [{
                    data: [
                        <?= $total_sales ?>,
                        <?= $total_purchases ?>,
                        <?= max(0, $profit_loss) ?>
                    ],
                    backgroundColor: [
                        'rgba(39, 174, 96, 0.8)',
                        'rgba(231, 76, 60, 0.8)',
                        'rgba(52, 152, 219, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>