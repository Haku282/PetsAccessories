<?php
// frontend/components/register.php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

$error = '';
$success = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm-password'] ?? '';

        // Kiểm tra dữ liệu hợp lệ cơ bản
        if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
            $error = "Vui lòng nhập đầy đủ thông tin.";
        } elseif ($password !== $confirm_password) {
            $error = "Mật khẩu nhập lại không khớp.";
        } else {
            // Kiểm tra xem Username hoặc Email đã tồn tại chưa
            $sqlCheck = "SELECT user_id FROM users WHERE username = ? OR email = ?";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->execute([$username, $email]);

            if ($stmtCheck->fetch()) {
                $error = "Tên đăng nhập hoặc Email này đã tồn tại trong hệ thống.";
            } else {
                // Mã hóa mật khẩu an toàn theo chuẩn bcrypt
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Chèn dữ liệu User mới vào bảng `users`
                $sqlInsert = "INSERT INTO users (fullname, username, email, password) VALUES (?, ?, ?, ?)";
                $stmtInsert = $db->prepare($sqlInsert);

                try {
                    if ($stmtInsert->execute([$fullname, $username, $email, $hashed_password])) {
                        $success = "Đăng ký thành công! Hãy đăng nhập để tiếp tục.";
                    } else {
                        $error = "Đã xảy ra lỗi khi tạo tài khoản, vui lòng thử lại sau.";
                    }
                } catch (PDOException $e) {
                    // Xử lý lỗi nếu database cấu hình ràng buộc chặt chẽ
                    $error = "Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage();
                }
            }
        }
    }
}
