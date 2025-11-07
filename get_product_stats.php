<?php
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    require 'db.php';

    $admin_id = $_SESSION['admin_id'];
    $product_id = $_POST['product_id'] ?? null;

    if (!$product_id) {
        echo json_encode(['error' => 'Product ID required']);
        exit();
    }

    // Verify product belongs to admin
    $verify = $conn->prepare("SELECT name FROM products WHERE id = ? AND admin_id = ?");
    $verify->bind_param('ii', $product_id, $admin_id);
    $verify->execute();
    $result = $verify->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Product not found']);
        exit();
    }

    $product = $result->fetch_assoc();
    $verify->close();

    // Get monthly sales (last 6 months) - using sale_items table
    $sales_query = "
        SELECT 
            DATE_FORMAT(s.sale_date, '%Y-%m') AS month,
            SUM(si.quantity) AS total_quantity,
            SUM(si.subtotal) AS total_amount,
            COUNT(DISTINCT s.id) AS transaction_count
        FROM sales s
        JOIN sale_items si ON s.id = si.sale_id
        WHERE si.product_id = ? AND s.admin_id = ?
        AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m')
        ORDER BY month DESC
    ";

    $stmt = $conn->prepare($sales_query);
    $stmt->bind_param('ii', $product_id, $admin_id);
    $stmt->execute();
    $sales_result = $stmt->get_result();

    $monthly_sales = [];
    while ($row = $sales_result->fetch_assoc()) {
        $monthly_sales[] = $row;
    }
    $stmt->close();

    // Get recent stock changes (last 20 transactions) - using purchase_items and sale_items
    $stock_query = "
        SELECT 
            'purchase' AS type,
            p.purchase_date AS date,
            pi.quantity,
            CONCAT('Purchase from ', sup.name) AS description
        FROM purchases p
        JOIN purchase_items pi ON p.id = pi.purchase_id
        LEFT JOIN suppliers sup ON p.supplier_id = sup.id
        WHERE pi.product_id = ? AND p.admin_id = ?
        
        UNION ALL
        
        SELECT 
            'sale' AS type,
            s.sale_date AS date,
            -si.quantity AS quantity,
            CONCAT('Sale to ', COALESCE(c.name, 'Customer')) AS description
        FROM sales s
        JOIN sale_items si ON s.id = si.sale_id
        LEFT JOIN customers c ON s.customer_id = c.id
        WHERE si.product_id = ? AND s.admin_id = ?
        
        ORDER BY date DESC
        LIMIT 20
    ";

    $stmt = $conn->prepare($stock_query);
    $stmt->bind_param('iiii', $product_id, $admin_id, $product_id, $admin_id);
    $stmt->execute();
    $stock_result = $stmt->get_result();

    $stock_history = [];
    while ($row = $stock_result->fetch_assoc()) {
        $stock_history[] = $row;
    }
    $stmt->close();

    // Get current stock
    $current_stock_query = "SELECT stock FROM products WHERE id = ? AND admin_id = ?";
    $stmt = $conn->prepare($current_stock_query);
    $stmt->bind_param('ii', $product_id, $admin_id);
    $stmt->execute();
    $current_stock = $stmt->get_result()->fetch_assoc()['stock'];
    $stmt->close();

    // Get total stats - using sale_items table
    $total_stats_query = "
        SELECT 
            COALESCE(SUM(si.quantity), 0) AS total_sold,
            COALESCE(SUM(si.subtotal), 0) AS total_revenue
        FROM sales s
        JOIN sale_items si ON s.id = si.sale_id
        WHERE si.product_id = ? AND s.admin_id = ?
    ";

    $stmt = $conn->prepare($total_stats_query);
    $stmt->bind_param('ii', $product_id, $admin_id);
    $stmt->execute();
    $total_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode([
        'success' => true,
        'product_name' => $product['name'],
        'current_stock' => $current_stock,
        'total_sold' => $total_stats['total_sold'],
        'total_revenue' => $total_stats['total_revenue'],
        'monthly_sales' => $monthly_sales,
        'stock_history' => $stock_history
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
}
?>