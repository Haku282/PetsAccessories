<?php
/**
 * API: Lấy chi tiết đơn hàng
 * GET /admin/backend/api/orders/get.php?id=ORDER_ID
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

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

// Helper functions - định nghĩa trước khi sử dụng
function getOrderStatusInfo($status) {
    $statusMap = [
        'pending' => ['label' => '⏳ Chờ xác nhận', 'color' => '#ff9800'],
        'confirmed' => ['label' => '✅ Đã xác nhận', 'color' => '#2196F3'],
        'shipping' => ['label' => '🚚 Đang giao', 'color' => '#4CAF50'],
        'completed' => ['label' => '🎉 Hoàn thành', 'color' => '#009688'],
        'cancelled' => ['label' => '❌ Hủy', 'color' => '#f44336']
    ];
    return $statusMap[$status] ?? ['label' => $status, 'color' => '#666'];
}

function getPaymentStatusInfo($status) {
    $statusMap = [
        'unpaid' => ['label' => '💳 Chưa thanh toán', 'color' => '#ff9800'],
        'paid' => ['label' => '✅ Đã thanh toán', 'color' => '#4CAF50'],
        'refunded' => ['label' => '↩️ Hoàn tiền', 'color' => '#2196F3']
    ];
    return $statusMap[$status] ?? ['label' => $status, 'color' => '#666'];
}

try {
    /** @var PDO $pdo */
    $db = $pdo;
    $orderId = (int)$_GET['id'];

    // Lấy thông tin đơn hàng
    $stmt = $db->prepare("
        SELECT 
            o.*,
            u.user_name,
            u.email,
            u.phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
        exit;
    }

    // Lấy chi tiết các sản phẩm trong đơn hàng
    $itemsStmt = $db->prepare("
        SELECT 
            oi.order_item_id,
            oi.product_id,
            oi.quantity,
            oi.price_at_purchase,
            p.product_name,
            p.thumbnail
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy lịch sử cập nhật trạng thái
    $historyStmt = $db->prepare("
        SELECT * FROM order_status_history 
        WHERE order_id = ? 
        ORDER BY changed_at DESC
    ");
    $historyStmt->execute([$orderId]);
    $statusHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy log thay đổi (nếu có)
    $logsStmt = $db->prepare("
        SELECT 
            ol.*,
            a.user_name as admin_name
        FROM order_logs ol
        LEFT JOIN users a ON ol.admin_id = a.user_id
        WHERE ol.order_id = ?
        ORDER BY ol.changed_at DESC
    ");
    $logsStmt->execute([$orderId]);
    $logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);

    $statusInfo = getOrderStatusInfo($order['order_status']);
    $paymentStatusInfo = getPaymentStatusInfo($order['payment_status']);

    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => $order['order_id'],
            'user_id' => $order['user_id'],
            'user_name' => $order['user_name'],
            'email' => $order['email'],
            'phone' => $order['phone'],
            'total_price' => (float)$order['total_price'],
            'shipping_fee' => (float)$order['shipping_fee'],
            'discount_amount' => (float)$order['discount_amount'],
            'order_status' => $order['order_status'],
            'order_status_label' => $statusInfo['label'],
            'payment_status' => $order['payment_status'],
            'payment_status_label' => $paymentStatusInfo['label'],
            'payment_method' => $order['payment_method'],
            'shipping_method' => $order['shipping_method'],
            'shipping_address' => $order['shipping_address'],
            'notes' => $order['notes'],
            'created_at' => $order['created_at'],
            'updated_at' => $order['updated_at'],
            'items' => array_map(function($item) {
                return [
                    'order_item_id' => $item['order_item_id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => (int)$item['quantity'],
                    'price_at_purchase' => (float)$item['price_at_purchase'],
                    'thumbnail' => $item['thumbnail'],
                    'subtotal' => (float)$item['quantity'] * (float)$item['price_at_purchase']
                ];
            }, $items),
            'status_history' => array_map(function($h) {
                return [
                    'status' => $h['status'],
                    'status_label' => getOrderStatusInfo($h['status'])['label'],
                    'changed_at' => $h['changed_at']
                ];
            }, $statusHistory),
            'logs' => $logs
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
