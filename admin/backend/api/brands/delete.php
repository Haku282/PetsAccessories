<?php
/**
 * API: Xóa thương hiệu
 * DELETE /admin/backend/api/brands/delete.php
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

    if (!$data || !isset($data['brand_id'])) {
        throw new Exception('ID thương hiệu không hợp lệ');
    }

    $brandId = (int)$data['brand_id'];

    // Kiểm tra thương hiệu tồn tại
    $stmt = $db->prepare("SELECT brand_id FROM brands WHERE brand_id = ?");
    $stmt->execute([$brandId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Thương hiệu không tồn tại');
    }

    // Kiểm tra xem có sản phẩm nào sử dụng thương hiệu này không
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE brand_id = ?");
    $stmt->execute([$brandId]);
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($productCount > 0) {
        throw new Exception('Không thể xóa thương hiệu này vì có ' . $productCount . ' sản phẩm đang sử dụng');
    }

    // Xóa thương hiệu
    $stmt = $db->prepare("DELETE FROM brands WHERE brand_id = ?");
    $stmt->execute([$brandId]);

    echo json_encode([
        'success' => true,
        'message' => 'Xóa thương hiệu thành công'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
