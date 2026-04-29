<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/change_password.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>

    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="auth-container">
        <div class="auth-box">
            <h2>Đổi mật khẩu</h2>

            <?php if (!empty($error)): ?>
                <div style="color: #c0392b; background-color: #fadbd8; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form action="change_password.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_new_password">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                </div>

                <button type="submit" class="btn-auth">Cập nhật mật khẩu</button>

                <p class="index-link">
                    <a href="/PetsAccessories/frontend/public/index.php">Quay về trang chủ</a>
                </p>
            </form>
        </div>
    </main>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>

</body>

</html>