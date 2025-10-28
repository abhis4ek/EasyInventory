<?php
require 'db.php';

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

// Total Sales
$sql_sales = "SELECT COUNT(*) as total_transactions, SUM(total_amount) as total_sales 
              FROM sales 
              WHERE sale_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_sales);
$stmt->bind_param('ss', $date_from, $date_to);
$stmt->execute();
$sales_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Total Purchases
$sql_purchases = "SELECT COUNT(*) as total_transactions, SUM(total_amount) as total_purchases 
                  FROM purchases 
                  WHERE purchase_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_purchases);
$stmt->bind_param('ss', $date_from, $date_to);
$stmt->execute();
$purchases_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate Profit/Loss
$total_sales = $sales_data['total_sales'] ?? 0;
$total_purchases = $purchases_data['total_purchases'] ?? 0;
$profit_loss = $total_sales - $total_purchases;

// Monthly breakdown for chart
$monthly_sql = "
    SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        SUM(sales) as sales,
        SUM(purchases) as purchases,
        SUM(sales) - SUM(purchases) as profit
    FROM (
        SELECT sale_date as date, total_amount as sales, 0 as purchases FROM sales
        WHERE sale_date BETWEEN ? AND ?
        UNION ALL
        SELECT purchase_date as date, 0 as sales, total_amount as purchases FROM purchases
        WHERE purchase_date BETWEEN ? AND ?
    ) combined
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
";
$stmt = $conn->prepare($monthly_sql);
$stmt->bind_param('ssss', $date_from, $date_to, $date_from, $date_to);
$stmt->execute();
$monthly_data = $stmt->get_result();
$stmt->close();

// Top selling products
$top_products_sql = "
    SELECT p.name, SUM(si.quantity) as total_qty, SUM(si.subtotal) as total_revenue
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE s.sale_date BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_revenue DESC
    LIMIT 5
";
$stmt = $conn->prepare($top_products_sql);
$stmt->bind_param('ss', $date_from, $date_to);
$stmt->execute();
$top_products = $stmt->get_result();
$stmt->close();

// Recent transactions
$recent_sales_sql = "
    SELECT s.id, s.sale_date, s.total_amount, c.name as customer_name
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.id
    WHERE s.sale_date BETWEEN ? AND ?
    ORDER BY s.sale_date DESC
    LIMIT 10
";
$stmt = $conn->prepare($recent_sales_sql);
$stmt->bind_param('ss', $date_from, $date_to);
$stmt->execute();
$recent_sales = $stmt->get_result();
$stmt->close();

$recent_purchases_sql = "
    SELECT p.id, p.purchase_date, p.total_amount, s.name as supplier_name
    FROM purchases p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.purchase_date BETWEEN ? AND ?
    ORDER BY p.purchase_date DESC
    LIMIT 10
";
$stmt = $conn->prepare($recent_purchases_sql);
$stmt->bind_param('ss', $date_from, $date_to);
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
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: #3498db;
            color: white;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .bg-success { background-color: #e8f5e9; color: #388e3c; }
        .bg-danger { background-color: #ffebee; color: #d32f2f; }
        .bg-warning { background-color: #fff8e1; color: #f57c00; }
        .bg-info { background-color: #e3f2fd; color: #1976d2; }
        
        .card-title {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .card-subtitle {
            font-size: 0.85rem;
            color: #95a5a6;
            margin-top: 5px;
        }
        
        .profit { color: #27ae60; }
        .loss { color: #e74c3c; }
        
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
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #2980b9;
        }
        
        .back-btn i {
            margin-right: 8px;
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

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon bg-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="card-title">Total Sales</div>
                    </div>
                </div>
                <div class="card-value">₹<?= number_format($total_sales, 2) ?></div>
                <div class="card-subtitle"><?= $sales_data['total_transactions'] ?> transactions</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <div class="card-title">Total Purchases</div>
                    </div>
                </div>
                <div class="card-value">₹<?= number_format($total_purchases, 2) ?></div>
                <div class="card-subtitle"><?= $purchases_data['total_transactions'] ?> transactions</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon <?= $profit_loss >= 0 ? 'bg-success' : 'bg-warning' ?>">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="card-title">Profit / Loss</div>
                    </div>
                </div>
                <div class="card-value <?= $profit_loss >= 0 ? 'profit' : 'loss' ?>">
                    ₹<?= number_format(abs($profit_loss), 2) ?>
                </div>
                <div class="card-subtitle">
                    <?= $profit_loss >= 0 ? 'Profit' : 'Loss' ?> 
                    (<?= $total_purchases > 0 ? number_format(($profit_loss / $total_purchases) * 100, 2) : 0 ?>%)
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon bg-info">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div>
                        <div class="card-title">Profit Margin</div>
                    </div>
                </div>
                <div class="card-value">
                    <?= $total_sales > 0 ? number_format(($profit_loss / $total_sales) * 100, 2) : 0 ?>%
                </div>
                <div class="card-subtitle">Net margin</div>
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

        <!-- Recent Transactions -->
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

        <div style="text-align: center; margin-top: 30px;">
            <a href="front.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
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