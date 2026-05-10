<?php
/**
 * API: Xóa mã giảm giá
 * DELETE /admin/backend/api/coupons/delete.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận DELETE request']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    
    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    // Kiểm tra ID
    if (!isset($data['coupon_id']) || !is_numeric($data['coupon_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID coupon không hợp lệ']);
        exit;
    }

    $couponId = (int)$data['coupon_id'];

    // Kiểm tra coupon tồn tại
    $checkStmt = $db->prepare("SELECT coupon_id FROM coupons WHERE coupon_id = ?");
    $checkStmt->execute([$couponId]);
    if ($checkStmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy mã coupon']);
        exit;
    }

    // Kiểm tra xem coupon đã được sử dụng trong đơn hàng nào chưa
    $orderCheckStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE coupon_id = ?");
    $orderCheckStmt->execute([$couponId]);
    $orderCount = $orderCheckStmt->fetchColumn();

    if ($orderCount > 0) {
        // Nếu đã được sử dụng, chúng ta không xóa mà chỉ vô hiệu hóa (status = 0)
        $stmt = $db->prepare("UPDATE coupons SET status = 0 WHERE coupon_id = ?");
        $success = $stmt->execute([$couponId]);
        $action = 'vô hiệu hóa';
    } else {
        // Nếu chưa được sử dụng, chúng ta có thể xóa hoàn toàn
        $stmt = $db->prepare("DELETE FROM coupons WHERE coupon_id = ?");
        $success = $stmt->execute([$couponId]);
        $action = 'xóa';
    }

    if (!$success) {
        throw new Exception('Không thể ' . $action . ' mã coupon');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " " . $action . " mã coupon ID: $couponId");

    echo json_encode([
        'success' => true,
        'message' => $action === 'vô hiệu hóa' ? 'Vô hiệu hóa mã coupon thành công (đã được sử dụng trong đơn hàng)' : 'Xóa mã coupon thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>