<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Không có quyền truy cập']));
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $uploadDir = __DIR__ . '/../../../../uploads/banners/';
    $id = (int)($_POST['id'] ?? 0);

    $checkStmt = $pdo->prepare("SELECT image_url FROM banners WHERE banner_id = ?");
    $checkStmt->execute([$id]);
    $banner = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($banner) {
        // Xóa file vật lý
        if ($banner['image_url'] && file_exists($uploadDir . $banner['image_url'])) {
            unlink($uploadDir . $banner['image_url']);
        }

        $stmt = $pdo->prepare("DELETE FROM banners WHERE banner_id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Đã xóa banner thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Banner không tồn tại']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>