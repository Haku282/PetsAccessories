<?php
/**
 * API: Xóa ảnh sản phẩm
 * POST /admin/backend/api/products/delete-image.php
 */

header('Content-Type: application/json');

// Chỉ gọi session_start() nếu session chưa được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST request']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';
require_once __DIR__ . '/../../utils/products_helper.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $imageId = isset($data['image_id']) ? (int)$data['image_id'] : 0;

    if (empty($imageId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Image ID không hợp lệ']);
        exit;
    }

    // Lấy thông tin ảnh
    $stmt = $db->prepare("SELECT image_url FROM product_images WHERE image_id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$image) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Ảnh không tìm thấy']);
        exit;
    }

    // Xóa file khỏi server
    $uploadDir = __DIR__ . '/../../../../frontend/public/uploads/products/';
    $fileName = basename($image['image_url']);
    $filePath = $uploadDir . $fileName;

    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Xóa từ database
    $stmt = $db->prepare("DELETE FROM product_images WHERE image_id = ?");
    $stmt->execute([$imageId]);

    echo json_encode([
        'success' => true,
        'message' => 'Xóa ảnh thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
