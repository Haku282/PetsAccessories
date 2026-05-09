<?php
/**
 * API: Cập nhật danh mục
 * PUT /admin/backend/api/categories/update.php
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
        throw new Exception('Dữ liệu không hợp lệ');
    }

    $categoryId = (int)$data['category_id'];

    // Kiểm tra danh mục tồn tại
    $stmt = $db->prepare("SELECT category_id FROM categories WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Danh mục không tồn tại');
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

    // Kiểm tra parent_id nếu có thay đổi
    if (isset($data['parent_id']) && $data['parent_id'] !== '') {
        $parentId = (int)$data['parent_id'];
        
        // Không được set danh mục con của chính nó làm parent
        if ($parentId === $categoryId) {
            $errors[] = 'Không thể set danh mục con của chính nó làm danh mục cha';
        }

        // Kiểm tra parent tồn tại
        $stmt = $db->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $stmt->execute([$parentId]);
        if ($stmt->rowCount() === 0) {
            $errors[] = 'Danh mục cha không tồn tại';
        }
    }

    if (!empty($errors)) {
        throw new Exception(implode(', ', $errors));
    }

    // Cập nhật danh mục
    $stmt = $db->prepare("
        UPDATE categories 
        SET category_name = ?, 
            pet_type = ?, 
            parent_id = ?,
            status = ?
        WHERE category_id = ?
    ");
    
    $stmt->execute([
        $data['category_name'],
        $data['pet_type'],
        isset($data['parent_id']) && $data['parent_id'] !== '' ? (int)$data['parent_id'] : null,
        isset($data['status']) ? (int)$data['status'] : 1,
        $categoryId
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật danh mục thành công'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
