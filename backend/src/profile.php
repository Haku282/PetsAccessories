<?php
session_start();
require_once __DIR__ . '/../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /PetsAccessories/frontend/components/login.php');
    exit;
}

$db = $pdo;
$error = '';
$success = '';

if (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = trim($_POST['fullname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($fullname)) {
            $error = 'Vui lòng nhập họ tên.';
        } else {
            try {
                $stmt = $db->prepare('UPDATE users SET fullname = ?, phone = ?, address = ? WHERE user_id = ?');
                $updated = $stmt->execute([$fullname, $phone, $address, $_SESSION['user_id']]);

                if ($updated) {
                    $success = 'Cập nhật hồ sơ thành công!';
                    $_SESSION['user_name'] = $fullname; // Update session name

                    // Xử lý Upload Avatar
                    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../../backend/upload/avatar/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                        if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                            // Xóa ảnh cũ theo pattern để tránh mọc ra các file phụ dư thừa khi user up lại
                            $oldFiles = glob($uploadDir . 'avatar_' . $_SESSION['user_id'] . '.*');
                            if ($oldFiles) {
                                foreach ($oldFiles as $oldFile) {
                                    unlink($oldFile);
                                }
                            }
                            // Lưu ảnh mới với format avatar_<user_id>.ext
                            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . 'avatar_' . $_SESSION['user_id'] . '.' . $fileExt);
                        } else {
                            $error = 'Hồ sơ đã lưu nhưng Ảnh Avatar bị từ chối (Chỉ cho phép JPG, PNG, GIF).';
                        }
                    }
                } else {
                    $error = 'Không thể cập nhật hồ sơ. Vui lòng thử lại.';
                }
            } catch (PDOException $e) {
                // Ignore missing columns if phone or address do not exist
                $error = 'Có lỗi hệ thống trong quá trình cập nhật, bạn có thể kiểm tra lại database schema.';
            }
        }
    }

    try {
        $stmt = $db->prepare('SELECT username, email, fullname, phone, address FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'Không tìm thấy tài khoản của bạn.';
            $user = ['username' => '', 'email' => '', 'fullname' => '', 'phone' => '', 'address' => ''];
        }
    } catch (PDOException $e) {
        $user = ['username' => '', 'email' => '', 'fullname' => '', 'phone' => '', 'address' => ''];
        // Tạm thời in lỗi thật ra màn hình để biết thiếu cột nào
        $error = 'Lỗi SQL: ' . $e->getMessage();
    }
}

// Xử lý load Avatar URL
$avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode(!empty($user['fullname']) ? $user['fullname'] : ($user['username'] ?? 'User')) . '&background=random&color=fff&size=100&bold=true';
if (isset($_SESSION['user_id'])) {
    $avatarGlob = glob(__DIR__ . '/../../backend/upload/avatar/avatar_' . $_SESSION['user_id'] . '.*');
    if (!empty($avatarGlob)) {
        // Đính kèm ?t=time() để trình duyệt không load cache cũ khi user vừa tải ảnh mới lên
        $avatarUrl = '/PetsAccessories/backend/upload/avatar/' . basename($avatarGlob[0]) . '?t=' . time();
    }
}
