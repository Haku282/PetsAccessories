<?php
/**
 * API: Lấy danh sách sản phẩm
 * GET /admin/backend/api/products/list.php
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
    
    // Lấy các tham số filter
    $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $brand_id = isset($_GET['brand_id']) ? $_GET['brand_id'] : '';
    $discount = isset($_GET['discount']) ? trim((string)$_GET['discount']) : '';
    $filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Xây dựng query
    $sql = "
        SELECT p.*, c.category_name, b.brand_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE 1=1
    ";
    $params = [];

    // Áp dụng filter nếu có
    if ($filter === 'low_stock') {
        $sql .= " AND p.stock_quantity < 10 AND p.stock_quantity > 0";
    } elseif ($filter === 'out_of_stock') {
        $sql .= " AND p.stock_quantity = 0";
    }

    if (!empty($category_id)) {
        $sql .= " AND (p.category_id = ? OR p.category_id IN (SELECT category_id FROM categories WHERE parent_id = ?))";
        $params[] = (int)$category_id;
        $params[] = (int)$category_id;
    }

    if (!empty($status)) {
        $sql .= " AND p.status = ?";
        $params[] = $status;
    }

    if (!empty($brand_id)) {
        $sql .= " AND p.brand_id = ?";
        $params[] = (int)$brand_id;
    }

    if ($discount !== '') {
        if ($discount === '1') {
            $sql .= " AND p.discount_price > 0 AND p.discount_price < p.price";
        } elseif ($discount === '0') {
            $sql .= " AND (p.discount_price IS NULL OR p.discount_price = 0 OR p.discount_price >= p.price)";
        }
    }

    if (!empty($search)) {
        $sql .= " AND (p.product_name LIKE ? OR p.sku LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(*) as total FROM ($sql) as counted";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy dữ liệu phân trang
    $sql .= " ORDER BY p.updated_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu trả về
    $formattedProducts = [];
    foreach ($products as $product) {
        $statusInfo = getProductStatusInfo($product['status']);
        $formattedProducts[] = [
            'product_id' => $product['product_id'],
            'product_name' => $product['product_name'],
            'category_name' => $product['category_name'],
            'brand_name' => $product['brand_name'],
            'sku' => $product['sku'],
            'price' => (float)$product['price'],
            'discount_price' => (float)$product['discount_price'],
            'stock_quantity' => (int)$product['stock_quantity'],
            'status' => $product['status'],
            'status_label' => $statusInfo['label'],
            'status_color' => $statusInfo['color'],
            'thumbnail' => $product['thumbnail'],
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at']
        ];
    }

    $totalPages = ceil($totalRecords / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formattedProducts,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'records_per_page' => $limit
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
