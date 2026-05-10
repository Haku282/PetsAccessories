<?php
/**
 * API: Lấy danh sách đơn hàng
 * GET /admin/backend/api/orders/list.php
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
    
    // Lấy các tham số filter
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;

// 1. Khởi tạo câu điều kiện WHERE và Params dùng chung
    $whereSql = " WHERE 1=1";
    $params = [];

    if (!empty($status)) {
        $whereSql .= " AND o.order_status = ?";
        $params[] = $status;
    }

    if (!empty($search)) {
        $whereSql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR o.shipping_address LIKE ? OR o.order_id = ?)";
        $searchTerm = "%$search%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, (int)$search);
    }

    // 2. Query Đếm tổng số bản ghi (Không cần JOIN order_items cho nhẹ)
    $countSql = "SELECT COUNT(o.order_id) as total 
                 FROM orders o 
                 LEFT JOIN users u ON o.user_id = u.user_id" 
                 . $whereSql;
    
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. Query Lấy danh sách đơn hàng
    $sql = "
        SELECT 
            o.order_id, o.user_id, u.username, u.email, u.phone, o.shipping_address, o.total_price, 
            o.shipping_fee, o.discount_amount, o.order_status, 
            o.payment_status, o.payment_method, o.shipping_method, 
            o.created_at, o.updated_at,
            COUNT(oi.order_item_id) as item_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        " . $whereSql . "
        GROUP BY o.order_id
        ORDER BY o.created_at DESC 
        LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu trả về
    $formattedOrders = [];
    foreach ($orders as $order) {
        $statusInfo = getOrderStatusInfo($order['order_status']);
        $paymentStatusInfo = getPaymentStatusInfo($order['payment_status']);
        
        $formattedOrders[] = [
            'order_id' => $order['order_id'],
            'username' => $order['username'],
            'email' => $order['email'],
            'item_count' => (int)$order['item_count'],
            'total_price' => (float)$order['total_price'],
            'shipping_fee' => (float)$order['shipping_fee'],
            'discount_amount' => (float)$order['discount_amount'],
            'phone' => $order['phone'],
            'shipping_address' => $order['shipping_address'],
            'order_status' => $order['order_status'],
            'order_status_label' => $statusInfo['label'],
            'order_status_color' => $statusInfo['color'],
            'payment_status' => $order['payment_status'],
            'payment_status_label' => $paymentStatusInfo['label'],
            'payment_status_color' => $paymentStatusInfo['color'],
            'payment_method' => $order['payment_method'],
            'shipping_method' => $order['shipping_method'],
            'created_at' => $order['created_at'],
            'updated_at' => $order['updated_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $formattedOrders,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalRecords,
            'pages' => ceil($totalRecords / $limit)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>

