<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/search.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <style>
        .search-results-section {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .search-results-section h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../layout/header.php'; ?>

    <main class="search-results-section">
        <h2><?php echo empty($searchQuery) ? 'Tìm kiếm' : htmlspecialchars($pageTitle); ?></h2>

        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (empty($products)): ?>
            <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa của bạn.</p>
        <?php else: ?>
            <p>Tìm thấy <strong><?php echo count($products); ?></strong> sản phẩm phù hợp.</p>
            <br>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)($product['product_id'] ?? 0); ?>">
                                <?php 
                                $image = !empty($product['thumbnail']) ? '/PetsAccessories/admin/backend/uploads/products/' . $product['thumbnail'] : (!empty($product['image']) ? $product['image'] : '/PetsAccessories/admin/backend/uploads/products/default-product.png'); 
                                ?>
                                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product['product_name'] ?? $product['name'] ?? 'Tên sản phẩm'); ?>">
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
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../layout/footer.php'; ?>
</body>

</html>