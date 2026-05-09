<?php
/**
 * API: Xóa vĩnh viễn đơn hàng
 * POST /admin/backend/api/orders/delete.php
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
$orderId = isset($data['id']) ? (int)$data['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;

    $stmt = $db->prepare("SELECT order_status FROM orders WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
        exit;
    }

    // [BẢO MẬT] Bắt buộc phải hủy đơn thì mới được xóa vĩnh viễn
    if ($order['order_status'] !== 'cancelled') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Vui lòng Hủy đơn hàng trước khi xóa vĩnh viễn!']);
        exit;
    }

    $db->beginTransaction();

    try {
        // Xóa lần lượt từ bảng con ra bảng cha để không dính lỗi Khóa ngoại (Foreign Key)
        $db->prepare("DELETE FROM order_logs WHERE order_id = ?")->execute([$orderId]);
        $db->prepare("DELETE FROM order_status_history WHERE order_id = ?")->execute([$orderId]);
        $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
        $db->prepare("DELETE FROM orders WHERE order_id = ?")->execute([$orderId]);

        $db->commit();

        echo json_encode(['success' => true, 'message' => 'Đã xóa vĩnh viễn đơn hàng']);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>