<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php'; 

header('Content-Type: application/json');

// Xóa khoảng trắng thừa ở mã code
$code = trim($_POST['code'] ?? '');
$subtotal = floatval($_POST['subtotal'] ?? 0);

if (empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập mã giảm giá.']);
    exit;
}

if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 1 LIMIT 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá không hợp lệ hoặc không tồn tại.']);
            exit;
        }

        $now = date('Y-m-d H:i:s');
        if (!empty($coupon['expiry_date']) && $coupon['expiry_date'] < $now) {
            echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá đã hết hạn sử dụng.']);
            exit;
        }

        if (!empty($coupon['usage_limit']) && intval($coupon['used_count']) >= intval($coupon['usage_limit'])) {
            echo json_encode(['status' => 'error', 'message' => 'Mã giảm giá đã hết lượt sử dụng.']);
            exit;
        }

        if (!empty($coupon['min_order_value']) && $subtotal < floatval($coupon['min_order_value'])) {
            echo json_encode(['status' => 'error', 'message' => 'Đơn hàng chưa đạt ' . number_format($coupon['min_order_value'], 0, ',', '.') . 'đ để sử dụng mã này.']);
            exit;
        }

        $discount = 0;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = $subtotal * (floatval($coupon['discount_value']) / 100);
            if (!empty($coupon['max_discount']) && floatval($coupon['max_discount']) > 0) {
                if ($discount > floatval($coupon['max_discount'])) {
                    $discount = floatval($coupon['max_discount']);
                }
            }
        } else {
            $discount = floatval($coupon['discount_value']);
        }

        // Đảm bảo số tiền giảm không vượt quá tổng tiền hàng
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        $_SESSION['applied_coupon'] = [
            'code' => $code,
            'discount_amount' => $discount
        ];

        echo json_encode([
            'status' => 'success',
            'message' => 'Áp dụng mã giảm giá thành công!',
            'discount' => $discount,
            'code' => $code
        ]);
        
    } catch (PDOException $e) {
        // Trả về lỗi nếu database có vấn đề
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: Không thể áp dụng mã lúc này.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Không thể kết nối cơ sở dữ liệu.']);
}