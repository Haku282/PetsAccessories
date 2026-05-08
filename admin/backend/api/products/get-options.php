<?php
/**
 * API: Lấy danh sách categories và brands
 * GET /admin/backend/api/products/get-options.php
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

require_once __DIR__ . '/../../../../backend/config/database.php';
require_once __DIR__ . '/../../utils/products_helper.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    
    $categories = getAllCategories($db);
    $brands = getAllBrands($db);

    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'brands' => $brands
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
