<?php
// admin/frontend/layout/sidebar.php
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isActive = function ($path) use ($currentPath) {
    return strpos($currentPath, $path) !== false ? 'active' : '';
};
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <h2><span>🐾</span> Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="/PetsAccessories/admin/frontend/index_admin.php" class="<?= $isActive('/admin/frontend/index_admin.php') ?>"><i class="icon">📊</i> Dashboard</a></li>
        <li class="menu-header">QUẢN LÝ CỬA HÀNG</li>
        <li><a href="/PetsAccessories/admin/frontend/pages/products/index.php" class="<?= $isActive('/admin/frontend/pages/products/') ?>"><i class="icon">📦</i> Sản Phẩm</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/categories/index.php" class="<?= $isActive('/admin/frontend/pages/categories/') ?>"><i class="icon">📁</i> Danh Mục</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/brands/index.php" class="<?= $isActive('/admin/frontend/pages/brands/') ?>"><i class="icon">🏷️</i> Thương Hiệu</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/orders/index.php" class="<?= $isActive('/admin/frontend/pages/orders/') ?>"><i class="icon">🛒</i> Đơn Hàng</a></li>
        
        <li class="menu-header">KHÁCH HÀNG & MARKETING</li>
        <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php" class="<?= $isActive('/admin/frontend/pages/users/') ?>"><i class="icon">👥</i> Người Dùng</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/clients/index.php" class="<?= $isActive('/admin/frontend/pages/clients/') ?>"><i class="icon">👦</i> Khách Hàng</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/return_requests/index.php" class="<?= $isActive('/admin/frontend/pages/return_requests/') ?>"><i class="icon">🔄</i> Yêu Cầu Đổi/Trả</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/reports/index.php" class="<?= $isActive('/admin/frontend/pages/reports/') ?>"><i class="icon">⚠️</i> Khiếu Nại / Báo Cáo</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/coupons/index.php" class="<?= $isActive('/admin/frontend/pages/coupons/') ?>"><i class="icon">🎟️</i> Mã Giảm Giá</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/reviews/index.php" class="<?= $isActive('/admin/frontend/pages/reviews/') ?>"><i class="icon">⭐</i> Đánh Giá</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/shipping/index.php" class="<?= $isActive('/admin/frontend/pages/shipping/') ?>"><i class="icon">🚚</i> Giao Hàng</a></li>

        <li class="menu-header">NỘI DUNG</li>
        <li><a href="/PetsAccessories/admin/frontend/pages/cms_pages/index.php" class="<?= $isActive('/admin/frontend/pages/cms_pages/') ?>"><i class="icon">📰</i> Tin Tức</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/cms_posts/index.php" class="<?= $isActive('/admin/frontend/pages/cms_posts/') ?>"><i class="icon">🎉</i> Chương Trình Khuyến Mãi</a></li>
        <li><a href="/PetsAccessories/admin/frontend/pages/banners/index.php" class="<?= $isActive('/admin/frontend/pages/banners/') ?>"><i class="icon">🖼️</i> Banners</a></li>
    </ul>
</aside>
