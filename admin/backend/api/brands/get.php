<?php
/**
 * API: Lấy chi tiết thương hiệu
 * GET /admin/backend/api/brands/get.php?id=<brand_id>
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
        throw new Exception('ID thương hiệu không hợp lệ');
    }

    $stmt = $db->prepare("SELECT * FROM brands WHERE brand_id = ?");
    $stmt->execute([$id]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$brand) {
        throw new Exception('Thương hiệu không tồn tại');
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'brand_id' => (int)$brand['brand_id'],
            'brand_name' => $brand['brand_name'],
            'brand_logo' => $brand['brand_logo'],
            'description' => $brand['description']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
