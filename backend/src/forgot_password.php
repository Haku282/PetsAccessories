<?php
// frontend/components/forgot_password.php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

$error = '';
$success = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $error = "Vui lòng nhập tài khoản email của bạn.";
        } else {
            // Kiểm tra xem email có tồn tại trong hệ thống không
            $sql = "SELECT user_id, fullname FROM users WHERE email = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // (Trong thực tế, bạn nên gửi một Email chứa Token mã hóa tới địa chỉ này).
                // Do test trên localhost không gửi được email, ta sẽ lưu tạm email vào session và cho phép đổi mật khẩu luôn.
                $_SESSION['reset_email'] = $email;

                // Chuyển hướng người dùng qua trang tạo mật khẩu mới
                header("Location: /PetsAccessories/frontend/components/reset_password.php");
                exit;
            } else {
                $error = "Địa chỉ email không tồn tại trong hệ thống.";
            }
        }
    }
}
