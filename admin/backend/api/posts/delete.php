<?php
/**
 * API: Xóa bài viết
 * DELETE /admin/backend/api/posts/delete.php
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['post_id']) || !is_numeric($data['post_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $postId = (int)$data['post_id'];

    $checkStmt = $db->prepare("SELECT post_id FROM posts WHERE post_id = ?");
    $checkStmt->execute([$postId]);
    if ($checkStmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài viết']);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM posts WHERE post_id = ?");
    $stmt->execute([$postId]);

    echo json_encode([
        'success' => true,
        'message' => 'Xóa bài viết thành công'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>