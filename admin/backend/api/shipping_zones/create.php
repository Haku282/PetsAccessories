<?php
/**
 * API: Thêm khu vực giao hàng mới
 * POST /admin/backend/api/shipping_zones/create.php
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
    if (empty($data['zone_name'])) {
        $errors['zone_name'] = 'Tên khu vực giao hàng là bắt buộc';
    }
    if (!isset($data['shipping_fee']) || $data['shipping_fee'] === '' || !is_numeric($data['shipping_fee']) || $data['shipping_fee'] < 0) {
        $errors['shipping_fee'] = 'Phí vận chuyển phải là số dương';
    }
    if (empty($data['estimated_delivery'])) {
        $errors['estimated_delivery'] = 'Thời gian giao hàng dự kiến là bắt buộc';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Chuẩn bị dữ liệu
    $zone_name = $data['zone_name'];
    $shipping_fee = (float)$data['shipping_fee'];
    $estimated_delivery = $data['estimated_delivery'];
    $status = isset($data['status']) ? (int)$data['status'] : 1;

    // Thêm khu vực giao hàng vào database
    $stmt = $db->prepare("
        INSERT INTO shipping_zones (zone_name, shipping_fee, estimated_delivery, status, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $success = $stmt->execute([
        $zone_name,
        $shipping_fee,
        $estimated_delivery,
        $status
    ]);

    if (!$success) {
        throw new Exception('Không thể thêm khu vực giao hàng');
    }

    $zoneId = $db->lastInsertId();

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " thêm khu vực giao hàng ID: $zoneId");

    echo json_encode([
        'success' => true,
        'message' => 'Thêm khu vực giao hàng thành công',
        'zone_id' => (int)$zoneId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
