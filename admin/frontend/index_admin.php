<?php
/**
 * Trang dashboard chính của admin
 * Kiểm tra quyền admin trước khi cho phép truy cập
 */

// Kiểm tra quyền admin
require_once __DIR__ . '/../backend/middleware/check_admin.php';
require_once __DIR__ . '/../../backend/config/database.php';

/** @var PDO $pdo */
$db = $pdo;
$stats = [
    'total_orders' => 0,
    'total_products' => 0,
    'total_customers' => 0,
    'total_revenue' => 0,
    'pending_orders' => 0,
    'out_of_stock' => 0,
];

if ($db instanceof PDO) {
    try {
        $apiStats = $db->query("SELECT 
            (SELECT COUNT(*) FROM orders) AS total_orders,
            (SELECT COUNT(*) FROM products) AS total_products,
            (SELECT COUNT(*) FROM users WHERE role = 'customer') AS total_customers,
            (SELECT COALESCE(SUM(total_price),0) FROM orders WHERE order_status = 'completed') AS total_revenue,
            (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') AS pending_orders,
            (SELECT COUNT(*) FROM products WHERE stock_quantity = 0) AS out_of_stock");
        $row = $apiStats->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $stats = array_merge($stats, $row);
        }
    } catch (Exception $e) {
        error_log('Error fetching stats: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quản Trị Viên</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><span>📊</span> Dashboard Quản Trị</h1>
            </div>
            <div class="user-info">
                <span>Xin chào: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                <a href="/PetsAccessories/frontend/components/logout.php" class="logout-btn">🚪 Đăng Xuất</a>
            </div>
        </div>

        <!-- Menu -->
        <div class="menu">
            <ul>
                <li><a href="/PetsAccessories/admin/frontend/index_admin.php"><span>📊</span> Dashboard</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/products/index.php"><span>📦</span> Sản Phẩm</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/orders/index.php"><span>🛒</span> Đơn Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/categories/index.php"><span>📁</span> Danh Mục</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/brands/index.php"><span>🏷️</span> Thương Hiệu</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php"><span>👥</span> Người Dùng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/clients/index.php"><span>👥</span> Khách Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons/index.php"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/reviews/index.php"><span>⭐</span> Đánh Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/shipping/index.php"><span>🚚</span> Giao Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/cms_pages/index.php"><span>📝</span> CMS</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/cms_posts/index.php"><span>📰</span> Bài Viết</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners/index.php"><span>🖼️</span> Banner</a></li>
            </ul>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card orders">
                <h3><span class="icon">📊</span> Tổng Đơn Hàng</h3>
                <div class="number"><?php echo $stats['total_orders'] ?? 0; ?></div>
                <div class="label">Tất cả đơn hàng</div>
            </div>

            <div class="stat-card products">
                <h3><span class="icon">📦</span> Tổng Sản Phẩm</h3>
                <div class="number"><?php echo $stats['total_products'] ?? 0; ?></div>
                <div class="label">Sản phẩm trong kho</div>
            </div>

            <div class="stat-card customers">
                <h3><span class="icon">👥</span> Khách Hàng</h3>
                <div class="number"><?php echo $stats['total_customers'] ?? 0; ?></div>
                <div class="label">Người dùng đã đăng ký</div>
            </div>

            <div class="stat-card revenue">
                <h3><span class="icon">💰</span> Doanh Thu</h3>
                <div class="number"><?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?> ₫</div>
                <div class="label">Từ các đơn hàng hoàn thành</div>
            </div>

            <div class="stat-card pending">
                <h3><span class="icon">⏳</span> Đơn Chờ Xác Nhận</h3>
                <div class="number"><?php echo $stats['pending_orders'] ?? 0; ?></div>
                <div class="label">Cần xử lý ngay</div>
            </div>

            <div class="stat-card stock">
                <h3><span class="icon">⚠️</span> Hết Hàng</h3>
                <div class="number"><?php echo $stats['out_of_stock'] ?? 0; ?></div>
                <div class="label">Sản phẩm cần nhập hàng</div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-section">
            <h2><span>ℹ️</span> Thông Tin Hệ Thống</h2>
            <p>Chào mừng <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> quay lại!</p>
            <div class="system-info">
                <p>📅 Ngày hôm nay: <strong><?php echo date('d/m/Y H:i:s'); ?></strong></p>
                <p>🔧 Sử dụng menu trên để quản lý các chức năng khác nhau của hệ thống.</p>
            </div>
        </div>
    </div>
</body>

</html>
