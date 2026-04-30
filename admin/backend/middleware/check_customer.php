<?php
/**
 * Middleware kiểm tra quyền customer
 * Sử dụng ở các page yêu cầu đăng nhập của customer
 */

session_start();

// Kiểm tra nếu user chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /PetsAccessories/frontend/components/login.php");
    exit;
}

// Kiểm tra nếu user là admin, chuyển hướng tới admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: /PetsAccessories/admin/frontend/index_admin.php");
    exit;
}

// Nếu vượt qua tất cả kiểm tra, tiếp tục
?>
