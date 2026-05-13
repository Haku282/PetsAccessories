<?php
/**
 * API: Tạo banner mới
 * POST /admin/backend/api/banners/create.php
 * Body: JSON {title, image_url, link_url, status}
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

    $title = trim($data['title'] ?? '');
    $imageUrl = trim($data['image_url'] ?? '');
    $linkUrl = trim($data['link_url'] ?? '');
    $status = isset($data['status']) ? (int)$data['status'] : 1;

    // Validation
    if (empty($title)) {
        throw new Exception('Tiêu đề banner không được để trống');
    }

    if (empty($imageUrl)) {
        throw new Exception('Hình ảnh không được để trống');
    }

    // Insert to database
    $stmt = $pdo->prepare("
        INSERT INTO banners (title, image_url, link_url, status)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$title, $imageUrl, $linkUrl, $status]);
    $bannerId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Thêm banner thành công',
        'banner_id' => (int)$bannerId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>