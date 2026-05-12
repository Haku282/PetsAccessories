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
                    WHERE created_at >= NOW() - INTERVAL 7 DAY 
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

    <div class="recent-products-section">
        <div style="text-align: center;">
            <h2>Sản Phẩm Mới Trong 7 Ngày Qua</h2>
        </div>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger" style="color: red; text-align: center; padding: 15px; border: 1px solid red; border-radius: 5px; background-color: #ffe6e6; max-width: 600px; margin: 0 auto;">
                <?php echo $errorMsg; ?>
            </div>
        <?php elseif (empty($recentProducts)): ?>
            <p style="text-align: center; color: #777; font-size: 16px;">Hiện tại không có sản phẩm nào mới được thêm trong tuần qua.</p>
        <?php else: ?>
            <div class="product-grid">

                <?php foreach ($recentProducts as $product): ?>
                    <?php
                    $price = (float)($product['price'] ?? 0);
                    $discount = (float)($product['discount_price'] ?? 0);
                    $hasDiscount = ($discount > 0 && $discount < $price);
                    $thumbnail = !empty($product['thumbnail']) ? htmlspecialchars($product['thumbnail']) : '/PetsAccessories/frontend/public/images/default-product.png';
                    ?>

                    <div class="product-card">
                        <img src="<?php echo $thumbnail; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">

                        <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>

                        <div class="price-box">
                            <?php if ($hasDiscount): ?>
                                <span style="color: #999; text-decoration: line-through; font-size: 14px; margin-right: 5px;">
                                    <?php echo number_format($price, 0, ',', '.'); ?> ₫
                                </span>
                                <span style="color: #E53935; font-weight: bold; font-size: 16px;">
                                    <?php echo number_format($discount, 0, ',', '.'); ?> ₫
                                </span>
                            <?php else: ?>
                                <span style="color: #4CAF50; font-weight: bold; font-size: 16px;">
                                    <?php echo number_format($price, 0, ',', '.'); ?> ₫
                                </span>
                            <?php endif; ?>
                        </div>

                        <a href="/PetsAccessories/frontend/public/product_detail.php?id=<?php echo $product['product_id']; ?>">
                            Xem chi tiết
                        </a>

                        <form action="/PetsAccessories/frontend/components/add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit">
                                Thêm vào giỏ
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </div>
    <?php if (!$isEmbedded): ?>
        <?php require_once __DIR__ . '/../layout/footer.php'; ?>
    </body>

    </html>
<?php endif; ?>