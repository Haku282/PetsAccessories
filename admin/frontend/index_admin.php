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
$stats = [];

// Lấy thống kê cơ bản
if ($db instanceof PDO) {
    try {
        // Tổng số đơn hàng
        $totalOrdersStmt = $db->query("SELECT COUNT(*) as total FROM orders");
        $stats['total_orders'] = $totalOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Tổng số sản phẩm
        $totalProductsStmt = $db->query("SELECT COUNT(*) as total FROM products");
        $stats['total_products'] = $totalProductsStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Tổng số khách hàng
        $totalCustomersStmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
        $stats['total_customers'] = $totalCustomersStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Tổng doanh thu (từ các đơn hàng đã hoàn thành)
        $totalRevenueStmt = $db->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE order_status = 'completed'");
        $stats['total_revenue'] = $totalRevenueStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Số đơn hàng chưa xác nhận
        $pendingOrdersStmt = $db->query("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'");
        $stats['pending_orders'] = $pendingOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Sản phẩm hết hàng
        $outOfStockStmt = $db->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity = 0");
        $stats['out_of_stock'] = $outOfStockStmt->fetch(PDO::FETCH_ASSOC)['total'];

    } catch (Exception $e) {
        error_log("Error fetching stats: " . $e->getMessage());
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
                <li><a href="/PetsAccessories/admin/frontend/pages/products.php"><span>📦</span> Sản Phẩm</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/orders.php"><span>🛒</span> Đơn Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/categories.php"><span>📁</span> Danh Mục</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php"><span>👥</span> Người Dùng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons.php"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners.php"><span>🖼️</span> Banner</a></li>
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
