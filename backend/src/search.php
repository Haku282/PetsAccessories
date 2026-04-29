<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

$query = $_GET['q'] ?? '';
$searchQuery = trim(strip_tags((string)$query));
$sort = $_GET['sort'] ?? '';
$orderBy = 'created_at DESC';

if ($sort === 'price_asc') {
    $orderBy = 'COALESCE(NULLIF(discount_price, 0), price) ASC, created_at DESC';
} elseif ($sort === 'price_desc') {
    $orderBy = 'COALESCE(NULLIF(discount_price, 0), price) DESC, created_at DESC';
}

$products = [];
$error = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    try {
        if (empty($searchQuery)) {
            // TRƯỜNG HỢP 1: KHÔNG NHẬP TỪ KHÓA -> LẤY TẤT CẢ SẢN PHẨM
            $pageTitle = 'Tất cả sản phẩm';
            $sql = 'SELECT 
                        product_id, 
                        product_name AS name, 
                        COALESCE(NULLIF(discount_price, 0), price) AS price, 
                        COALESCE(NULLIF(thumbnail, ""), "/PetsAccessories/frontend/public/images/default-product.png") AS image
                    FROM products 
                    WHERE status = 1 
                    ORDER BY ' . $orderBy;

            $productsStmt = $db->prepare($sql);
            $productsStmt->execute();
            $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // TRƯỜNG HỢP 2: CÓ TỪ KHÓA -> LỌC THEO TỪ KHÓA
            $pageTitle = 'Kết quả tìm kiếm cho: "' . htmlspecialchars($searchQuery) . '"';
            $sql = 'SELECT 
                        product_id, 
                        product_name AS name, 
                        COALESCE(NULLIF(discount_price, 0), price) AS price, 
                        COALESCE(NULLIF(thumbnail, ""), "/PetsAccessories/frontend/public/images/default-product.png") AS image
                    FROM products 
                    WHERE status = 1 AND (
                        product_name LIKE :keyword 
                        OR sku LIKE :keyword 
                        OR description LIKE :keyword 
                    )
                    ORDER BY ' . $orderBy;

            $productsStmt = $db->prepare($sql);
            $searchTerm = '%' . $searchQuery . '%';
            $productsStmt->execute(['keyword' => $searchTerm]);
            $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Fallback nếu database không có cột sku hoặc description
        try {
            $sql = 'SELECT 
                        product_id, 
                        product_name AS name, 
                        COALESCE(NULLIF(discount_price, 0), price) AS price, 
                        COALESCE(NULLIF(thumbnail, ""), "/PetsAccessories/frontend/public/images/default-product.png") AS image
                    FROM products 
                    WHERE status = 1 AND product_name LIKE :keyword 
                    ORDER BY ' . $orderBy;

            $productsStmt = $db->prepare($sql);
            $productsStmt->execute(['keyword' => '%' . $searchQuery . '%']);
            $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            $error = 'Có lỗi khi lấy danh sách sản phẩm.';
        }
    }
}
?>