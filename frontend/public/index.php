<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pets Accessories - Trang chủ</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>

    <?php
    // Kiểm tra session - nếu admin, chuyển hướng tới admin dashboard
    session_start();
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: /PetsAccessories/admin/frontend/index_admin.php");
        exit;
    }

    $page = $_GET['page'] ?? '';
    if ($page === 'wishlist') {
        require_once __DIR__ . '/../components/wishlist.php';
        exit;
    }
    if ($page === 'product_detail') {
        require_once __DIR__ . '/../components/product_detail.php';
        exit;
    }
    if ($page === 'products') {
        require_once __DIR__ . '/../components/products.php';
        exit;
    }
    if ($page === 'news_detail') {
        require_once __DIR__ . '/../components/news_detail.php';
        exit;
    }
    if ($page === 'page_detail') {
        require_once __DIR__ . '/../components/page_detail.php';
        exit;
    }

    // Kết nối DB để lấy sản phẩm cho trang chủ
    require_once __DIR__ . '/../../backend/config/database.php';

    $featuredProducts = [];
    $newProducts = [];
    $saleProducts = [];
    $db = $pdo;

    if ($db instanceof PDO) {
        try {
            $featuredStmt = $db->query(
            "SELECT
                p.product_id,
                p.product_name AS name,
                COALESCE(NULLIF(p.discount_price, 0), p.price) AS price,
                COALESCE(NULLIF(p.thumbnail, ''), '/PetsAccessories/frontend/public/images/default-product.png') AS image
             FROM products p
             WHERE p.status = 'active'
             ORDER BY (CASE WHEN p.discount_price > 0 THEN 1 ELSE 0 END) DESC, p.stock_quantity DESC, p.created_at DESC
             LIMIT 8"
            );
            $featuredProducts = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

            $newStmt = $db->query(
            "SELECT
                p.product_id,
                p.product_name AS name,
                COALESCE(NULLIF(p.discount_price, 0), p.price) AS price,
                COALESCE(NULLIF(p.thumbnail, ''), '/PetsAccessories/frontend/public/images/default-product.png') AS image
             FROM products p
             WHERE p.status = 'active'
               AND p.created_at >= NOW() - INTERVAL 7 DAY
             ORDER BY p.created_at DESC
             LIMIT 8"
            );
            $newProducts = $newStmt->fetchAll(PDO::FETCH_ASSOC);

            $saleStmt = $db->query(
            "SELECT
                p.product_id,
                p.product_name AS name,
                p.discount_price AS price,
                COALESCE(NULLIF(p.thumbnail, ''), '/PetsAccessories/frontend/public/images/default-product.png') AS image
             FROM products p
             WHERE p.status = 'active'
               AND p.discount_price > 0
               AND p.discount_price < p.price
             ORDER BY (p.price - p.discount_price) DESC, p.created_at DESC
             LIMIT 8"
            );
            $saleProducts = $saleStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Giữ mảng rỗng để product_grid hiển thị thông báo fallback.
        }
    }

    // Tích hợp các components
    require_once __DIR__ . '/../layout/Header.php';
    require_once __DIR__ . '/../components/banner_slider.php';
    require_once __DIR__ . '/../components/news_section.php';

    $isEmbedded = true;

    // Các section Sản phẩm:
    $sectionTitle = "Sản phẩm Nổi Bật";
    $products = $featuredProducts;
    require __DIR__ . '/../components/products.php';

    $sectionTitle = "Sản phẩm Mới";
    $products = $newProducts;
    require __DIR__ . '/../components/popular_products.php';

    $sectionTitle = "Sản phẩm Khuyến Mãi";
    $products = $saleProducts;
    require __DIR__ . '/../components/products.php';

    // Tin tức & Thông tin
    require_once __DIR__ . '/../layout/Footer.php';
    ?>

</body>

</html>