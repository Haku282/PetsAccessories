<?php
/**
 * API: Tạo khuyến mãi mới
 * POST /admin/backend/api/promotions/create.php
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
    if (empty($data['promotion_name'])) {
        $errors['promotion_name'] = 'Tên khuyến mãi là bắt buộc';
    }
    if (empty($data['discount_percent']) || !is_numeric($data['discount_percent']) || $data['discount_percent'] < 0 || $data['discount_percent'] > 100) {
        $errors['discount_percent'] = 'Giá trị giảm phải là số từ 0 đến 100';
    }
    if (!empty($data['start_date']) && !strtotime($data['start_date'])) {
        $errors['start_date'] = 'Ngày bắt đầu không hợp lệ';
    }
    if (!empty($data['end_date']) && !strtotime($data['end_date'])) {
        $errors['end_date'] = 'Ngày kết thúc không hợp lệ';
    }
    if (!empty($data['start_date']) && !empty($data['end_date']) && strtotime($data['start_date']) > strtotime($data['end_date'])) {
        $errors['date_range'] = 'Ngày bắt đầu phải trước ngày kết thúc';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Chuẩn bị dữ liệu
    $promotion_name = $data['promotion_name'];
    $description = !empty($data['description']) ? $data['description'] : null;
    $discount_percent = (int)$data['discount_percent'];
    $start_date = !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = !empty($data['end_date']) ? $data['end_date'] : null;
    $status = isset($data['status']) ? (int)$data['status'] : 1;

    // Thêm khuyến mãi vào database
    $stmt = $db->prepare("
        INSERT INTO promotions (promotion_name, description, discount_percent, start_date, end_date, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $success = $stmt->execute([
        $promotion_name,
        $description,
        $discount_percent,
        $start_date,
        $end_date,
        $status
    ]);

    if (!$success) {
        throw new Exception('Không thể thêm khuyến mãi');
    }

    $promotionId = $db->lastInsertId();

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " thêm khuyến mãi ID: $promotionId");

    echo json_encode([
        'success' => true,
        'message' => 'Thêm khuyến mãi thành công',
        'promotion_id' => (int)$promotionId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>