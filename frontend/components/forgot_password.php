<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/forgot_password.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>

    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="auth-container">
        <div class="auth-box">
            <h2>Quên mật khẩu</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px; font-size: 14px;">
                Vui lòng nhập địa chỉ email đã đăng ký để lấy lại mật khẩu.
            </p>

            <?php if (!empty($error)): ?>
                <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Tài khoản Email</label>
                    <input type="email" id="email" name="email" placeholder="Nhập email của bạn" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn-auth">Tiếp tục</button>
                <p class="index-link" style="margin-top: 20px;">
                    <a href="/PetsAccessories/frontend/components/login.php">Quay lại trang Đăng nhập</a>
                </p>
            </form>
        </div>
    </main>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>

</body>

</html>