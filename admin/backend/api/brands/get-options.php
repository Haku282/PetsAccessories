<?php
/**
 * API: Lấy danh sách thương hiệu cho dropdown
 * GET /admin/backend/api/brands/get-options.php
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
    
    $stmt = $db->query("
        SELECT brand_id, brand_name, brand_logo 
        FROM brands 
        ORDER BY brand_name
    ");
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedBrands = [];
    foreach ($brands as $brand) {
        $formattedBrands[] = [
            'id' => (int)$brand['brand_id'],
            'name' => $brand['brand_name'],
            'logo' => $brand['brand_logo']
        ];
    }

    echo json_encode([
        'success' => true,
        'brands' => $formattedBrands
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
