<?php
/**
 * API: Thống kê & báo cáo tổng hợp
 * GET /admin/backend/api/statistics.php
 */

header('Content-Type: application/json');

// Chỉ gọi session_start() nếu session chưa được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

require_once __DIR__ . '/../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;

    $mode = isset($_GET['mode']) ? trim((string)$_GET['mode']) : '';
    $groupBy = isset($_GET['group_by']) ? trim((string)$_GET['group_by']) : 'month';

    if ($mode === 'revenue_breakdown') {
        $allowedGroups = ['day', 'month', 'year'];
        if (!in_array($groupBy, $allowedGroups, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'group_by không hợp lệ']);
            exit;
        }

        if ($groupBy === 'day') {
            $periodExpr = "DATE(created_at)";
            $labelExpr = "DATE_FORMAT(created_at, '%d/%m/%Y')";
        } elseif ($groupBy === 'year') {
            $periodExpr = "DATE_FORMAT(created_at, '%Y')";
            $labelExpr = "DATE_FORMAT(created_at, '%Y')";
        } else {
            $periodExpr = "DATE_FORMAT(created_at, '%Y-%m')";
            $labelExpr = "DATE_FORMAT(created_at, '%m/%Y')";
        }

        $stmt = $db->query("
            SELECT 
                {$periodExpr} AS period_key,
                {$labelExpr} AS period_label,
                COUNT(*) AS order_count,
                COALESCE(SUM(total_price), 0) AS revenue
            FROM orders
            WHERE order_status = 'completed'
            GROUP BY period_key, period_label
            ORDER BY period_key DESC
        ");
        $periodRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalRevenueStmt = $db->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE order_status = 'completed'");
        $totalRevenue = (float)$totalRevenueStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'stats' => [
                'group_by' => $groupBy,
                'total_revenue' => $totalRevenue,
                'periods' => array_map(function ($row) {
                    return [
                        'period_key' => $row['period_key'],
                        'period_label' => $row['period_label'],
                        'order_count' => (int)$row['order_count'],
                        'revenue' => (float)$row['revenue']
                    ];
                }, $periodRows)
            ]
        ]);
        exit;
    }
    
    $stats = [];

    // 1. Thống kê khách hàng
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $stats['total_customers'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['new_customers_30d'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("
        SELECT COUNT(*) FROM (
            SELECT user_id FROM orders 
            GROUP BY user_id 
            HAVING COUNT(order_id) >= 3
        ) AS frequent
    ");
    $stats['frequent_customers'] = (int)$stmt->fetchColumn();

    // 2. Thống kê đơn hàng
    $stmt = $db->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'completed'");
    $stats['completed_orders'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
    $stats['pending_orders'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'cancelled'");
    $stats['cancelled_orders'] = (int)$stmt->fetchColumn();

    // 3. Thống kê doanh thu
    $stmt = $db->query("
        SELECT 
            COALESCE(SUM(total_price), 0) as total_revenue,
            COALESCE(SUM(CASE WHEN order_status = 'completed' THEN total_price ELSE 0 END), 0) as completed_revenue,
            COALESCE(AVG(total_price), 0) as avg_order_value
        FROM orders
    ");
    $revenueRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_revenue'] = (float)$revenueRow['total_revenue'];
    $stats['completed_revenue'] = (float)$revenueRow['completed_revenue'];
    $stats['avg_order_value'] = (float)$revenueRow['avg_order_value'];

    // 4. Thống kê sản phẩm
    $stmt = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $stats['active_products'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5");
    $stats['low_stock_products'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("
        SELECT p.product_name, SUM(oi.quantity) as sold_quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.order_status IN ('confirmed', 'shipping', 'completed')
        GROUP BY p.product_id, p.product_name
        ORDER BY sold_quantity DESC
        LIMIT 5
    ");
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats['top_selling_products'] = array_map(function($product) {
        return [
            'product_name' => $product['product_name'],
            'sold_quantity' => (int)$product['sold_quantity']
        ];
    }, $topProducts);

    // 5. Thống kê khuyến mãi & coupon
    $stmt = $db->query("SELECT COUNT(*) FROM promotions WHERE status = 1");
    $stats['active_promotions'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM coupons WHERE status = 1");
    $stats['active_coupons'] = (int)$stmt->fetchColumn();

    // 6. Thống kê đánh giá
    $stmt = $db->query("SELECT COUNT(*) FROM reviews WHERE status = 1");
    $stats['approved_reviews'] = (int)$stmt->fetchColumn();

    $stmt = $db->query("SELECT AVG(rating) FROM reviews WHERE status = 1");
    $avgRating = $stmt->fetchColumn();
    $stats['avg_rating'] = $avgRating ? (float)$avgRating : 0.0;

    // 7. Thống kê theo thời gian (7 ngày gần nhất)
    $stmt = $db->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as order_count,
            COALESCE(SUM(total_price), 0) as revenue
        FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats['last_7_days'] = array_map(function($day) {
        return [
            'date' => $day['date'],
            'order_count' => (int)$day['order_count'],
            'revenue' => (float)$day['revenue']
        ];
    }, $dailyStats);

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
