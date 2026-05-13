<?php
/**
 * API: Cập nhật thương hiệu
 * PUT /admin/backend/api/brands/update.php
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
        throw new Exception('Dữ liệu không hợp lệ');
    }

    $brandId = (int)$data['brand_id'];

    // Kiểm tra thương hiệu tồn tại
    $stmt = $db->prepare("SELECT brand_id FROM brands WHERE brand_id = ?");
    $stmt->execute([$brandId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Thương hiệu không tồn tại');
    }

    // Validation
    $errors = [];

    if (empty($data['brand_name'])) {
        $errors[] = 'Tên thương hiệu không được để trống';
    } elseif (strlen($data['brand_name']) > 100) {
        $errors[] = 'Tên thương hiệu không được vượt quá 100 ký tự';
    }

    // Kiểm tra tên thương hiệu đã tồn tại (trừ chính nó)
    if (!empty($data['brand_name'])) {
        $stmt = $db->prepare("SELECT brand_id FROM brands WHERE brand_name = ? AND brand_id != ?");
        $stmt->execute([$data['brand_name'], $brandId]);
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Tên thương hiệu này đã được sử dụng';
        }
    }

    // Validation description
    if (!empty($data['description']) && strlen($data['description']) > 1000) {
        $errors[] = 'Mô tả không được vượt quá 1000 ký tự';
    }

    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }

    // Get old brand data to check logo change
    $oldBrandStmt = $db->prepare("SELECT brand_logo FROM brands WHERE brand_id = ?");
    $oldBrandStmt->execute([$brandId]);
    $oldBrand = $oldBrandStmt->fetch(PDO::FETCH_ASSOC);

    // Delete old logo if changed
    if (!empty($data['brand_logo']) && !empty($oldBrand['brand_logo']) && $data['brand_logo'] !== $oldBrand['brand_logo']) {
        $uploadDir = __DIR__ . '/../../uploads/brands/';
        $oldLogoPath = $uploadDir . $oldBrand['brand_logo'];
        if (file_exists($oldLogoPath)) {
            unlink($oldLogoPath);
        }
    }

    // Cập nhật thương hiệu
    $stmt = $db->prepare("
        UPDATE brands 
        SET brand_name = ?, 
            brand_logo = ?,
            description = ?
        WHERE brand_id = ?
    ");
    
    $stmt->execute([
        $data['brand_name'],
        isset($data['brand_logo']) ? $data['brand_logo'] : $oldBrand['brand_logo'],
        isset($data['description']) ? $data['description'] : null,
        $brandId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật thương hiệu thành công'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
