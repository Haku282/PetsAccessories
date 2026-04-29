<?php
// frontend/components/reset_password.php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

// Kiểm tra xem người dùng có đi qua form "Quên mật khẩu" hay không
if (!isset($_SESSION['reset_email'])) {
    // Không có email tạm thì quay về file quên mật khẩu
    header("Location: /PetsAccessories/frontend/components/forgot_password.php");
    exit;
}

$email = $_SESSION['reset_email'];
$error = '';
$success = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirm_password)) {
            $error = "Vui lòng điền đủ mật khẩu mới.";
        } elseif ($password !== $confirm_password) {
            $error = "Mật khẩu xác nhận không khớp.";
        } elseif (strlen($password) < 3) {
            $error = "Mật khẩu phải dài hơn 3 ký tự.";
        } else {
            // Cập nhật Database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $db->prepare($sql);

            if ($stmt->execute([$hashed_password, $email])) {
                $success = "Cập nhật mật khẩu thành công!";
                // Xóa Session reset mật khẩu để bảo mật sau khi đổi xong
                unset($_SESSION['reset_email']);
            } else {
                $error = "Có lỗi máy chủ, không thể cập nhật lúc này.";
            }
        }
    }
}
