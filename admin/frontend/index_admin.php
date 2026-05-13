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

            <div class="stat-card revenue clickable-stat" id="revenueStatCard" role="button" tabindex="0" aria-label="Xem chi tiết doanh thu">
                <h3><span class="icon">💰</span> Doanh Thu</h3>
                <div class="number"><?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?> đ</div>
                <div class="label">Từ đơn hàng hoàn thành • Bấm để xem chi tiết</div>
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

        <div class="modal" id="revenueStatsModal">
            <div class="modal-content revenue-modal-content">
                <div class="modal-header">
                    <h3>💰 Chi Tiết Doanh Thu</h3>
                    <button type="button" class="modal-close" id="closeRevenueModalBtn">×</button>
                </div>
                <div class="modal-body revenue-modal-body">
                    <div class="revenue-controls">
                        <label for="revenueGroupBy">Nhóm theo kỳ:</label>
                        <select id="revenueGroupBy">
                            <option value="day">Ngày</option>
                            <option value="month" selected>Tháng</option>
                            <option value="year">Năm</option>
                        </select>
                    </div>

                    <div class="revenue-kpi-grid">
                        <div class="revenue-kpi">
                            <span class="kpi-label">Tổng doanh thu</span>
                            <strong class="kpi-value" id="revenueModalTotal">0 đ</strong>
                        </div>
                        <div class="revenue-kpi">
                            <span class="kpi-label">Tổng số kỳ</span>
                            <strong class="kpi-value" id="revenueModalPeriods">0</strong>
                        </div>
                        <div class="revenue-kpi">
                            <span class="kpi-label">Tổng đơn hoàn thành</span>
                            <strong class="kpi-value" id="revenueModalOrders">0</strong>
                        </div>
                    </div>

                    <div class="table-wrapper revenue-table-wrapper">
                        <table class="orders-table revenue-table">
                            <thead>
                                <tr>
                                    <th>Kỳ</th>
                                    <th>Số đơn hoàn thành</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody id="revenueStatsBody">
                                <tr><td colspan="3" style="text-align: center;">Đang tải dữ liệu...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeRevenueModalFooterBtn">Đóng</button>
                </div>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const revenueCard = document.getElementById('revenueStatCard');
    const revenueModal = document.getElementById('revenueStatsModal');
    const revenueGroupBy = document.getElementById('revenueGroupBy');
    const revenueStatsBody = document.getElementById('revenueStatsBody');
    const revenueModalTotal = document.getElementById('revenueModalTotal');
    const revenueModalPeriods = document.getElementById('revenueModalPeriods');
    const revenueModalOrders = document.getElementById('revenueModalOrders');
    const closeModalBtn = document.getElementById('closeRevenueModalBtn');
    const closeModalFooterBtn = document.getElementById('closeRevenueModalFooterBtn');

    const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0
    }).format(value || 0);

    const renderRows = (periods) => {
        if (!Array.isArray(periods) || periods.length === 0) {
            revenueStatsBody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Chưa có dữ liệu doanh thu</td></tr>';
            revenueModalPeriods.textContent = '0';
            revenueModalOrders.textContent = '0';
            return;
        }

        const totalOrders = periods.reduce((sum, item) => sum + Number(item.order_count || 0), 0);
        revenueModalPeriods.textContent = new Intl.NumberFormat('vi-VN').format(periods.length);
        revenueModalOrders.textContent = new Intl.NumberFormat('vi-VN').format(totalOrders);

        revenueStatsBody.innerHTML = periods.map((item) => `
            <tr>
                <td>${item.period_label}</td>
                <td>${item.order_count}</td>
                <td><strong>${formatCurrency(item.revenue)}</strong></td>
            </tr>
        `).join('');
    };

    const loadRevenueStats = async (groupBy = 'month') => {
        revenueStatsBody.innerHTML = '<tr><td colspan="3" style="text-align: center;">Đang tải dữ liệu...</td></tr>';
        revenueModalPeriods.textContent = '--';
        revenueModalOrders.textContent = '--';
        try {
            const response = await fetch(`/PetsAccessories/admin/backend/api/statistics.php?mode=revenue_breakdown&group_by=${encodeURIComponent(groupBy)}`);
            const rawText = await response.text();
            let data;
            try {
                data = JSON.parse(rawText);
            } catch (parseError) {
                throw new Error('API doanh thu trả về dữ liệu không hợp lệ. Vui lòng tải lại trang.');
            }

            if (!response.ok) {
                throw new Error(data.message || 'Không thể tải thống kê doanh thu');
            }
            if (!data.success) {
                throw new Error(data.message || 'Không thể tải thống kê doanh thu');
            }
            const stats = data.stats || {};
            revenueModalTotal.textContent = formatCurrency(stats.total_revenue);
            renderRows(stats.periods || []);
        } catch (error) {
            revenueStatsBody.innerHTML = `<tr><td colspan="3" style="text-align: center; color: #d32f2f;">${error.message}</td></tr>`;
            revenueModalTotal.textContent = formatCurrency(0);
            revenueModalPeriods.textContent = '0';
            revenueModalOrders.textContent = '0';
        }
    };

    const openModal = async () => {
        revenueModal.classList.add('show');
        revenueGroupBy.value = 'month';
        await loadRevenueStats('month');
    };

    const closeModal = () => {
        revenueModal.classList.remove('show');
    };

    revenueCard?.addEventListener('click', openModal);
    revenueCard?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openModal();
        }
    });

    revenueGroupBy?.addEventListener('change', (event) => {
        loadRevenueStats(event.target.value);
    });

    closeModalBtn?.addEventListener('click', closeModal);
    closeModalFooterBtn?.addEventListener('click', closeModal);
    revenueModal?.addEventListener('click', (event) => {
        if (event.target === revenueModal) {
            closeModal();
        }
    });
});
</script>

<?php 
require_once __DIR__ . '/layout/footer.php'; 
?>
