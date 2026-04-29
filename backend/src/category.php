<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

$categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$categoryName = 'Danh mục sản phẩm';
$products = [];
$error = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if (!$categoryId) {
        $error = 'Danh mục không hợp lệ.';
    } else {
        try {
            $categoryStmt = $db->prepare('SELECT category_id, category_name FROM categories WHERE category_id = ? AND status = 1');
            $categoryStmt->execute([$categoryId]);
            $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                $error = 'Danh mục không tồn tại hoặc đã ẩn.';
            } else {
                $categoryName = $category['category_name'];

                $productsStmt = $db->prepare(
                    'SELECT
                        p.product_id,
                        p.product_name AS name,
                        COALESCE(NULLIF(p.discount_price, 0), p.price) AS price,
                        COALESCE(NULLIF(p.thumbnail, ""), "/PetsAccessories/frontend/public/images/default-product.png") AS image
                     FROM products p
                     WHERE p.status = 1
                       AND p.category_id IN (
                           SELECT c.category_id
                           FROM categories c
                           WHERE c.category_id = :categoryId OR c.parent_id = :categoryId
                       )
                     ORDER BY p.created_at DESC'
                );
                $productsStmt->execute(['categoryId' => $categoryId]);
                $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $error = 'Không thể tải dữ liệu danh mục lúc này.';
        }
    }
}
