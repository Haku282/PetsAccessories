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
    'need_import_stock' => 0,
];

if ($db instanceof PDO) {
    try {
        $apiStats = $db->query("SELECT 
            (SELECT COUNT(*) FROM orders) AS total_orders,
            (SELECT COUNT(*) FROM products) AS total_products,
            (SELECT COUNT(*) FROM users WHERE role = 'customer') AS total_customers,
            (SELECT COALESCE(SUM(total_price),0) FROM orders WHERE order_status = 'completed') AS total_revenue,
            (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') AS pending_orders,
            (SELECT COUNT(*) FROM products WHERE stock_quantity = 0) AS out_of_stock,
            (SELECT COUNT(*) FROM products WHERE stock_quantity <= 5 AND stock_quantity > 0) AS need_import_stock");
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

            <div class="stat-card need-import" onclick="openLowStockModal()">
                <h3><span class="icon">📦</span> Cần Nhập Kho</h3>
                <div class="number"><?php echo $stats['need_import_stock'] ?? 0; ?></div>
                <div class="label">Sản phẩm (Click để xem)</div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-section">
            <h2><span class="icon">📋</span> Đơn Hàng Gần Đây</h2>
            <div class="recent-table-wrapper">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>ID Đơn</th>
                            <th>Khách Hàng</th>
                            <th>Tổng Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Thanh Toán</th>
                            <th>Ngày Tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        try {
                            $stmt = $db->query("SELECT 
                                o.order_id, 
                                o.total_price, 
                                o.order_status, 
                                o.payment_status,
                                o.created_at,
                                COALESCE(u.fullname, u.username, 'Khách lạ') as customer_name
                            FROM orders o
                            LEFT JOIN users u ON o.user_id = u.user_id
                            ORDER BY o.created_at DESC
                            LIMIT 5");
                            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($orders as $order):
                                $status_class = strtolower($order['order_status']);
                                $payment_class = strtolower($order['payment_status']);
                        ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td class="price"><?php echo number_format($order['total_price'], 0, ',', '.'); ?> đ</td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php 
                                    $status_labels = [
                                        'pending' => '⏳ Chờ xác nhận',
                                        'confirmed' => '✅ Đã xác nhận',
                                        'shipping' => '🚚 Đang giao',
                                        'completed' => '🎉 Hoàn thành',
                                        'cancelled' => '❌ Hủy'
                                    ];
                                    echo $status_labels[$order['order_status']] ?? $order['order_status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="payment-badge <?php echo $payment_class; ?>">
                                    <?php 
                                    $payment_labels = [
                                        'unpaid' => '❌ Chưa TT',
                                        'paid' => '✅ Đã TT',
                                        'refunded' => '🔄 Hoàn tiền'
                                    ];
                                    echo $payment_labels[$order['payment_status']] ?? $order['payment_status'];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php } catch (Exception $e) { ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Không thể tải dữ liệu</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Best Selling Products -->
        <div class="recent-section">
            <h2><span class="icon">⭐</span> Top 5 Sản Phẩm Bán Chạy</h2>
            <div class="recent-table-wrapper">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th>Danh Mục</th>
                            <th>Giá</th>
                            <th>Tồn Kho</th>
                            <th>Số Lượng Bán</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        try {
                            $stmt = $db->query("SELECT 
                                p.product_id,
                                p.product_name,
                                p.price,
                                p.stock_quantity,
                                p.status,
                                c.category_name,
                                COALESCE(SUM(oi.quantity), 0) as total_sold
                            FROM products p
                            LEFT JOIN categories c ON p.category_id = c.category_id
                            LEFT JOIN order_items oi ON p.product_id = oi.product_id
                            LEFT JOIN orders o ON oi.order_id = o.order_id
                            WHERE o.order_status = 'completed' OR o.order_status IS NULL
                            GROUP BY p.product_id, p.product_name, p.price, p.stock_quantity, p.status, c.category_name
                            ORDER BY total_sold DESC
                            LIMIT 5");
                            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($products as $product):
                                $status_class = $product['status'];
                                $stock_class = $product['stock_quantity'] == 0 ? 'out-of-stock' : 'in-stock';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                            <td class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</td>
                            <td>
                                <span class="stock-badge <?php echo $stock_class; ?>">
                                    <?php echo $product['stock_quantity']; ?>
                                </span>
                            </td>
                            <td style="font-weight: 600; color: #06d6a0;">
                                <?php echo intval($product['total_sold']); ?> sản phẩm
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php 
                                    $status_labels = [
                                        'active' => '✅ Đang bán',
                                        'inactive' => '⛔ Ngừng KD',
                                        'out_of_stock' => '⚠️ Hết hàng'
                                    ];
                                    echo $status_labels[$product['status']] ?? $product['status'];
                                    ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php } catch (Exception $e) { ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Không thể tải dữ liệu</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Stock Import History -->
        <div class="recent-section">
            <h2><span class="icon">📝</span> Lịch Sử Nhập Kho Gần Đây</h2>
            <div class="recent-table-wrapper">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th>Loại</th>
                            <th>Số Lượng</th>
                            <th>Trước / Sau</th>
                            <th>Ghi Chú</th>
                            <th>Ngày</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        try {
                            $stmt = $db->query("SELECT 
                                psl.log_id,
                                psl.product_id,
                                psl.type,
                                psl.quantity,
                                psl.current_stock,
                                psl.new_stock,
                                psl.note,
                                psl.created_at,
                                p.product_name
                            FROM product_stock_logs psl
                            LEFT JOIN products p ON psl.product_id = p.product_id
                            ORDER BY psl.created_at DESC
                            LIMIT 10");
                            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($logs) > 0) {
                                foreach ($logs as $log):
                                    $type_label = $log['type'] === 'import' ? '📦 Nhập' : '📤 Xuất';
                                    $type_color = $log['type'] === 'import' ? '#06d6a0' : '#ff6b6b';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['product_name'] ?? 'N/A'); ?></td>
                            <td><span style="color: <?php echo $type_color; ?>; font-weight: 600;"><?php echo $type_label; ?></span></td>
                            <td style="font-weight: 600; color: #4361ee;"><?php echo $log['quantity']; ?> SP</td>
                            <td style="font-size: 12px;">
                                <span style="color: #999;"><?php echo $log['current_stock']; ?></span>
                                <span style="margin: 0 4px; color: #ccc;">→</span>
                                <span style="color: #06d6a0; font-weight: 600;"><?php echo $log['new_stock']; ?></span>
                            </td>
                            <td style="font-size: 12px; color: #666; max-width: 200px; white-space: normal;"><?php echo htmlspecialchars($log['note'] ?? '-'); ?></td>
                            <td style="font-size: 12px;"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; 
                            } else {
                        ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                                📭 Chưa có lịch sử nhập kho
                            </td>
                        </tr>
                        <?php 
                            }
                        } catch (Exception $e) { ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">❌ Không thể tải lịch sử</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Info -->
        <div class="recent-section">
            <h2><span class="icon">ℹ️</span> Thông Tin Hệ Thống</h2>
            <p>Chào mừng <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong> quay lại!</p>
            <div class="system-info">
                <p>📅 Ngày hôm nay: <strong><?php echo date('d/m/Y H:i:s'); ?></strong></p>
                <p>🔧 Sử dụng menu bên trái để quản lý các chức năng khác nhau của hệ thống.</p>
            </div>
        </div>

        <!-- Import Modal -->
        <div id="importModal" class="modal">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3>📦 Nhập Kho Sản Phẩm</h3>
                    <button class="close-btn" onclick="closeImportModal()">×</button>
                </div>
                <div class="modal-body">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="margin: 0; font-size: 13px; color: #666;">Sản Phẩm:</p>
                        <p id="importProductName" style="margin: 5px 0 0 0; font-weight: 600; font-size: 15px;"></p>
                    </div>

                    <div style="background: #fff3cd; padding: 12px; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
                        <p style="margin: 0; font-size: 12px;">
                            ⚠️ <strong>Tồn kho hiện tại:</strong> <span id="currentStock" style="color: #ff6b6b; font-weight: 600;"></span> SP
                        </p>
                    </div>

                    <div class="form-group-vertical">
                        <label for="importQuantity">Số Lượng Nhập Kho *</label>
                        <input type="number" id="importQuantity" placeholder="Nhập số lượng..." min="1" required>
                        <small style="color: #666;">Nhập số lượng cần bổ sung vào kho</small>
                    </div>

                    <div class="form-group-vertical" style="margin-top: 15px;">
                        <label for="importNote">Ghi Chú (tùy chọn)</label>
                        <textarea id="importNote" placeholder="Vd: Nhập hàng từ nhà cung cấp..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-height: 80px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeImportModal()">Hủy</button>
                    <button class="btn btn-primary" id="confirmImportBtn" onclick="submitImport()">💾 Xác Nhận Nhập Kho</button>
                </div>
            </div>
        </div>

        <!-- Low Stock Products Modal -->
        <div id="lowStockModal" class="modal">
            <div class="modal-content" style="max-width: 900px;">
                <div class="modal-header">
                    <h3>📦 Sản Phẩm Cần Nhập Kho (< 10)</h3>
                    <button class="close-btn" onclick="closeLowStockModal()">×</button>
                </div>
                <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                    <div class="recent-table-wrapper">
                        <table class="recent-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Ảnh</th>
                                    <th>Sản Phẩm</th>
                                    <th>Danh Mục</th>
                                    <th>Giá</th>
                                    <th>Tồn Kho</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockTableBody">
                                <tr><td colspan="6" style="text-align: center; padding: 30px;">⏳ Đang tải...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Stats Modal -->
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
            // ===== Import Modal Functions =====
            let importData = {
                product_id: null,
                current_stock: null
            };

            function showImportModal(productId, productName, currentStock) {
                importData.product_id = productId;
                importData.current_stock = currentStock;

                document.getElementById('importProductName').textContent = productName;
                document.getElementById('currentStock').textContent = currentStock;
                document.getElementById('importQuantity').value = '';
                document.getElementById('importNote').value = '';
                
                // Đóng low stock modal trước
                closeLowStockModal();
                
                // Mở import modal
                document.getElementById('importModal').classList.add('show');
            }

            function closeImportModal() {
                document.getElementById('importModal').classList.remove('show');
            }

            function submitImport() {
                const quantity = parseInt(document.getElementById('importQuantity').value);
                const note = document.getElementById('importNote').value;

                if (!quantity || quantity < 1) {
                    alert('❌ Vui lòng nhập số lượng hợp lệ');
                    return;
                }

                const btn = document.getElementById('confirmImportBtn');
                btn.disabled = true;
                btn.textContent = '⏳ Đang xử lý...';

                fetch('/PetsAccessories/admin/backend/api/products/update-stock.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: importData.product_id,
                        quantity: quantity,
                        note: note,
                        type: 'import'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Nhập kho thành công!');
                        closeImportModal();
                        location.reload();
                    } else {
                        alert('❌ Lỗi: ' + data.message);
                    }
                })
                .catch(err => alert('❌ Lỗi kết nối: ' + err.message))
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = '💾 Xác Nhận Nhập Kho';
                });
            }

            // ===== Low Stock Modal Functions =====
            function openLowStockModal() {
                document.getElementById('lowStockModal').classList.add('show');
                loadLowStockProducts();
            }

            function closeLowStockModal() {
                document.getElementById('lowStockModal').classList.remove('show');
            }

            function loadLowStockProducts() {
                fetch('/PetsAccessories/admin/backend/api/products/list.php?filter=low_stock&limit=20')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.data && data.data.length > 0) {
                            let html = '';
                            data.data.forEach(product => {
                                const stockClass = product.stock_quantity < 5 ? 'critical' : 'warning';
                                html += `
                                    <tr>
                                        <td>
                                            <img src="${product.thumbnail ? '/PetsAccessories/admin/backend/uploads/products/' + product.thumbnail : '/PetsAccessories/frontend/public/images/default.jpg'}" 
                                                 alt="${product.product_name}"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        </td>
                                        <td>${product.product_name}</td>
                                        <td>${product.category_name || '-'}</td>
                                        <td class="price">${new Intl.NumberFormat('vi-VN').format(product.price)} đ</td>
                                        <td>
                                            <span class="stock-badge ${stockClass}">
                                                ⚠️ ${product.stock_quantity} SP
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" onclick="showImportModal(${product.product_id}, '${product.product_name}', ${product.stock_quantity})">
                                                📦 Nhập Kho
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                            document.getElementById('lowStockTableBody').innerHTML = html;
                        } else {
                            document.getElementById('lowStockTableBody').innerHTML = `
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px;">
                                        ✅ Tất cả sản phẩm đều có tồn kho đủ (≥ 10)
                                    </td>
                                </tr>
                            `;
                        }
                    })
                    .catch(err => {
                        document.getElementById('lowStockTableBody').innerHTML = `
                            <tr>
                                <td colspan="6" style="text-align: center; color: red;">❌ Lỗi tải dữ liệu</td>
                            </tr>
                        `;
                    });
            }

            // ===== Revenue Stats Modal Functions =====
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
                            throw new Error('API doanh thu trả về dữ liệu không hợp lệ.');
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

            // ===== Modal Event Listeners =====
            document.getElementById('importModal')?.addEventListener('click', (e) => {
                if (e.target.id === 'importModal') closeImportModal();
            });

            document.getElementById('lowStockModal')?.addEventListener('click', (e) => {
                if (e.target.id === 'lowStockModal') closeLowStockModal();
            });
        </script>

<?php 
require_once __DIR__ . '/layout/footer.php'; 
?>
