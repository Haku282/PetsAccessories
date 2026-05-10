<?php
/**
 * API: Xóa trang
 * DELETE /admin/backend/api/pages/delete.php
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

    if (!$data || !isset($data['page_id']) || !is_numeric($data['page_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID trang không hợp lệ']);
        exit;
    }

    $pageId = (int)$data['page_id'];

    // Kiểm tra trang tồn tại
    $checkStmt = $db->prepare("SELECT page_id FROM pages WHERE page_id = ?");
    $checkStmt->execute([$pageId]);
    if ($checkStmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy trang']);
        exit;
    }

    // Xóa trang
    $stmt = $db->prepare("DELETE FROM pages WHERE page_id = ?");
    $success = $stmt->execute([$pageId]);

    if (!$success) {
        throw new Exception('Không thể xóa trang');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " xóa trang ID: $pageId");

    echo json_encode([
        'success' => true,
        'message' => 'Xóa trang thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>