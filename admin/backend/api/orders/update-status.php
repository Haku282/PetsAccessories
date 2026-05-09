<?php
/**
 * API: Cập nhật trạng thái đơn hàng
 * POST /admin/backend/api/orders/update-status.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST request']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    
    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['order_id']) || !isset($data['new_status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $orderId = (int)$data['order_id'];
    $newStatus = $data['new_status'];
    $reason = $data['reason'] ?? '';

    // Các trạng thái hợp lệ
    $validStatuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
    if (!in_array($newStatus, $validStatuses)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
        exit;
    }

    // Lấy đơn hàng hiện tại
    $stmt = $db->prepare("SELECT order_status FROM orders WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
        exit;
    }

    $oldStatus = $order['order_status'];

    // Không cập nhật nếu trạng thái giống nhau
    if ($oldStatus === $newStatus) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Trạng thái mới giống trạng thái cũ']);
        exit;
    }

    // Bắt đầu transaction
    $db->beginTransaction();

    try {
        // Cập nhật trạng thái đơn hàng
        $updateStmt = $db->prepare("
            UPDATE orders 
            SET order_status = ?, updated_at = NOW()
            WHERE order_id = ?
        ");
        $updateStmt->execute([$newStatus, $orderId]);

        // Thêm vào lịch sử trạng thái (chỉ lưu status mới)
        $historyStmt = $db->prepare("
            INSERT INTO order_status_history (order_id, status, changed_at)
            VALUES (?, ?, NOW())
        ");
        $historyStmt->execute([$orderId, $newStatus]);

        // Ghi log thay đổi
        $logStmt = $db->prepare("
            INSERT INTO order_logs (order_id, admin_id, old_status, new_status, reason, changed_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $logStmt->execute([$orderId, $_SESSION['user_id'], $oldStatus, $newStatus, $reason]);

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công',
            'data' => [
                'order_id' => $orderId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
