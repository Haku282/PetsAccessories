<?php
/**
 * API: Lấy chi tiết mã giảm giá
 * GET /admin/backend/api/coupons/get.php?id=1
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
    $db = $pdo;
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID coupon không hợp lệ']);
        exit;
    }

    $stmt = $db->prepare('SELECT * FROM coupons WHERE coupon_id = ?');
    $stmt->execute([$id]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy mã coupon']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'coupon_id' => (int)$coupon['coupon_id'],
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => (float)$coupon['discount_value'],
            'min_order_value' => (float)$coupon['min_order_value'],
            'max_discount' => $coupon['max_discount'] !== null ? (float)$coupon['max_discount'] : null,
            'usage_limit' => $coupon['usage_limit'] !== null ? (int)$coupon['usage_limit'] : null,
            'used_count' => (int)$coupon['used_count'],
            'expiry_date' => $coupon['expiry_date'],
            'status' => (int)$coupon['status'],
            'created_at' => $coupon['created_at']
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
