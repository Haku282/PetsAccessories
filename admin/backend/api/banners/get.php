<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Không có quyền truy cập']));
}

require_once __DIR__ . '/../../../config/database.php';

try {
    /** @var PDO $pdo */
    $id = (int)($_GET['id'] ?? 0);
    
    $stmt = $pdo->prepare("
        SELECT banner_id as id, title, image_url as image, link_url as link, status 
        FROM banners WHERE banner_id = ?
    ");
    $stmt->execute([$id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($banner) {
        echo json_encode(['success' => true, 'banner' => $banner]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Banner không tồn tại']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>