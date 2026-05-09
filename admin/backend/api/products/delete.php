<?php
/**
 * API: Xóa sản phẩm
 * DELETE /admin/backend/api/products/delete.php?id=1
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
    
    // Lấy product ID
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;

    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID không hợp lệ']);
        exit;
    }

    // Kiểm tra sản phẩm có tồn tại
    $product = getProductById($db, $productId);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tìm thấy']);
        exit;
    }

    // Bắt đầu transaction
    $db->beginTransaction();

    try {
        // Xóa ảnh sản phẩm từ database (file sẽ được xử lý riêng)
        $stmt = $db->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Xóa sản phẩm
        $stmt = $db->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);

        $db->commit();

        // Log activity
        error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " xóa sản phẩm ID: $productId");

        echo json_encode([
            'success' => true,
            'message' => 'Xóa sản phẩm thành công'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
