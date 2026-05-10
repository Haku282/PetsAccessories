<?php
/**
 * API: Xóa khu vực giao hàng
 * DELETE /admin/backend/api/shipping_zones/delete.php
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

    // Kiểm tra xem khu vực giao hàng có được sử dụng trong đơn hàng nào không
    // Theo thiết kế đơn giản, chúng ta sẽ cho phép xóa trực tiếp
    // Trong thực tế, có thể cần kiểm tra xem có đơn hàng nào sử dụng khu vực này không
    
    // Xóa khu vực giao hàng
    $stmt = $db->prepare("DELETE FROM shipping_zones WHERE zone_id = ?");
    $success = $stmt->execute([$zoneId]);

    if (!$success) {
        throw new Exception('Không thể xóa khu vực giao hàng');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " xóa khu vực giao hàng ID: $zoneId");

    echo json_encode([
        'success' => true,
        'message' => 'Xóa khu vực giao hàng thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>