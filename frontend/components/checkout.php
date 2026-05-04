<?php
require_once __DIR__ . '/../../backend/src/cart.php';

$prefillName = '';
$prefillPhone = '';
$prefillAddress = '';
$prefillEmail = '';

if (isset($_SESSION['user_id']) && ($db instanceof PDO)) {
    try {
        $stmt = $db->prepare('SELECT fullname, phone, address, email FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($profile)) {
            $prefillName = (string) ($profile['fullname'] ?? '');
            $prefillPhone = (string) ($profile['phone'] ?? '');
            $prefillAddress = (string) ($profile['address'] ?? '');
            $prefillEmail = (string) ($profile['email'] ?? '');
        }
    } catch (PDOException $e) {
        // Ignore profile prefill errors
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="cart-page">
        <div class="cart-container">
            <h2>Thanh toán</h2>

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
                                        <span>Số lượng: <strong><?php echo (int) $item['quantity']; ?></strong></span>
                                        <span>Tạm tính: <strong><?php echo number_format((float) $item['line_total'], 0, ',', '.'); ?> đ</strong></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div id="checkout-form-section" class="checkout-form-section" data-hidden="1" style="display: none; margin-top: 30px; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                            <!-- Trust Badges (giống hình Chiaki) -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px; padding-bottom: 25px; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="font-size: 28px; color: #0284c7;">🛡️</div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;">An toàn</div>
                                        <div style="font-size: 13px; color: #64748b;">Bảo mật thanh toán</div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="font-size: 28px; color: #0284c7;">🚚</div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;">Miễn phí giao hàng</div>
                                        <div style="font-size: 13px; color: #64748b;">Với đơn hàng từ 500k</div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="font-size: 28px; color: #0284c7;">🔄</div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;">Miễn phí trả hàng</div>
                                        <div style="font-size: 13px; color: #64748b;">Lên đến 15 ngày</div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="font-size: 28px; color: #0284c7;">✅</div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;">Đảm bảo giao hàng</div>
                                        <div style="font-size: 13px; color: #64748b;">Hoàn tiền bất kỳ lúc nào</div>
                                    </div>
                                </div>
                            </div>

                            <form action="/PetsAccessories/frontend/components/process_checkout.php" method="POST" id="checkoutForm" class="auth-form" style="text-align: left;">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                                    <!-- Form Thông tin (Nền xanh nhạt giống panel bên trái) -->
                                    <div style="background-color: #f0f9ff; padding: 25px; border-radius: 12px;">
                                        <h3 style="margin-bottom: 20px; color: #0f172a; font-size: 18px;">Thông tin nhận hàng</h3>
                                        
                                        <div class="form-group" style="margin-bottom: 18px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Họ và tên <span style="color:red;">*</span></label>
                                            <input type="text" name="customer_name" value="<?php echo htmlspecialchars($prefillName); ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 15px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='#cbd5e1'">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 18px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Số điện thoại <span style="color:red;">*</span></label>
                                            <input type="tel" name="customer_phone" value="<?php echo htmlspecialchars($prefillPhone); ?>" required pattern="[0-9]{10,11}" title="Vui lòng nhập 10-11 số" style="width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 15px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='#cbd5e1'">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 18px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Email (để nhận hóa đơn)</label>
                                            <input type="email" name="customer_email" value="<?php echo htmlspecialchars($prefillEmail); ?>" placeholder="Nhập email của bạn" style="width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 15px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='#cbd5e1'">
                                        </div>
                                        <div class="form-group" style="margin-bottom: 18px;">
                                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Địa chỉ giao hàng <span style="color:red;">*</span></label>
                                            <textarea name="customer_address" required rows="3" style="width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 15px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#38bdf8'" onblur="this.style.borderColor='#cbd5e1'"><?php echo htmlspecialchars($prefillAddress); ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Phương thức vận chuyển và thanh toán (Giống panel bên phải) -->
                                    <div style="display: flex; flex-direction: column; gap: 20px;">
                                        <div style="border: 1px solid #e2e8f0; padding: 25px; border-radius: 12px;">
                                            <h3 style="margin-bottom: 15px; color: #0f172a; font-size: 16px;">Phương thức vận chuyển <span style="color:red;">*</span></h3>
                                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                                <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: normal; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; transition: all 0.2s;" onchange="document.querySelectorAll('input[name=shipping_method]').forEach(el => el.parentElement.style.borderColor='#e2e8f0'); this.style.borderColor='#38bdf8';">
                                                    <input type="radio" name="shipping_method" value="standard" checked> 🚚 Giao hàng tiêu chuẩn
                                                </label>
                                                <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: normal; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; transition: all 0.2s;" onchange="document.querySelectorAll('input[name=shipping_method]').forEach(el => el.parentElement.style.borderColor='#e2e8f0'); this.style.borderColor='#38bdf8';">
                                                    <input type="radio" name="shipping_method" value="express"> ⚡ Giao hàng nhanh
                                                </label>
                                                <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: normal; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; transition: all 0.2s;" onchange="document.querySelectorAll('input[name=shipping_method]').forEach(el => el.parentElement.style.borderColor='#e2e8f0'); this.style.borderColor='#38bdf8';">
                                                    <input type="radio" name="shipping_method" value="pickup"> 🏪 Lấy tại cửa hàng
                                                </label>
                                            </div>
                                        </div>

                                        <div style="border: 1px solid #e2e8f0; padding: 25px; border-radius: 12px;">
                                            <h3 style="margin-bottom: 15px; color: #0f172a; font-size: 16px;">Phương thức thanh toán <span style="color:red;">*</span></h3>
                                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                                <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: normal; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; transition: all 0.2s;" onchange="document.querySelectorAll('input[name=payment_method]').forEach(el => el.parentElement.style.borderColor='#e2e8f0'); this.style.borderColor='#38bdf8';">
                                                    <input type="radio" name="payment_method" value="cod" checked> 💵 Thanh toán khi nhận hàng (COD)
                                                </label>
                                                <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: normal; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; transition: all 0.2s;" onchange="document.querySelectorAll('input[name=payment_method]').forEach(el => el.parentElement.style.borderColor='#e2e8f0'); this.style.borderColor='#38bdf8';">
                                                    <input type="radio" name="payment_method" value="bank_transfer"> 🏦 Chuyển khoản ngân hàng
                                                </label>
                                                <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: normal; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; transition: all 0.2s;" onchange="document.querySelectorAll('input[name=payment_method]').forEach(el => el.parentElement.style.borderColor='#e2e8f0'); this.style.borderColor='#38bdf8';">
                                                    <input type="radio" name="payment_method" value="ewallet"> 📱 Ví điện tử (Momo, ZaloPay, VNPAY)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
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
                        
                        <div class="cart-summary__actions" style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;">
                            <button type="button" id="place-order-btn" class="cart-btn" style="width: 100%; font-size: 16px; padding: 14px; background: #38bdf8; border-radius: 8px; color: #fff; font-weight: bold; border: none; cursor: pointer; transition: background 0.3s;">Đặt hàng</button>
                            <a href="/PetsAccessories/frontend/components/cart.php" class="cart-link" style="text-align: center; display: block; margin-top: 10px; color: #64748b; font-weight: 500;">Quay lại giỏ hàng</a>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>

    <!-- Modal QR Code -->
    <div id="qr-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; max-width: 400px; width: 90%;">
            <h3 style="margin-bottom: 20px; color: #0f172a;">Quét mã QR để thanh toán</h3>
            <p style="color: #64748b; margin-bottom: 20px;">Đơn hàng của bạn sẽ được hoàn tất sau khi chuyển tiền.</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=Thanh+Toan+PetsAccessories" alt="QR Code" style="width: 250px; height: 250px; margin-bottom: 30px; border: 1px solid #e2e8f0; padding: 10px; border-radius: 8px;">
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button type="button" id="btn-cancel-qr" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #64748b; cursor: pointer;">Hủy</button>
                <button type="button" id="btn-paid-qr" style="padding: 10px 20px; border-radius: 8px; border: none; background: #38bdf8; color: #fff; font-weight: bold; cursor: pointer;">Đã chuyển tiền</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const placeOrderBtn = document.getElementById('place-order-btn');
            const formSection = document.getElementById('checkout-form-section');
            const form = document.getElementById('checkoutForm');
            const qrModal = document.getElementById('qr-modal');
            const btnCancelQr = document.getElementById('btn-cancel-qr');
            const btnPaidQr = document.getElementById('btn-paid-qr');

            if (!placeOrderBtn || !formSection || !form) return;

            placeOrderBtn.addEventListener('click', function () {
                const isHidden = formSection.getAttribute('data-hidden') === '1';

                if (isHidden) {
                    formSection.style.display = 'block';
                    formSection.setAttribute('data-hidden', '0');
                    placeOrderBtn.textContent = 'Xác nhận đặt hàng';

                    const firstInput = form.querySelector('input, textarea, select');
                    if (firstInput) {
                        firstInput.focus();
                    }

                    formSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                form.requestSubmit();
            });

            // Xử lý sự kiện submit form
            form.addEventListener('submit', function(e) {
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
                
                if (paymentMethod && (paymentMethod.value === 'bank_transfer' || paymentMethod.value === 'ewallet') && !form.dataset.qrConfirmed) {
                    e.preventDefault();
                    qrModal.style.display = 'flex';
                }
            });

            btnCancelQr.addEventListener('click', function() {
                qrModal.style.display = 'none';
            });

            btnPaidQr.addEventListener('click', function() {
                alert('Chuyển tiền thành công');
                qrModal.style.display = 'none';
                form.dataset.qrConfirmed = '1';
                
                // Cập nhật form action để chuyển về trang chủ nhanh (nếu cần qua process để lưu DB, thì process_checkout phải redirect về index)
                // Nhưng vì process_checkout hiện tại đang hiển thị giao diện "Đặt hàng thành công", 
                // người dùng yêu cầu: "sau đó quay lại trang index"
                // Thêm 1 input flag báo cho process_checkout biết cần redirect về index.
                const redirectInput = document.createElement('input');
                redirectInput.type = 'hidden';
                redirectInput.name = 'redirect_to_index';
                redirectInput.value = '1';
                form.appendChild(redirectInput);
                
                form.submit();
            });
        })();
    </script>
</body>

</html>
