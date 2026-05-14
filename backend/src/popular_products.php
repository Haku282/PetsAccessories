<?php
// Bật session nếu bạn có xử lý giỏ hàng/yêu thích ở component này
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Kết nối cơ sở dữ liệu
require_once __DIR__ . '/../config/database.php';

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

<div class="recent-products-section" style="margin-top: 40px;">
    <h2 style="text-align: center; color: #4CAF50; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; display: inline-block; margin-bottom: 20px;">
        Sản Phẩm Mới Trong 7 Ngày Qua
    </h2>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger" style="color: red; text-align: center;">
            <?php echo $errorMsg; ?>
        </div>
    <?php elseif (empty($recentProducts)): ?>
        <p style="text-align: center; color: #777;">Hiện tại không có sản phẩm nào mới được thêm trong tuần qua.</p>
    <?php else: ?>
        <div class="product-grid" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">

            <?php foreach ($recentProducts as $product): ?>
                <?php
                $price = (float)($product['price'] ?? 0);
                $discount = (float)($product['discount_price'] ?? 0);
                $hasDiscount = ($discount > 0 && $discount < $price);
                $thumbnail = !empty($product['thumbnail']) ? htmlspecialchars($product['thumbnail']) : '/PetsAccessories/admin/backend/uploads/products/default-product.png';
                ?>

                <div class="product-card" style="border: 1px solid #eee; border-radius: 8px; padding: 15px; width: 220px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <img src="<?php echo $thumbnail; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px;">

                    <h4 style="font-size: 16px; margin: 10px 0; height: 38px; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars($product['product_name']); ?>
                    </h4>

                    <div class="price-box" style="margin-bottom: 15px;">
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

                    <a href="/PetsAccessories/frontend/public/product_detail.php?id=<?php echo $product['product_id']; ?>"
                        style="display: inline-block; padding: 8px 12px; background-color: #f1f1f1; color: #333; text-decoration: none; border-radius: 4px; font-size: 14px; margin-bottom: 5px; width: 100%; box-sizing: border-box;">
                        Xem chi tiết
                    </a>

                    <form action="/PetsAccessories/frontend/components/add_to_cart.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" style="width: 100%; padding: 8px 12px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                            Thêm vào giỏ
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>

        </div>
    <?php endif; ?>
</div>