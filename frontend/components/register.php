<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/register.php';

// Kiểm tra xem có đang ở bước OTP không
$is_otp_step = isset($_SESSION['otp_pending']) && $_SESSION['otp_pending'] === true;
// Biến đánh dấu thành công bước cuối (để có thể refesh về login)
$is_registered_success = !$is_otp_step && !empty($success) && strpos($success, 'Đăng ký thành công') !== false;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Pets Accessories</title>
    <link rel="stylesheet" type="text/css" href="/PetsAccessories/frontend/layout/style.css">
</head>

<body>
    <div class="background-blur-overlay"></div>
    <main class="auth-container">
        <div class="auth-box">
            <?php if ($is_otp_step): ?>
                <h2>Xác thực OTP Email</h2>
            <?php else: ?>
                <h2>Đăng ký tài khoản</h2>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php if ($is_registered_success): ?>
                    <!-- Chuyển hướng về trang đăng nhập sau 2 giây -->
                    <meta http-equiv="refresh" content="2;url=login.php">
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($is_otp_step): ?>
                <!-- BƯỚC 2: FORM XÁC THỰC OTP -->
                <form action="register.php" method="POST" class="auth-form">
                    <input type="hidden" name="action" value="verify_otp">
                    <div class="form-group">
                        <label for="otp">Mã OTP (kiểm tra Email của bạn)</label>
                        <input type="text" id="otp" name="otp" placeholder="Nhập mã 6 chữ số" required>
                    </div>
                    <button type="submit" class="btn-auth btn-auth-register">Xác nhận OTP</button>
                    <p class="auth-links">
                        <a href="register.php?cancel=1" style="color: #e74c3c;">Vẽ trang đăng ký lại</a>
                    </p>
                </form>

            <?php elseif (!$is_registered_success): ?>
                <!-- BƯỚC 1: FORM ĐĂNG KÝ THÔNG TIN -->
                <!-- Form gọi POST dữ liệu vào chính file này thay vì index.php để kiểm tra code PHP -> DB -->
                <form action="register.php" method="POST" class="auth-form">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label for="fullname">Họ tên</label>
                        <input type="text" id="fullname" name="fullname" placeholder="Họ tên của bạn" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Tên đăng nhập</label>
                        <input type="text" id="username" name="username" placeholder="Tên đăng nhập" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" id="password" name="password" placeholder="Tạo mật khẩu" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Nhập lại mật khẩu</label>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Nhập lại mật khẩu" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Tài khoản Email</label>
                        <input type="email" id="email" name="email" placeholder="Nhập email của bạn" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" id="phone" name="phone" placeholder="Nhập số điện thoại" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                    </div>
                    <button type="submit" class="btn-auth btn-auth-register">Tạo tài khoản</button>
                    <p class="auth-links">
                        Đã có tài khoản? <a href="/PetsAccessories/frontend/components/login.php">Đăng nhập</a>
                    </p>
                    <p class="index-link">
                        <a href="/PetsAccessories/frontend/public/index.php">Quay về trang chủ</a>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>