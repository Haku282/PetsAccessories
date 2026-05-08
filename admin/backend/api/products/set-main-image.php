<?php
/**
 * API: Đặt ảnh chính cho sản phẩm
 * POST /admin/backend/api/products/set-main-image.php
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

    // Bắt đầu transaction
    $db->beginTransaction();

    try {
        // Lấy product_id từ image
        $stmt = $db->prepare("SELECT product_id FROM product_images WHERE image_id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            throw new Exception('Ảnh không tìm thấy');
        }

        $productId = $image['product_id'];

        // Hủy tất cả ảnh chính
        $stmt = $db->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Đặt ảnh hiện tại làm chính
        $stmt = $db->prepare("UPDATE product_images SET is_main = 1 WHERE image_id = ?");
        $stmt->execute([$imageId]);

        // Cập nhật thumbnail của product
        $stmt = $db->prepare("SELECT image_url FROM product_images WHERE image_id = ?");
        $stmt->execute([$imageId]);
        $mainImage = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mainImage) {
            $stmt = $db->prepare("UPDATE products SET thumbnail = ? WHERE product_id = ?");
            $stmt->execute([$mainImage['image_url'], $productId]);
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật ảnh chính thành công'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
