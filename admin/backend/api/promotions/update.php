<?php
/**
 * API: Cập nhật khuyến mãi
 * PUT /admin/backend/api/promotions/update.php
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
    if (!isset($data['promotion_id']) || !is_numeric($data['promotion_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID khuyến mãi không hợp lệ']);
        exit;
    }

    $promotionId = (int)$data['promotion_id'];

    // Kiểm tra khuyến mãi tồn tại
    $checkStmt = $db->prepare("SELECT promotion_id FROM promotions WHERE promotion_id = ?");
    $checkStmt->execute([$promotionId]);
    if ($checkStmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy khuyến mãi']);
        exit;
    }

    // Validate dữ liệu
    $errors = [];
    if (isset($data['promotion_name']) && empty($data['promotion_name'])) {
        $errors['promotion_name'] = 'Tên khuyến mãi là bắt buộc';
    }
    if (isset($data['discount_percent']) && (!is_numeric($data['discount_percent']) || $data['discount_percent'] < 0 || $data['discount_percent'] > 100)) {
        $errors['discount_percent'] = 'Giá trị giảm phải là số từ 0 đến 100';
    }
    if (isset($data['start_date']) && !empty($data['start_date']) && !strtotime($data['start_date'])) {
        $errors['start_date'] = 'Ngày bắt đầu không hợp lệ';
    }
    if (isset($data['end_date']) && !empty($data['end_date']) && !strtotime($data['end_date'])) {
        $errors['end_date'] = 'Ngày kết thúc không hợp lệ';
    }
    if (isset($data['start_date'], $data['end_date']) && !empty($data['start_date']) && !empty($data['end_date']) && strtotime($data['start_date']) > strtotime($data['end_date'])) {
        $errors['date_range'] = 'Ngày bắt đầu phải trước ngày kết thúc';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Chuẩn bị dữ liệu
    $promotion_name = !empty($data['promotion_name']) ? $data['promotion_name'] : null;
    $description = !empty($data['description']) ? $data['description'] : null;
    $discount_percent = isset($data['discount_percent']) ? (int)$data['discount_percent'] : null;
    $start_date = !empty($data['start_date']) ? $data['start_date'] : null;
    $end_date = !empty($data['end_date']) ? $data['end_date'] : null;
    $status = isset($data['status']) ? (int)$data['status'] : null;

    // Xây dựng query cập nhật động
    $updateFields = [];
    $params = [];

    if ($promotion_name !== null) {
        $updateFields[] = "promotion_name = ?";
        $params[] = $promotion_name;
    }
    if ($description !== null) {
        $updateFields[] = "description = ?";
        $params[] = $description;
    }
    if ($discount_percent !== null) {
        $updateFields[] = "discount_percent = ?";
        $params[] = $discount_percent;
    }
    if ($start_date !== null) {
        $updateFields[] = "start_date = ?";
        $params[] = $start_date;
    }
    if ($end_date !== null) {
        $updateFields[] = "end_date = ?";
        $params[] = $end_date;
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

    $params[] = $promotionId;
    $sql = "UPDATE promotions SET " . implode(", ", $updateFields) . " WHERE promotion_id = ?";

    $stmt = $db->prepare($sql);
    $success = $stmt->execute($params);

    if (!$success) {
        throw new Exception('Không thể cập nhật khuyến mãi');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " cập nhật khuyến mãi ID: $promotionId");

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật khuyến mãi thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>