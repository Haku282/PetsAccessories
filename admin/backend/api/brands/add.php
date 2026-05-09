<?php
/**
 * API: Thêm thương hiệu mới
 * POST /admin/backend/api/brands/add.php
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

    if (!$data) {
        throw new Exception('Dữ liệu không hợp lệ');
    }

    // Validation
    $errors = [];

    if (empty($data['brand_name'])) {
        $errors[] = 'Tên thương hiệu không được để trống';
    } elseif (strlen($data['brand_name']) > 100) {
        $errors[] = 'Tên thương hiệu không được vượt quá 100 ký tự';
    }

    // Kiểm tra tên thương hiệu đã tồn tại chưa
    if (!empty($data['brand_name'])) {
        $stmt = $db->prepare("SELECT brand_id FROM brands WHERE brand_name = ?");
        $stmt->execute([$data['brand_name']]);
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Tên thương hiệu này đã tồn tại';
        }
    }

    // Validation description
    if (!empty($data['description']) && strlen($data['description']) > 1000) {
        $errors[] = 'Mô tả không được vượt quá 1000 ký tự';
    }

    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }

    // Thêm thương hiệu
    $stmt = $db->prepare("
        INSERT INTO brands (brand_name, brand_logo, description)
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([
        $data['brand_name'],
        isset($data['brand_logo']) ? $data['brand_logo'] : null,
        isset($data['description']) ? $data['description'] : null
    ]);

    $brandId = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Thêm thương hiệu thành công',
        'brand_id' => (int)$brandId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
