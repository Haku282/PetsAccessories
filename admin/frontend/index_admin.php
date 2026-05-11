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

$pageTitle = 'Dashboard';
require_once __DIR__ . '/layout/header.php';
?>

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
                <div class="label">Người dùng đăng ký</div>
            </div>

            <div class="stat-card revenue">
                <h3><span class="icon">💰</span> Doanh Thu</h3>
                <div class="number"><?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?> đ</div>
                <div class="label">Từ đơn hàng hoàn thành</div>
            </div>

            <div class="stat-card pending">
                <h3><span class="icon">⏳</span> Đơn Chờ Xử Lý</h3>
                <div class="number"><?php echo $stats['pending_orders'] ?? 0; ?></div>
                <div class="label">Cần xác nhận</div>
            </div>

            <div class="stat-card out-of-stock">
                <h3><span class="icon">⚠️</span> Hết Hàng</h3>
                <div class="number"><?php echo $stats['out_of_stock'] ?? 0; ?></div>
                <div class="label">Sản phẩm cần nhập hàng</div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-section">
            <h2><span class="icon">ℹ️</span> Thông Tin Hệ Thống</h2>
            <p>Chào mừng <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong> quay lại!</p>
            <div class="system-info">
                <p>📅 Ngày hôm nay: <strong><?php echo date('d/m/Y H:i:s'); ?></strong></p>
                <p>🔧 Sử dụng menu bên trái để quản lý các chức năng khác nhau của hệ thống.</p>
            </div>
        </div>

<?php 
require_once __DIR__ . '/layout/footer.php'; 
?>