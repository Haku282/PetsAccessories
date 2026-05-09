<?php
// frontend/components/ProductCard.php
// Dự kiến nhận vào mảng $product với các thông tin: id, name, price, image...
?>
<div class="product-card">
    <div class="product-image">
        <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)($product['product_id'] ?? 0); ?>">
            <img src="<?php echo htmlspecialchars($product['image'] ?? '/PetsAccessories/frontend/public/images/default.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? 'Tên sản phẩm'); ?>">
        </a>
    </div>
    <div class="product-info">
        <h3>
            <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)($product['product_id'] ?? 0); ?>" style="text-decoration: none; color: inherit;">
                <?php echo htmlspecialchars($product['name'] ?? 'Product Name'); ?>
            </a>
        </h3>
        <p class="price"><?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?> đ</p>
        <div style="display:flex; gap:10px;">
            <button class="btn-add-cart" style="flex:1;" data-id="<?php echo (int)($product['product_id'] ?? 0); ?>" onclick="addToCart(this)">Thêm vào giỏ</button>
            <button class="btn-wishlist" style="background:none; border:1px solid #ccc; border-radius:4px; padding:0 10px; cursor:pointer;" onclick="toggleWishlist(<?php echo (int)($product['product_id'] ?? 0); ?>)" title="Yêu thích">❤️</button>
        </div>
    </div>
</div>