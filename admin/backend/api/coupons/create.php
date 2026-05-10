<?php
/**
 * API: Tạo mã giảm giá mới
 * POST /admin/backend/api/coupons/create.php
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

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    // Validate dữ liệu
    $errors = [];
    if (empty($data['code'])) {
        $errors['code'] = 'Mã coupon là bắt buộc';
    } else {
        // Kiểm tra mã coupon đã tồn tại
        $checkStmt = $db->prepare("SELECT coupon_id FROM coupons WHERE code = ?");
        $checkStmt->execute([$data['code']]);
        if ($checkStmt->fetchColumn()) {
            $errors['code'] = 'Mã coupon đã tồn tại';
        }
    }
    if (empty($data['discount_type']) || !in_array($data['discount_type'], ['percentage', 'fixed'])) {
        $errors['discount_type'] = 'Loại giảm giá không hợp lệ';
    }
    if (empty($data['discount_value']) || !is_numeric($data['discount_value']) || $data['discount_value'] < 0) {
        $errors['discount_value'] = 'Giá trị giảm phải là số dương';
    }
    if (!empty($data['min_order_value']) && (!is_numeric($data['min_order_value']) || $data['min_order_value'] < 0)) {
        $errors['min_order_value'] = 'Giá trị đơn hàng tối thiểu phải là số dương';
    }
    if (!empty($data['max_discount']) && (!is_numeric($data['max_discount']) || $data['max_discount'] < 0)) {
        $errors['max_discount'] = 'Giảm giá tối đa phải là số dương';
    }
    if (!empty($data['usage_limit']) && (!is_numeric($data['usage_limit']) || $data['usage_limit'] < 0)) {
        $errors['usage_limit'] = 'Giới hạn sử dụng phải là số nguyên dương';
    }
    if (!empty($data['expiry_date']) && !strtotime($data['expiry_date'])) {
        $errors['expiry_date'] = 'Ngày hết hạn không hợp lệ';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Chuẩn bị dữ liệu
    $code = $data['code'];
    $discount_type = $data['discount_type'];
    $discount_value = (float)$data['discount_value'];
    $min_order_value = !empty($data['min_order_value']) ? (float)$data['min_order_value'] : 0.00;
    $max_discount = !empty($data['max_discount']) ? (float)$data['max_discount'] : null;
    $usage_limit = !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null;
    $expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
    $status = isset($data['status']) ? (int)$data['status'] : 1;

    // Thêm coupon vào database
    $stmt = $db->prepare("
        INSERT INTO coupons (code, discount_type, discount_value, min_order_value, max_discount, usage_limit, expiry_date, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $success = $stmt->execute([
        $code,
        $discount_type,
        $discount_value,
        $min_order_value,
        $max_discount,
        $usage_limit,
        $expiry_date,
        $status
    ]);

    if (!$success) {
        throw new Exception('Không thể thêm mã coupon');
    }

    $couponId = $db->lastInsertId();

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " thêm mã coupon ID: $couponId");

    echo json_encode([
        'success' => true,
        'message' => 'Thêm mã coupon thành công',
        'coupon_id' => (int)$couponId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>