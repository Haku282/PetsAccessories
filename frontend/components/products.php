<?php
require_once __DIR__ . '/../../backend/config/database.php';

$isEmbedded = $isEmbedded ?? false;

// Pagination and filtering could go here
if (!isset($products)) {
    try {
        if (isset($pdo)) {
            $stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 20");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $products = [];
        }
    } catch (PDOException $e) {
        $products = [];
    }
}
$sectionTitle = $sectionTitle ?? 'Danh sách sản phẩm';
?>
<?php if (!$isEmbedded): ?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($sectionTitle); ?> - PetsAccessories</title>
        <link rel="stylesheet" href="../layout/style.css">
    </head>

    <body>
        <?php require_once __DIR__ . '/../layout/header.php'; ?>
    <?php endif; ?>
    <main class="products-page" style="padding: 40px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
            <section class="product-section">
                <h2>
                    <?php echo htmlspecialchars($sectionTitle); ?>
                </h2>

                <div class="product-grid">
                    <?php if (!empty($products) && is_array($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)($product['product_id'] ?? 0); ?>">
                                        <?php
                                        // Build image path - support multiple thumbnail columns
                                        $thumbnail = $product['thumbnail'] ?? $product['image'] ?? '';
                                        if (!empty($thumbnail)) {
                                            $image = '/PetsAccessories/admin/backend/uploads/products/' . htmlspecialchars($thumbnail);
                                        } else {
                                            $image = '/PetsAccessories/frontend/public/images/default.jpg';
                                        }
                                        ?>
                                        <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($product['product_name'] ?? $product['name'] ?? 'Tên sản phẩm'); ?>" loading="lazy">
                                    </a>
                                </div>
                                <div class="product-info">
                                    <h3>
                                        <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)($product['product_id'] ?? 0); ?>" style="text-decoration: none; color: inherit;">
                                            <?php echo htmlspecialchars($product['product_name'] ?? $product['name'] ?? 'Product Name'); ?>
                                        </a>
                                    </h3>
                                    <p class="price">
                                        <?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?> đ
                                    </p>
                                    <div style="display:flex; gap:10px;">
                                        <button class="btn-add-cart" style="flex:1;" data-id="<?php echo (int)($product['product_id'] ?? 0); ?>" onclick="addToCart(this)">Thêm vào giỏ</button>
                                        <button class="btn-wishlist" style="background:none; border:1px solid #ccc; border-radius:4px; padding:0 10px; cursor:pointer;" onclick="toggleWishlist(<?php echo (int)($product['product_id'] ?? 0); ?>)" title="Yêu thích">❤️</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-products">Chưa có sản phẩm nào.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    <?php if (!$isEmbedded): ?>
        <?php require_once __DIR__ . '/../layout/footer.php'; ?>
        
    </body>

    </html>
<?php endif; ?>