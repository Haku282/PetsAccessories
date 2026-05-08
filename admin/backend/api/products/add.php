<?php
/**
 * API: Thêm sản phẩm mới
 * POST /admin/backend/api/products/add.php
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

    // Validate dữ liệu
    $errors = [];
    if (!validateProductData($data, $errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Kiểm tra SKU nếu được cung cấp
    if (!empty($data['sku']) && skuExists($db, $data['sku'])) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'SKU đã tồn tại trong hệ thống']);
        exit;
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

    // Thêm sản phẩm vào database
    $stmt = $db->prepare("
        INSERT INTO products (category_id, brand_id, product_name, sku, price, discount_price, stock_quantity, status, description, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
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
        $description
    ]);

    if (!$success) {
        throw new Exception('Không thể thêm sản phẩm');
    }

    $productId = $db->lastInsertId();

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " thêm sản phẩm ID: $productId");

    echo json_encode([
        'success' => true,
        'message' => 'Thêm sản phẩm thành công',
        'product_id' => (int)$productId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
