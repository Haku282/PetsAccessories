<?php
require_once __DIR__ . '/../../backend/src/wishlist.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách yêu thích - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .wishlist-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .wishlist-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }
        .wishlist-item-info {
            flex: 1;
        }
        .wishlist-item-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            text-decoration: none;
            color: #333;
        }
        .wishlist-item-price {
            color: #d32f2f;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .wishlist-actions button {
            margin-left: 10px;
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../layout/Header.php'; ?>

<main class="wishlist-container">
    <h2>Danh sách yêu thích của bạn</h2>
    <?php if (empty($wishlists)): ?>
        <p>Kho danh sách yêu thích của bạn đang trống. Hãy thêm một vài sản phẩm nhé!</p>
        <a href="/PetsAccessories/frontend/public/index.php" class="btn">Tiếp tục mua sắm</a>
    <?php else: ?>
        <?php foreach ($wishlists as $item): ?>
            <?php
                $price = $item['price'];
                $discount = $item['discount_price'];
                $final = ($discount > 0 && $discount < $price) ? $discount : $price;
                $thumbnail = !empty($item['thumbnail']) ? $item['thumbnail'] : '/PetsAccessories/frontend/public/images/default-product.png';
            ?>
            <div class="wishlist-item" id="wishlist-item-<?php echo $item['product_id']; ?>">
                <a href="/PetsAccessories/frontend/public/index.php?page=product_detail&id=<?php echo $item['product_id']; ?>">
                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                </a>
                <div class="wishlist-item-info">
                    <a href="/PetsAccessories/frontend/public/index.php?page=product_detail&id=<?php echo $item['product_id']; ?>" class="wishlist-item-title">
                        <?php echo htmlspecialchars($item['product_name']); ?>
                    </a>
                    <div class="wishlist-item-price"><?php echo number_format($final, 0, ',', '.'); ?> đ</div>
                </div>
                <div class="wishlist-actions">
                    <button class="btn btn-add-cart" data-id="<?php echo $item['product_id']; ?>" onclick="addToCart(this)">Thêm vào giỏ</button>
                    <button class="btn" style="background:#dc3545;" onclick="toggleWishlist(<?php echo $item['product_id']; ?>)">Xóa</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<script>
function toggleWishlist(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'toggle');

    fetch('/PetsAccessories/backend/src/wishlist_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            if(data.action === 'removed') {
                const row = document.getElementById('wishlist-item-' + productId);
                if(row) row.remove();
            }
            alert(data.message);
            // reload if empty
            if(document.querySelectorAll('.wishlist-item').length === 0) {
                location.reload();
            }
        } else {
            alert(data.message);
        }
    })
    .catch(err => alert('Có lỗi xảy ra.'));
}
</script>

<?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>
</html>