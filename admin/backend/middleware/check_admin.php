<?php
/**
 * Middleware kiểm tra quyền admin
 * Sử dụng ở đầu các file admin để bảo vệ trang
 */

session_start();

// Kiểm tra nếu user chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /PetsAccessories/frontend/components/login.php");
    exit;
}

// Kiểm tra nếu user không phải admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /PetsAccessories/frontend/public/index.php");
    exit;
}

// Nếu vượt qua tất cả kiểm tra, tiếp tục
?>
