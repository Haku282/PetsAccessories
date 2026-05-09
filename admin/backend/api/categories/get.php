<?php
/**
 * API: Lấy chi tiết danh mục
 * GET /admin/backend/api/categories/get.php?id=<category_id>
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
    $db = $pdo;
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        throw new Exception('ID danh mục không hợp lệ');
    }

    $stmt = $db->prepare("
        SELECT c.*, parent.category_name as parent_name
        FROM categories c
        LEFT JOIN categories parent ON c.parent_id = parent.category_id
        WHERE c.category_id = ?
    ");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        throw new Exception('Danh mục không tồn tại');
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'category_id' => (int)$category['category_id'],
            'category_name' => $category['category_name'],
            'parent_id' => $category['parent_id'] ? (int)$category['parent_id'] : null,
            'parent_name' => $category['parent_name'],
            'pet_type' => $category['pet_type'],
            'status' => (int)$category['status']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
