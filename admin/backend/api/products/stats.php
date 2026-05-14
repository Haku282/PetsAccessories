<?php
/**
 * API: Thống kê sản phẩm (bán chạy + tồn kho)
 * GET /admin/backend/api/products/stats.php
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;

    $topProductStmt = $db->query("
        SELECT 
            p.product_id,
            p.product_name,
            COALESCE(SUM(CASE WHEN o.order_id IS NOT NULL THEN oi.quantity ELSE 0 END), 0) AS sold_quantity
        FROM products p
        LEFT JOIN order_items oi ON oi.product_id = p.product_id
        LEFT JOIN orders o ON o.order_id = oi.order_id
            AND o.order_status IN ('confirmed', 'shipping', 'completed')
        GROUP BY p.product_id, p.product_name
        ORDER BY sold_quantity DESC, p.product_id DESC
        LIMIT 1
    ");
    $topProduct = $topProductStmt->fetch(PDO::FETCH_ASSOC);

    $inventoryStmt = $db->query("
        SELECT
            COUNT(*) AS total_products,
            SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) AS out_of_stock_products,
            SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= 5 THEN 1 ELSE 0 END) AS low_stock_products,
            COALESCE(SUM(stock_quantity), 0) AS total_units_in_stock
        FROM products
    ");
    $inventory = $inventoryStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => [
            'top_selling_product' => [
                'product_id' => isset($topProduct['product_id']) ? (int)$topProduct['product_id'] : null,
                'product_name' => $topProduct['product_name'] ?? 'Chưa có dữ liệu',
                'sold_quantity' => isset($topProduct['sold_quantity']) ? (int)$topProduct['sold_quantity'] : 0
            ],
            'inventory' => [
                'total_products' => (int)($inventory['total_products'] ?? 0),
                'out_of_stock_products' => (int)($inventory['out_of_stock_products'] ?? 0),
                'low_stock_products' => (int)($inventory['low_stock_products'] ?? 0),
                'total_units_in_stock' => (int)($inventory['total_units_in_stock'] ?? 0)
            ]
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
