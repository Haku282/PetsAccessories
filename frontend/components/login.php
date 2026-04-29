<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/login.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>

    <main class="auth-container">
        <div class="auth-box">
            <h2>Đăng nhập</h2>

            <?php if (!empty($error)): ?>
                <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="login_id">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="login_id" name="login_id" placeholder="Nhập tên đăng nhập hoặc email" value="<?php echo htmlspecialchars($login_id ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                </div>
                <div style="text-align: right; margin-bottom: 15px;">
                    <a href="/PetsAccessories/frontend/components/forgot_password.php" style="color: #ff6f61; font-size: 14px; text-decoration: none; font-weight: bold;">Quên mật khẩu?</a>
                </div>
                <button type="submit" class="btn-auth">Đăng nhập</button>
                <p class="auth-links">
                    Chưa có tài khoản? <a href="/PetsAccessories/frontend/components/register.php">Đăng ký ngay</a>
                </p>
                <p class="index-link">
                    <a href="/PetsAccessories/frontend/public/index.php">Quay về trang chủ</a>
                </p>
            </form>
        </div>
    </main>

</body>

</html>