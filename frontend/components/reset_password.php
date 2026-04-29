<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/reset_password.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>

    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="auth-container">
        <div class="auth-box">
            <h2>Đặt lại mật khẩu mới</h2>

            <?php if (!empty($success)): ?>
                <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <p style="text-align: center; color: #555; margin-bottom: 20px;">
                    Đang chuyển hướng về lại trang đăng nhập...
                </p>
                <meta http-equiv="refresh" content="2;url=/PetsAccessories/frontend/components/login.php">
            <?php else: ?>
                <p style="text-align: center; color: #ff6f61; margin-bottom: 20px; font-weight: bold; font-size: 15px;">
                    Tài khoản: <?php echo htmlspecialchars($email); ?>
                </p>

                <?php if (!empty($error)): ?>
                    <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="reset_password.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="password">Mật khẩu mới</label>
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu mới" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                    </div>
                    <button type="submit" class="btn-auth">Cập nhật mật khẩu</button>
                    <p class="index-link" style="margin-top: 20px;">
                        <a href="/PetsAccessories/frontend/components/login.php">Hủy bỏ</a>
                    </p>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>

</body>

</html>