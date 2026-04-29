<?php
// frontend/components/login.php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

$error = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login_id = trim($_POST['login_id'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($login_id) || empty($password)) {
            $error = "Vui lòng nhập đầy đủ thông tin.";
        } else {
            // Truy vấn user từ DB
            $sql = "SELECT user_id, username, email, password, fullname FROM users WHERE username = ? OR email = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$login_id, $login_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Kiểm tra mật khẩu
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = !empty($user['fullname']) ? $user['fullname'] : $user['username'];

                // Đăng nhập thành công -> Về trang chủ
                header("Location: /PetsAccessories/frontend/public/index.php");
                exit;
            } else {
                $error = "Tài khoản hoặc mật khẩu không chính xác!";
            }
        }
    }
}
?>