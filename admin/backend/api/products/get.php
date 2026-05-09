<?php
/**
 * API: Lấy chi tiết sản phẩm
 * GET /admin/backend/api/products/get.php?id=1
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

require_once __DIR__ . '/../../../../backend/config/database.php';
require_once __DIR__ . '/../../utils/products_helper.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID không hợp lệ']);
        exit;
    }

    $product = getProductById($db, $productId);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tìm thấy']);
        exit;
    }

    // Lấy ảnh sản phẩm
    $images = getProductImages($db, $productId);

    // Format dữ liệu
    $response = [
        'success' => true,
        'data' => [
            'product_id' => (int)$product['product_id'],
            'product_name' => $product['product_name'],
            'category_id' => (int)$product['category_id'],
            'category_name' => $product['category_name'],
            'brand_id' => $product['brand_id'] ? (int)$product['brand_id'] : null,
            'brand_name' => $product['brand_name'],
            'sku' => $product['sku'],
            'price' => (float)$product['price'],
            'discount_price' => (float)$product['discount_price'],
            'stock_quantity' => (int)$product['stock_quantity'],
            'status' => $product['status'],
            'description' => $product['description'],
            'thumbnail' => $product['thumbnail'],
            'images' => $images,
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at']
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
