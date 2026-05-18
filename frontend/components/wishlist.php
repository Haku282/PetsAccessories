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
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .wishlist-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        .wishlist-item:last-child {
            border-bottom: none;
        }

        .wishlist-item img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-right: 25px;
            padding: 5px;
        }

        .wishlist-item-info {
            flex: 1;
        }

        .wishlist-item-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-decoration: none;
            color: #333;
            display: block;
        }

        .wishlist-item-title:hover {
            color: #ff5c5c;
        }

        .wishlist-item-price {
            color: #d32f2f;
            font-weight: bold;
            font-size: 1.1rem;
        }

        /* CSS CHỈNH 2 NÚT RỘNG BẰNG NHAU */
        .wishlist-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 150px;
        }

        .wishlist-actions button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 14px;
        }

        .btn-add-cart {
            background: #ff5c5c;
        }

        .btn-add-cart:hover {
            background: #e04a4a;
        }

        .btn-delete {
            background: #6c757d;
        }

        .btn-delete:hover {
            background: #5a6268;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="wishlist-container">
        <h2 style="border-bottom: 2px solid #ddd; padding-bottom: 15px; margin-bottom: 20px;">Danh sách yêu thích của bạn</h2>

        <?php if (empty($wishlists)): ?>
            <div style="text-align: center; padding: 50px 0;">
                <p style="font-size: 16px; color: #777; margin-bottom: 20px;">Kho danh sách yêu thích của bạn đang trống. Hãy thêm một vài sản phẩm nhé!</p>
                <a href="/PetsAccessories/frontend/public/index.php" style="display: inline-block; padding: 12px 25px; background: #0b59d6; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold;">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <?php foreach ($wishlists as $item): ?>
                <?php
                $price = (float)($item['price'] ?? 0);
                $discount = (float)($item['discount_price'] ?? 0);
                $final = ($discount > 0 && $discount < $price) ? $discount : $price;

                // Xử lý chuẩn đường dẫn ảnh
                $rawThumb = $item['thumbnail'] ?? '';
                if (!empty($rawThumb)) {
                    if (strpos($rawThumb, '/') !== false) {
                        $thumbnail = $rawThumb;
                    } else {
                        $thumbnail = '/PetsAccessories/admin/backend/uploads/products/' . $rawThumb;
                    }
                } else {
                    $thumbnail = '/PetsAccessories/frontend/public/images/default-product.png';
                }
                ?>
                <div class="wishlist-item" id="wishlist-item-<?php echo (int)$item['product_id']; ?>">
                    <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)$item['product_id']; ?>">
                        <img src="<?php echo htmlspecialchars($thumbnail); ?>"
                            alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                            onerror="this.onerror=null; this.src='/PetsAccessories/frontend/public/images/default-product.png'">
                    </a>
                    <div class="wishlist-item-info">
                        <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)$item['product_id']; ?>" class="wishlist-item-title">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                        </a>
                        <div class="wishlist-item-price"><?php echo number_format($final, 0, ',', '.'); ?> đ</div>
                    </div>
                    <div class="wishlist-actions">
                        <button type="button" class="btn-add-cart" data-id="<?php echo (int)$item['product_id']; ?>" onclick="addToCart(this)">Thêm vào giỏ</button>

                        <button type="button" class="btn-delete" onclick="removeFromWishlist(this, <?php echo (int)$item['product_id']; ?>)">Xóa khỏi danh sách</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script>
        // Hàm mới: Xử lý Xóa khỏi Yêu thích cực mượt
        function removeFromWishlist(btn, productId) {
            // Đổi trạng thái nút để tránh bấm 2 lần
            btn.disabled = true;
            btn.innerText = 'Đang xóa...';
            btn.style.opacity = '0.7';

            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', 'toggle');

            fetch('/PetsAccessories/backend/src/wishlist_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const row = document.getElementById('wishlist-item-' + productId);
                        if (row) {
                            // Tạo hiệu ứng mờ dần và trượt sang trái (giống trang Giỏ hàng)
                            row.style.transition = 'opacity 0.3s, transform 0.3s';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-20px)';

                            setTimeout(() => {
                                row.remove();
                                // Nếu xóa hết sạch sản phẩm thì tải lại trang để hiện câu thông báo "Đang trống"
                                if (document.querySelectorAll('.wishlist-item').length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    } else {
                        alert(data.message);
                        btn.disabled = false;
                        btn.innerText = 'Xóa khỏi danh sách';
                        btn.style.opacity = '1';
                    }
                })
                .catch(err => {
                    alert('Có lỗi kết nối. Vui lòng thử lại.');
                    btn.disabled = false;
                    btn.innerText = 'Xóa khỏi danh sách';
                    btn.style.opacity = '1';
                });
        }

        // Hàm xử lý Thêm vào giỏ hàng
        function addToCart(btn) {
            const id = btn.getAttribute('data-id');
            fetch('/PetsAccessories/backend/src/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'product_id=' + id + '&quantity=1'
            }).then(res => res.json()).then(data => {
                alert('Đã thêm sản phẩm vào giỏ hàng!');
                if (data.cart_count) {
                    let countElem = document.getElementById('cart-count-badge');
                    if (!countElem) countElem = document.querySelector('.cart-count');
                    if (countElem) countElem.innerText = data.cart_count;
                }
            }).catch(err => console.error(err));
        }
    </script>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>

</html>