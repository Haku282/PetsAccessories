<?php
/**
 * API: Lấy danh sách danh mục cho dropdown
 * GET /admin/backend/api/categories/get-options.php
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
    
    $excludeId = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;

    // Lấy danh mục gốc (parent categories)
    $stmt = $db->query("
        SELECT category_id, category_name, pet_type
        FROM categories 
        WHERE parent_id IS NULL AND status = 1
        ORDER BY category_name
    ");
    $parentCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy tất cả danh mục (có cấu trúc phân cấp)
    $stmt = $db->query("
        SELECT c.category_id, c.category_name, c.parent_id, c.pet_type,
               parent.category_name as parent_name
        FROM categories c
        LEFT JOIN categories parent ON c.parent_id = parent.category_id
        WHERE c.status = 1 
        ORDER BY c.parent_id, c.category_name
    ");
    $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu với cấu trúc phân cấp
    $formattedCategories = [];
    foreach ($allCategories as $cat) {
        if ($cat['category_id'] != $excludeId) {
            $formattedCategories[] = [
                'id' => (int)$cat['category_id'],
                'name' => $cat['category_name'],
                'parent_id' => $cat['parent_id'] ? (int)$cat['parent_id'] : null,
                'parent_name' => $cat['parent_name'],
                'pet_type' => $cat['pet_type'],
                'level' => $cat['parent_id'] ? 1 : 0,
                'display_name' => ($cat['parent_id'] ? '— ' : '') . $cat['category_name']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'parent_categories' => $parentCategories,
        'all_categories' => $formattedCategories
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
