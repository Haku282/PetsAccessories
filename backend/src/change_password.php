<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /PetsAccessories/frontend/components/login.php');
    exit;
}

$error = '';
$success = '';
$db = $pdo;

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        } elseif ($newPassword !== $confirmNewPassword) {
            $error = 'Mật khẩu mới và xác nhận mật khẩu không khớp.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        } elseif ($newPassword === $currentPassword) {
            $error = 'Mật khẩu mới phải khác mật khẩu hiện tại.';
        } else {
            try {
                $stmt = $db->prepare('SELECT id, password FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = 'Không tìm thấy tài khoản của bạn.';
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $error = 'Mật khẩu hiện tại không chính xác.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateStmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $updated = $updateStmt->execute([$hashedPassword, (int) $_SESSION['user_id']]);

                    if ($updated) {
                        $success = 'Đổi mật khẩu thành công!';
                    } else {
                        $error = 'Không thể cập nhật mật khẩu. Vui lòng thử lại.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Có lỗi hệ thống, vui lòng thử lại sau.';
            }
        }
    }
}
