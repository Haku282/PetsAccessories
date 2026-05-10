<?php
/**
 * API: Cập nhật khu vực giao hàng
 * PUT /admin/backend/api/shipping_zones/update.php
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
    if (!isset($data['zone_id']) || !is_numeric($data['zone_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID khu vực giao hàng không hợp lệ']);
        exit;
    }

    $zoneId = (int)$data['zone_id'];

    // Kiểm tra khu vực giao hàng tồn tại
    $checkStmt = $db->prepare("SELECT zone_id FROM shipping_zones WHERE zone_id = ?");
    $checkStmt->execute([$zoneId]);
    if ($checkStmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy khu vực giao hàng']);
        exit;
    }

    // Validate dữ liệu
    $errors = [];
    if (isset($data['zone_name']) && empty($data['zone_name'])) {
        $errors['zone_name'] = 'Tên khu vực giao hàng là bắt buộc';
    }
    if (isset($data['shipping_fee']) && (!is_numeric($data['shipping_fee']) || $data['shipping_fee'] < 0)) {
        $errors['shipping_fee'] = 'Phí vận chuyển phải là số dương';
    }
    if (isset($data['estimated_delivery']) && empty($data['estimated_delivery'])) {
        $errors['estimated_delivery'] = 'Thời gian giao hàng dự kiến là bắt buộc';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Chuẩn bị dữ liệu
    $zone_name = isset($data['zone_name']) ? $data['zone_name'] : null;
    $shipping_fee = isset($data['shipping_fee']) ? (float)$data['shipping_fee'] : null;
    $estimated_delivery = isset($data['estimated_delivery']) ? $data['estimated_delivery'] : null;
    $status = isset($data['status']) ? (int)$data['status'] : null;

    // Xây dựng query cập nhật động
    $updateFields = [];
    $params = [];

    if ($zone_name !== null) {
        $updateFields[] = "zone_name = ?";
        $params[] = $zone_name;
    }
    if ($shipping_fee !== null) {
        $updateFields[] = "shipping_fee = ?";
        $params[] = $shipping_fee;
    }
    if ($estimated_delivery !== null) {
        $updateFields[] = "estimated_delivery = ?";
        $params[] = $estimated_delivery;
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

    $params[] = $zoneId;
    $sql = "UPDATE shipping_zones SET " . implode(", ", $updateFields) . " WHERE zone_id = ?";

    $stmt = $db->prepare($sql);
    $success = $stmt->execute($params);

    if (!$success) {
        throw new Exception('Không thể cập nhật khu vực giao hàng');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " cập nhật khu vực giao hàng ID: $zoneId");

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật khu vực giao hàng thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>