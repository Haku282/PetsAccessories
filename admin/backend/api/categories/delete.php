<?php
/**
 * API: Xóa danh mục
 * DELETE /admin/backend/api/categories/delete.php
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
    
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['category_id'])) {
        throw new Exception('ID danh mục không hợp lệ');
    }

    $categoryId = (int)$data['category_id'];

    // Kiểm tra danh mục tồn tại
    $stmt = $db->prepare("SELECT category_id FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Danh mục không tồn tại');
    }

    // Kiểm tra xem có sản phẩm nào sử dụng danh mục này không
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($productCount > 0) {
        throw new Exception('Không thể xóa danh mục này vì có ' . $productCount . ' sản phẩm đang sử dụng');
    }

    // Kiểm tra xem có danh mục con nào không
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM categories WHERE parent_id = ?");
    $stmt->execute([$categoryId]);
    $childCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($childCount > 0) {
        throw new Exception('Không thể xóa danh mục này vì có ' . $childCount . ' danh mục con');
    }

    // Xóa danh mục
    $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);

    echo json_encode([
        'success' => true,
        'message' => 'Xóa danh mục thành công'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
