<?php
/**
 * API: Xóa khuyến mãi
 * DELETE /admin/backend/api/promotions/delete.php
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

    // Xóa khuyến mãi (cập nhật status = 0 hoặc xóa thực tế? Theo yêu cầu, chúng ta có thể xóa mềm hoặc xóa cứng.
    // Vì trong bảng promotions có trường status, chúng ta có thể thực hiện xóa mềm bằng cách設定 status = 0.
    // Tuy nhiên, chức năng "xóa" thường là xóa vĩnh viễn. Kiểm chuẩn: trong trường hợp này, chúng ta sẽ xóa mềm để duy trì dữ liệu lịch sử.
    // Nhưng để đơn giản và tuân theo các chức năng khác ( như products, users) thường là xóa mềm, chúng ta sẽ cập nhật status = 0.
    $stmt = $db->prepare("UPDATE promotions SET status = 0 WHERE promotion_id = ?");
    $success = $stmt->execute([$promotionId]);

    if (!$success) {
        throw new Exception('Không thể xóa khuyến mãi');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " xóa khuyến mãi ID: $promotionId");

    echo json_encode([
        'success' => true,
        'message' => 'Xóa khuyến mãi thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>