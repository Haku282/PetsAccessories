<?php
/**
 * Root index.php - Điểm vào chính của ứng dụng
 * Logic: Mặc định hiển thị trang shop, nếu admin thì redirect tới dashboard
 */

session_start();

// Nếu đã login và là admin, redirect tới admin dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: /PetsAccessories/admin/frontend/index_admin.php");
    exit;
}

// Mặc định, redirect tới trang shop
header("Location: /PetsAccessories/frontend/public/index.php");
exit;
?>
