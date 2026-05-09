<?php
/**
 * API: Cập nhật sản phẩm
 * PUT /admin/backend/api/products/update.php
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
    
    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;

    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID không hợp lệ']);
        exit;
    }

    // Kiểm tra sản phẩm có tồn tại
    $existingProduct = getProductById($db, $productId);
    if (!$existingProduct) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tìm thấy']);
        exit;
    }

    // Validate dữ liệu
    $errors = [];
    if (!validateProductData($data, $errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Kiểm tra SKU nếu được cung cấp
    if (!empty($data['sku']) && $data['sku'] !== $existingProduct['sku']) {
        if (skuExists($db, $data['sku'], $productId)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'SKU đã tồn tại trong hệ thống']);
            exit;
        }
    }

    // Chuẩn bị dữ liệu
    $product_name = $data['product_name'];
    $category_id = (int)$data['category_id'];
    $brand_id = !empty($data['brand_id']) ? (int)$data['brand_id'] : null;
    $sku = !empty($data['sku']) ? $data['sku'] : null;
    $price = (float)$data['price'];
    $discount_price = !empty($data['discount_price']) ? (float)$data['discount_price'] : 0.00;
    $stock_quantity = (int)$data['stock_quantity'];
    $status = $data['status'];
    $description = !empty($data['description']) ? $data['description'] : null;

    // Cập nhật sản phẩm
    $stmt = $db->prepare("
        UPDATE products 
        SET category_id = ?, brand_id = ?, product_name = ?, sku = ?, price = ?, discount_price = ?, stock_quantity = ?, status = ?, description = ?, updated_at = NOW()
        WHERE product_id = ?
    ");

    $success = $stmt->execute([
        $category_id,
        $brand_id,
        $product_name,
        $sku,
        $price,
        $discount_price,
        $stock_quantity,
        $status,
        $description,
        $productId
    ]);

    if (!$success) {
        throw new Exception('Không thể cập nhật sản phẩm');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " cập nhật sản phẩm ID: $productId");

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật sản phẩm thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
