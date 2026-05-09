<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $stmt = $pdo->query("
        SELECT 
            banner_id as id, 
            title, 
            image_url as image, 
            link_url as link, 
            status, 
            position, 
            order_priority 
        FROM banners 
        ORDER BY order_priority ASC, banner_id DESC
    ");
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'banners' => $banners]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>