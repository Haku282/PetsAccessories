<?php
require_once __DIR__ . '/../../backend/src/cart.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="cart-page">
        <div class="cart-container">
            <h2>Giỏ hàng</h2>

            <?php if (!empty($error)): ?>
                <div class="cart-alert cart-alert--error"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (!empty($notice)): ?>
                <div class="cart-alert cart-alert--notice"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>

            <?php if (empty($cartItems)): ?>
                <p>Giỏ hàng của bạn đang trống.</p>
                <p><a href="/PetsAccessories/frontend/public/index.php" class="cart-link">Tiếp tục mua sắm</a></p>
            <?php else: ?>
                <div class="cart-grid">
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item__thumb">
                                    <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int) $item['product_id']; ?>">
                                        <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    </a>
                                </div>

                                <div class="cart-item__info">
                                    <a class="cart-item__name" href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int) $item['product_id']; ?>">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </a>
                                    <div class="cart-item__meta">
                                        <span>Đơn giá: <strong><?php echo number_format((float) $item['unit_price'], 0, ',', '.'); ?> đ</strong></span>
                                        <span>Tạm tính: <strong><?php echo number_format((float) $item['line_total'], 0, ',', '.'); ?> đ</strong></span>
                                    </div>

                                    <div class="cart-item__actions">
                                        <form method="POST" action="" class="cart-form">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $item['product_id']; ?>">
                                            <label class="cart-qty">
                                                Số lượng:
                                                <input type="number" name="quantity" min="0" max="99" value="<?php echo (int) $item['quantity']; ?>">
                                            </label>
                                            <button type="submit" class="cart-btn">Cập nhật</button>
                                        </form>

                                        <form method="POST" action="" class="cart-form" onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?');">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $item['product_id']; ?>">
                                            <button type="submit" class="cart-btn cart-btn--danger">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <aside class="cart-summary">
                        <h3>Tạm tính</h3>
                        <div class="cart-summary__row">
                            <span>Tổng tiền hàng</span>
                            <strong><?php echo number_format((float) $subtotal, 0, ',', '.'); ?> đ</strong>
                        </div>
                        <div class="cart-summary__row">
                            <span>Thuế (tạm tính)</span>
                            <strong><?php echo number_format((float) $tax, 0, ',', '.'); ?> đ</strong>
                        </div>
                        <div class="cart-summary__row">
                            <span>Phí vận chuyển (tạm tính)</span>
                            <strong><?php echo number_format((float) $shipping, 0, ',', '.'); ?> đ</strong>
                        </div>
                        <div class="cart-summary__row cart-summary__row--total">
                            <span>Tổng cộng</span>
                            <strong><?php echo number_format((float) $estimatedTotal, 0, ',', '.'); ?> đ</strong>
                        </div>

                        <p class="cart-summary__hint">Các giá trị trên là tạm tính và có thể thay đổi khi thanh toán.</p>

                        <div class="cart-summary__actions">
                            <a href="/PetsAccessories/frontend/components/checkout.php" class="cart-btn">Thanh toán</a>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>

</html>
