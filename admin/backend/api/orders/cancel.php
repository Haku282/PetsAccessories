<?php
/**
 * API: Hủy đơn hàng và Hoàn tồn kho
 * POST /admin/backend/api/orders/cancel.php
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

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$orderId = (int)$data['order_id'];
$reason = $data['reason'] ?? 'Admin hủy đơn hàng';

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;

    // 1. Kiểm tra đơn hàng
    $stmt = $db->prepare("SELECT order_status FROM orders WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
        exit;
    }

    $oldStatus = $order['order_status'];

    // Không cho phép hủy nếu đã hoàn thành hoặc đã hủy rồi
    if ($oldStatus === 'cancelled' || $oldStatus === 'completed') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng đang ở trạng thái này']);
        exit;
    }

    $db->beginTransaction();

    try {
        // 2. Cập nhật trạng thái thành 'cancelled'
        $updateStmt = $db->prepare("UPDATE orders SET order_status = 'cancelled', updated_at = NOW() WHERE order_id = ?");
        $updateStmt->execute([$orderId]);

        // 3. Lưu lịch sử
        $historyStmt = $db->prepare("INSERT INTO order_status_history (order_id, status, changed_at) VALUES (?, 'cancelled', NOW())");
        $historyStmt->execute([$orderId]);

        // 4. Lưu log
        $logStmt = $db->prepare("INSERT INTO order_logs (order_id, admin_id, old_status, new_status, reason, changed_at) VALUES (?, ?, ?, 'cancelled', ?, NOW())");
        $logStmt->execute([$orderId, $_SESSION['user_id'], $oldStatus, $reason]);

        // 5. HOÀN LẠI TỒN KHO
        $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$orderId]);
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Lưu ý: Sửa chữ 'stock_quantity' thành tên cột số lượng tồn kho thực tế trong bảng products của bạn
        $restoreStockStmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
        foreach ($orderItems as $item) {
            $restoreStockStmt->execute([$item['quantity'], $item['product_id']]);
        }

        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng và hoàn lại tồn kho']);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>