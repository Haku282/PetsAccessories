<?php
/**
 * API: Cập nhật mã giảm giá
 * PUT /admin/backend/api/coupons/update.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận PUT request']);
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

    // Validate dữ liệu
    $errors = [];
    if (isset($data['code']) && empty($data['code'])) {
        $errors['code'] = 'Mã coupon là bắt buộc';
    } elseif (isset($data['code'])) {
        // Kiểm tra mã coupon đã tồn tại (trừ chính nó)
        $checkStmt = $db->prepare("SELECT coupon_id FROM coupons WHERE code = ? AND coupon_id != ?");
        $checkStmt->execute([$data['code'], $couponId]);
        if ($checkStmt->fetchColumn()) {
            $errors['code'] = 'Mã coupon đã tồn tại';
        }
    }
    if (isset($data['discount_type']) && !in_array($data['discount_type'], ['percentage', 'fixed'])) {
        $errors['discount_type'] = 'Loại giảm giá không hợp lệ';
    }
    if (isset($data['discount_value']) && (!is_numeric($data['discount_value']) || $data['discount_value'] < 0)) {
        $errors['discount_value'] = 'Giá trị giảm phải là số dương';
    }
    if (isset($data['min_order_value']) && (!is_numeric($data['min_order_value']) || $data['min_order_value'] < 0)) {
        $errors['min_order_value'] = 'Giá trị đơn hàng tối thiểu phải là số dương';
    }
    if (isset($data['max_discount']) && (!is_numeric($data['max_discount']) || $data['max_discount'] < 0)) {
        $errors['max_discount'] = 'Giảm giá tối đa phải là số dương';
    }
    if (isset($data['usage_limit']) && (!is_numeric($data['usage_limit']) || $data['usage_limit'] < 0)) {
        $errors['usage_limit'] = 'Giới hạn sử dụng phải là số nguyên dương';
    }
    if (isset($data['expiry_date']) && !empty($data['expiry_date']) && !strtotime($data['expiry_date'])) {
        $errors['expiry_date'] = 'Ngày hết hạn không hợp lệ';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Chuẩn bị dữ liệu
    $code = isset($data['code']) ? $data['code'] : null;
    $discount_type = isset($data['discount_type']) ? $data['discount_type'] : null;
    $discount_value = isset($data['discount_value']) ? (float)$data['discount_value'] : null;
    $min_order_value = isset($data['min_order_value']) ? (float)$data['min_order_value'] : null;
    $max_discount = isset($data['max_discount']) ? (float)$data['max_discount'] : null;
    $usage_limit = isset($data['usage_limit']) ? (int)$data['usage_limit'] : null;
    $expiry_date = isset($data['expiry_date']) ? $data['expiry_date'] : null;
    $status = isset($data['status']) ? (int)$data['status'] : null;

    // Xây dựng query cập nhật động
    $updateFields = [];
    $params = [];

    if ($code !== null) {
        $updateFields[] = "code = ?";
        $params[] = $code;
    }
    if ($discount_type !== null) {
        $updateFields[] = "discount_type = ?";
        $params[] = $discount_type;
    }
    if ($discount_value !== null) {
        $updateFields[] = "discount_value = ?";
        $params[] = $discount_value;
    }
    if ($min_order_value !== null) {
        $updateFields[] = "min_order_value = ?";
        $params[] = $min_order_value;
    }
    if ($max_discount !== null) {
        $updateFields[] = "max_discount = ?";
        $params[] = $max_discount;
    }
    if ($usage_limit !== null) {
        $updateFields[] = "usage_limit = ?";
        $params[] = $usage_limit;
    }
    if ($expiry_date !== null) {
        $updateFields[] = "expiry_date = ?";
        $params[] = $expiry_date;
    }
    if ($status !== null) {
        $updateFields[] = "status = ?";
        $params[] = $status;
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có trường nào để cập nhật']);
        exit;
    }

    $params[] = $couponId;
    $sql = "UPDATE coupons SET " . implode(", ", $updateFields) . " WHERE coupon_id = ?";

    $stmt = $db->prepare($sql);
    $success = $stmt->execute($params);

    if (!$success) {
        throw new Exception('Không thể cập nhật mã coupon');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " cập nhật mã coupon ID: $couponId");

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật mã coupon thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>