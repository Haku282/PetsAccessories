<?php
require_once __DIR__ . '/../../backend/config/database.php';
$isEmbedded = $isEmbedded ?? false; ?>
<?php if (!$isEmbedded): ?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sản Phẩm Mới - Pets Accessories</title>
        <link rel="stylesheet" href="../layout/style.css">
    </head>

    <body>
        <?php require_once __DIR__ . '/../layout/header.php'; ?>
    <?php endif; ?>

    <?php
    // Bật session nếu bạn có xử lý giỏ hàng/yêu thích ở component này
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Kết nối cơ sở dữ liệu
    require_once __DIR__ . '/../../backend/config/database.php';

    $recentProducts = [];
    $errorMsg = '';

    if (isset($pdo)) {
        try {
            $sql = "SELECT product_id, product_name, price, discount_price, thumbnail, description, created_at 
                    FROM products 
                    WHERE created_at >= NOW() - INTERVAL 7 DAY AND status = 1
                    ORDER BY created_at DESC 
                    LIMIT 50";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $recentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errorMsg = "Lỗi tải sản phẩm mới: " . $e->getMessage();
        }
    } else {
        $errorMsg = "Chưa kết nối được cơ sở dữ liệu.";
    }
    ?>

    <main class="products-page" style="padding: 40px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
            <div class="recent-products-section">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h2 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; display: inline-block;">Sản Phẩm Mới Trong 7 Ngày Qua</h2>
                </div>

                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger" style="color: red; text-align: center; padding: 15px; border: 1px solid red; border-radius: 5px; background-color: #ffe6e6; max-width: 600px; margin: 0 auto;">
                        <?php echo $errorMsg; ?>
                    </div>
                <?php elseif (empty($recentProducts)): ?>
                    <p style="text-align: center; color: #777; font-size: 16px;">Hiện tại không có sản phẩm nào mới được thêm trong tuần qua.</p>
                <?php else: ?>
                    <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">

                        <?php foreach ($recentProducts as $product): ?>
                            <?php
                            $price = (float)($product['price'] ?? 0);
                            $discount = (float)($product['discount_price'] ?? 0);
                            $hasDiscount = ($discount > 0 && $discount < $price);

                            // Xử lý chuẩn đường dẫn ảnh
                            $rawThumb = $product['thumbnail'] ?? '';
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

                            <div class="product-card" style="position: relative; border: 1px solid #e1e8ee; border-radius: 8px; overflow: hidden; background: #fff; display: flex; flex-direction: column; height: 100%; transition: transform 0.2s, box-shadow 0.2s;">

                                <div style="position: absolute; top: 10px; right: 10px; background: #ff4747; color: white; font-size: 12px; font-weight: bold; padding: 4px 10px; border-radius: 20px; z-index: 2; box-shadow: 0 2px 5px rgba(255, 71, 71, 0.4); text-transform: uppercase; letter-spacing: 1px;">
                                    New
                                </div>

                                <div class="product-image" style="height: 200px; display: flex; align-items: center; justify-content: center; background: #f9fbfd; padding: 10px;">
                                    <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo $product['product_id']; ?>" style="width: 100%; height: 100%;">
                                        <img src="<?php echo htmlspecialchars($thumbnail); ?>"
                                            alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                            style="width: 100%; height: 100%; object-fit: contain;"
                                            onerror="this.onerror=null; this.src='/PetsAccessories/frontend/public/images/default-product.png'">
                                    </a>
                                </div>

                                <div class="product-info" style="padding: 15px; display: flex; flex-direction: column; flex: 1;">
                                    <h3 style="font-size: 14px; margin: 0 0 10px; height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; line-clamp: 2; -webkit-box-orient: vertical;">
                                        <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: #333; line-height: 1.4;">
                                            <?php echo htmlspecialchars($product['product_name']); ?>
                                        </a>
                                    </h3>

                                    <div class="price-box" style="margin-top: auto; margin-bottom: 15px;">
                                        <?php if ($hasDiscount): ?>
                                            <span style="color: #999; text-decoration: line-through; font-size: 13px; margin-right: 5px;">
                                                <?php echo number_format($price, 0, ',', '.'); ?> ₫
                                            </span>
                                            <span style="color: #E53935; font-weight: bold; font-size: 15px;">
                                                <?php echo number_format($discount, 0, ',', '.'); ?> ₫
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #E53935; font-weight: bold; font-size: 15px;">
                                                <?php echo number_format($price, 0, ',', '.'); ?> ₫
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div style="display: flex; gap: 10px;">
                                        <button type="button" class="btn-add-cart" data-id="<?php echo $product['product_id']; ?>" onclick="addToCart(this)" style="flex: 1; padding: 10px; background: #ff5c5c; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; transition: background 0.3s;">
                                            Thêm vào giỏ
                                        </button>

                                        <button class="btn-wishlist" style="background: none; border: 1px solid #ddd; color: #ff5c5c; border-radius: 4px; width: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;" onclick="toggleWishlist(<?php echo (int)($product['product_id'] ?? 0); ?>)" title="Yêu thích">
                                            ❤️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php if (!$isEmbedded): ?>
        <?php require_once __DIR__ . '/../layout/footer.php'; ?>

        <script>
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
                        if (!countElem) countElem = document.querySelector('.cart-count'); // fallback
                        if (countElem) countElem.innerText = data.cart_count;
                    }
                }).catch(err => console.error(err));
            }
        </script>
    </body>

    </html>
<?php endif; ?>