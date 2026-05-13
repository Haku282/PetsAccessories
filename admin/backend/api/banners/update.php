<?php
/**
 * API: Cập nhật banner
 * POST /admin/backend/api/banners/update.php
 * Body: JSON {id, title, image_url, link_url, status}
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

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Dữ liệu không hợp lệ');
    }

    $id = (int)($data['id'] ?? 0);
    $title = trim($data['title'] ?? '');
    $imageUrl = trim($data['image_url'] ?? '');
    $linkUrl = trim($data['link_url'] ?? '');
    $status = isset($data['status']) ? (int)$data['status'] : 1;

    if ($id <= 0) {
        throw new Exception('ID banner không hợp lệ');
    }

    // Check banner exists
    $checkStmt = $pdo->prepare("SELECT image_url FROM banners WHERE banner_id = ?");
    $checkStmt->execute([$id]);
    $oldBanner = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldBanner) {
        throw new Exception('Banner không tồn tại');
    }

    // Validation
    if (empty($title)) {
        throw new Exception('Tiêu đề banner không được để trống');
    }

    // If image changed, delete old image
    $newImageUrl = $imageUrl;
    if ($imageUrl !== $oldBanner['image_url']) {
        $uploadDir = __DIR__ . '/../../uploads/banners/';
        $oldImagePath = $uploadDir . $oldBanner['image_url'];
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }

    // Update to database
    $stmt = $pdo->prepare("
        UPDATE banners 
        SET title = ?, image_url = ?, link_url = ?, status = ? 
        WHERE banner_id = ?
    ");
    
    $stmt->execute([$title, $newImageUrl, $linkUrl, $status, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật banner thành công'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
