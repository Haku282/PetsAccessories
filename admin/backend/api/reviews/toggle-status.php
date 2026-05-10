<?php
/**
 * API: Ẩn/hiện đánh giá & bình luận
 * PUT /admin/backend/api/reviews/toggle-status.php
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
    if (!isset($data['review_id']) || !is_numeric($data['review_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID đánh giá không hợp lệ']);
        exit;
    }

    $reviewId = (int)$data['review_id'];
    $status = isset($data['status']) ? (int)$data['status'] : 0;

    // Kiểm tra đánh giá tồn tại
    $checkStmt = $db->prepare("SELECT review_id FROM reviews WHERE review_id = ?");
    $checkStmt->execute([$reviewId]);
    if ($checkStmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đánh giá']);
        exit;
    }

    // Cập nhật trạng thái đánh giá
    $stmt = $db->prepare("UPDATE reviews SET status = ? WHERE review_id = ?");
    $success = $stmt->execute([$status, $reviewId]);

    if (!$success) {
        throw new Exception('Không thể cập nhật trạng thái đánh giá');
    }

    // Log activity
    $action = $status == 1 ? 'hiện' : 'ẩn';
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " $action đánh giá ID: $reviewId");

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật trạng thái đánh giá thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>