<?php
/**
 * API: Cập nhật trạng thái thanh toán đơn hàng
 * POST /admin/backend/api/orders/update-payment-status.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST request']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    $db = $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['order_id']) || !is_numeric($data['order_id']) || !isset($data['payment_status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $orderId = (int)$data['order_id'];
    $paymentStatus = $data['payment_status'];
    $allowed = ['unpaid', 'paid', 'refunded'];
    if (!in_array($paymentStatus, $allowed, true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Trạng thái thanh toán không hợp lệ']);
        exit;
    }

    $stmt = $db->prepare('SELECT order_id FROM orders WHERE order_id = ?');
    $stmt->execute([$orderId]);
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
        exit;
    }

    $update = $db->prepare('UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE order_id = ?');
    $update->execute([$paymentStatus, $orderId]);

    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thanh toán thành công']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
