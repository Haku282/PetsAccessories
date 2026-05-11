<?php
require_once __DIR__ . '/../../backend/src/process_checkout.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layout/header.php'; ?>
    <main style="max-width: 600px; margin: 60px auto; text-align: center; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.05);">
        <h2 style="color: #027a48; margin-bottom: 20px;">🎉 Đặt hàng thành công!</h2>
        <p style="margin-bottom: 10px; color: #555;">Cảm ơn <strong><?php echo htmlspecialchars($name); ?></strong> đã mua sắm tại PetsAccessories.</p>
        <p style="margin-bottom: 10px; color: #555;">Đơn hàng sẽ được giao đến địa chỉ: <strong><?php echo htmlspecialchars($address); ?></strong>.</p>
        
        <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px; text-align: left; display: inline-block;">
            <p style="margin: 5px 0;"><strong>Vận chuyển:</strong> <?php echo htmlspecialchars($shippingLabel); ?></p>
            <p style="margin: 5px 0;"><strong>Thanh toán:</strong> <?php echo htmlspecialchars($paymentLabel); ?></p>
        </div>

        <p style="margin-bottom: 30px; color: #155724; background: #d4edda; padding: 12px; border-radius: 8px;">
            <?php echo $notificationMessage; ?>
        </p>

        <?php if (!empty($createdOrderId)): ?>
            <p style="margin-bottom: 15px; color: #555;">
                Mã đơn hàng của bạn: <strong>#<?php echo (int) $createdOrderId; ?></strong>
                
                <a href="/PetsAccessories/frontend/components/order_detail.php?id=<?php echo (int) $createdOrderId; ?>" style="margin-left: 10px; color: #007bff; text-decoration: none; font-weight: 600;">Xem chi tiết</a>
            </p>
        <?php endif; ?>

        <?php if (!empty($lineItems)): ?>
            <div style="margin: 0 auto 25px; max-width: 520px; text-align: left; background: #fff; border: 1px solid #eee; border-radius: 10px; padding: 14px;">
                <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #333;">Danh sách sản phẩm</h3>
                <?php foreach ($lineItems as $it): ?>
                    <div style="display:flex; gap:10px; align-items:center; padding: 10px 0; border-bottom: 1px dashed #eee;">
                        <img src="<?php echo htmlspecialchars((string) $it['thumbnail']); ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:8px;">
                        <div style="flex:1;">
                            <div style="font-weight: 700; color:#333;"><?php echo htmlspecialchars((string) $it['product_name']); ?></div>
                            <div style="color:#666; font-size: 13px;">SL: <?php echo (int) $it['quantity']; ?> · Đơn giá: <?php echo number_format((float) $it['unit_price'], 0, ',', '.'); ?> đ</div>
                        </div>
                        <div style="font-weight: 800; color:#111;"><?php echo number_format((float) $it['line_total'], 0, ',', '.'); ?> đ</div>
                    </div>
                <?php endforeach; ?>
                <div style="display:flex; justify-content: space-between; padding-top: 12px; font-weight: 800;">
                    <span>Tạm tính</span>
                    <span><?php echo number_format((float) $totalValue, 0, ',', '.'); ?> đ</span>
                </div>
            </div>
        <?php endif; ?>
        
        <a href="/PetsAccessories/frontend/public/index.php" class="cart-btn" style="display: inline-block;">Tiếp tục mua sắm</a>
    </main>
    <?php require_once __DIR__ . '/../layout/footer.php'; ?>
</body>
</html>
