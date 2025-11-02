<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo '<script>parent.location.href="login.php";</script>';
    exit();
}
require 'db.php';

$admin_id = $_SESSION['admin_id'];
$fullname = $_SESSION['fullname'] ?? 'Admin User';

// Total products
$sql_total = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE admin_id = ?";
$stmt = $conn->prepare($sql_total);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$total_products = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// In stock (>20)
$sql_instock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE admin_id = ? AND stock > 20";
$stmt = $conn->prepare($sql_instock);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$in_stock = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Low stock (1-20)
$sql_lowstock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE admin_id = ? AND stock > 0 AND stock <= 20";
$stmt = $conn->prepare($sql_lowstock);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$low_stock = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Out of stock (0)
$sql_outstock = "SELECT COUNT(DISTINCT id) AS total FROM products WHERE admin_id = ? AND stock = 0";
$stmt = $conn->prepare($sql_outstock);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$out_of_stock = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total purchases
$sql = "SELECT COUNT(*) AS total, COALESCE(SUM(total_amount), 0) AS total_amount FROM purchases WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$total_purchases = $r['total'];
$total_purchases_amount = $r['total_amount'];
$stmt->close();

// Total sales
$sql = "SELECT COUNT(*) AS total, COALESCE(SUM(total_amount), 0) AS total_amount FROM sales WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$total_sales = $r['total'];
$total_sales_amount = $r['total_amount'];
$stmt->close();

// Calculate Profit/Loss
$profit_loss = $total_sales_amount - $total_purchases_amount;

// Get total inventory value
$sql_inventory = "SELECT COALESCE(SUM(cost_price * stock), 0) AS inventory_value FROM products WHERE admin_id = ?";
$stmt = $conn->prepare($sql_inventory);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$inventory_value = $stmt->get_result()->fetch_assoc()['inventory_value'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
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
        
        .card-info small {
            display: block;
            color: #95a5a6;
            font-size: 0.75rem;
            margin-top: 2px;
        }
        
        .bg-primary { background-color: #e3f2fd; color: #1976d2; }
        .bg-success { background-color: #e8f5e9; color: #388e3c; }
        .bg-warning { background-color: #fff8e1; color: #f57c00; }
        .bg-danger { background-color: #ffebee; color: #d32f2f; }
        .bg-purple { background-color: #f3e5f5; color: #7b1fa2; }
        .bg-teal { background-color: #e0f2f1; color: #00796b; }
        .bg-info { background-color: #e1f5fe; color: #0277bd; }
        
        .profit-text { color: #388e3c; }
        .loss-text { color: #d32f2f; }

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

        .badge-mrp {
          background: #ff9800;
          color: white;
          padding: 2px 8px;
          border-radius: 4px;
          font-size: 0.75rem;
          margin-left: 8px;
          float: right;  
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 class="page-title">Dashboard Overview</h2>
        <div class="user-info">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullname) ?>&background=3498db&color=fff" alt="User">
            <span><?= htmlspecialchars($fullname) ?></span>
        </div>
    </div>

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

        <div class="card">
            <div class="card-icon bg-info">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="card-info">
                <h3>₹<?php echo number_format($inventory_value, 2); ?></h3>
                <p>Inventory Value</p>
                <small>Total stock at cost price</small>
            </div>
        </div>

        <div class="card">
            <div class="card-icon bg-success">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-info">
                <h3>₹<?php echo number_format($total_sales_amount, 2); ?></h3>
                <p>Total Sales</p>
                <small><?php echo $total_sales; ?> transactions</small>
            </div>
        </div>

        <div class="card">
            <div class="card-icon bg-purple">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card-info">
                <h3>₹<?php echo number_format($total_purchases_amount, 2); ?></h3>
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
                    ₹<?php echo number_format(abs($profit_loss), 2); ?>
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

    <div class="content-section">
        <div class="section-header">
            <h3 class="section-title">Recent Products</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $conn->prepare("
                    SELECT p.id, p.name, p.has_mrp, p.stock, c.name AS category_name
                    FROM products p
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.admin_id = ?
                    ORDER BY 
                        CASE 
                            WHEN p.stock > 20 THEN 1
                            WHEN p.stock > 0 THEN 2
                            ELSE 3
                        END, p.name
                    LIMIT 10
                ");
                $stmt->bind_param('i', $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($product = $result->fetch_assoc()) { 
                        $status_class = $product['stock'] > 20 ? 'instock' : ($product['stock'] > 0 ? 'lowstock' : 'outstock');
                        $status_text = $product['stock'] > 20 ? 'In Stock' : ($product['stock'] > 0 ? 'Low Stock' : 'Out of Stock');
                        ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($product['name']); ?>
                            <?php if($product['has_mrp']): ?>
                                <span class="badge-mrp">MRP</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td>
                            <span class="status status-<?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td>
                            <button class="action-btn edit-btn" 
                                onclick="goToInventory(<?php echo $product['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>No products found</td></tr>";
                }
                $stmt->close();
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function goToInventory(productId) {
            parent.loadPage('inventory.php');
        }

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