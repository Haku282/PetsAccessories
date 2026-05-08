<?php
/**
 * API: Thêm danh mục mới
 * POST /admin/backend/api/categories/add.php
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

    if (empty($data['category_name'])) {
        $errors[] = 'Tên danh mục không được để trống';
    } elseif (strlen($data['category_name']) > 255) {
        $errors[] = 'Tên danh mục không được vượt quá 255 ký tự';
    }

    if (empty($data['pet_type'])) {
        $errors[] = 'Loại thú cưng không được để trống';
    } elseif (!in_array($data['pet_type'], ['dog', 'cat', 'all'])) {
        $errors[] = 'Loại thú cưng không hợp lệ';
    }

    // Kiểm tra parent_id nếu có
    if (!empty($data['parent_id'])) {
        $stmt = $db->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $stmt->execute([(int)$data['parent_id']]);
        if ($stmt->rowCount() === 0) {
            $errors[] = 'Danh mục cha không tồn tại';
        }
    }

    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }

    // Kiểm tra tên danh mục đã tồn tại chưa
    $stmt = $db->prepare("SELECT category_id FROM categories WHERE category_name = ? AND parent_id IS ?");
    $stmt->execute([
        $data['category_name'],
        !empty($data['parent_id']) ? (int)$data['parent_id'] : null
    ]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Danh mục này đã tồn tại');
    }

    // Thêm danh mục
    $stmt = $db->prepare("
        INSERT INTO categories (category_name, pet_type, parent_id, status)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['category_name'],
        $data['pet_type'],
        !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
        isset($data['status']) ? (int)$data['status'] : 1
    ]);

    $categoryId = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Thêm danh mục thành công',
        'category_id' => (int)$categoryId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
