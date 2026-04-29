<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/register.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>

    <main class="auth-container">
        <div class="auth-box">
            <h2>Đăng ký tài khoản</h2>

            <?php if (!empty($error)): ?>
                <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <!-- Chuyển hướng về trang đăng nhập sau 2 giây -->
                <meta http-equiv="refresh" content="2;url=login.php">
            <?php endif; ?>

            <!-- Form gọi POST dữ liệu vào chính file này thay vì index.php để kiểm tra code PHP -> DB -->
            <form action="register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="fullname">Họ tên</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Họ tên của bạn" value="<?php echo htmlspecialchars($fullname ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" placeholder="Tên đăng nhập" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
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
                    <input type="email" id="email" name="email" placeholder="Nhập email của bạn" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn-auth btn-auth-register">Tạo tài khoản</button>
                <p class="auth-links">
                    Đã có tài khoản? <a href="/PetsAccessories/frontend/components/login.php">Đăng nhập</a>
                </p>
                <p class="index-link">
                    <a href="/PetsAccessories/frontend/public/index.php">Quay về trang chủ</a>
                </p>
            </form>
        </div>
    </main>

</body>

</html>