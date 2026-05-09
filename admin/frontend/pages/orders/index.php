<?php
/**
 * Trang quản lý đơn hàng
 * File: /admin/frontend/pages/orders/index.php
 */

// Kiểm tra quyền admin
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - Admin</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/orders.css">
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><span>🛒</span> Quản Lý Đơn Hàng</h1>
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
                <li><a href="/PetsAccessories/admin/frontend/pages/orders/index.php" class="active"><span>🛒</span> Đơn Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/categories/index.php"><span>📁</span> Danh Mục</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/brands/index.php"><span>🏷️</span> Thương Hiệu</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php"><span>👥</span> Người Dùng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons.php"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners.php"><span>🖼️</span> Banner</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="orders-container">
            <!-- Messages Container -->
            <div id="messagesContainer"></div>

            <!-- Header Section -->
            <div class="orders-header">
                <h2>🛒 Đơn Hàng</h2>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-filter-section">
                <div class="search-filter-grid">
                    <div class="form-group">
                        <label for="searchInput">🔍 Tìm kiếm</label>
                        <input type="text" id="searchInput" placeholder="Tên khách, email, ID đơn...">
                    </div>

                    <div class="form-group">
                        <label for="statusFilter">📊 Trạng Thái Đơn Hàng</label>
                        <select id="statusFilter">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="pending">⏳ Chờ xác nhận</option>
                            <option value="confirmed">✅ Đã xác nhận</option>
                            <option value="shipping">🚚 Đang giao</option>
                            <option value="completed">🎉 Hoàn thành</option>
                            <option value="cancelled">❌ Hủy</option>
                        </select>
                    </div>
                </div>

                <div class="search-filter-actions">
                    <button class="btn btn-secondary" id="resetBtn">↻ Làm mới</button>
                    <button class="btn btn-primary" id="filterBtn">🔍 Tìm kiếm</button>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID Đơn</th>
                            <th>Khách Hàng</th>
                            <th style="width: 100px;">Sản Phẩm</th>
                            <th style="width: 120px;">Tổng Tiền</th>
                            <th style="width: 140px;">Trạng Thái</th>
                            <th style="width: 140px;">Thanh Toán</th>
                            <th style="width: 120px;">Ngày Tạo</th>
                            <th style="width: 180px;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <div class="loading"></div>
                                <p style="margin-top: 15px;">Đang tải...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container"></div>
        </div>

        <!-- Order Detail Modal -->
        <div id="orderModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>📋 Chi Tiết Đơn Hàng</h3>
                    <button class="close-btn" id="closeModalBtn">×</button>
                </div>
                <div class="modal-body">
                    <!-- Chi tiết sẽ được load qua JavaScript -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-info" id="exportPdfBtn">📄 Xuất PDF</button>
                    <button class="btn btn-info" id="exportExcelBtn">📊 Xuất Excel</button>
                    <button class="btn btn-warning" id="updateStatusBtn">📝 Cập Nhật Trạng Thái</button>
                    <button class="btn btn-secondary" id="cancelModalBtn">Đóng</button>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div id="statusModal" class="modal">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3>📝 Cập Nhật Trạng Thái Đơn Hàng</h3>
                    <button class="close-btn" id="closeStatusModalBtn" onclick="ordersManager.closeStatusModal()">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group-vertical">
                        <label for="newStatusSelect">Trạng Thái Mới *</label>
                        <select id="newStatusSelect" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="pending">⏳ Chờ xác nhận</option>
                            <option value="confirmed">✅ Đã xác nhận</option>
                            <option value="shipping">🚚 Đang giao</option>
                            <option value="completed">🎉 Hoàn thành</option>
                            <option value="cancelled">❌ Hủy</option>
                        </select>
                    </div>

                    <div class="form-group-vertical">
                        <label for="reasonInput">Lý Do (tùy chọn)</label>
                        <textarea id="reasonInput" placeholder="Nhập lý do thay đổi trạng thái..."></textarea>
                        <div class="help-text">Ghi chú này sẽ được lưu vào lịch sử đơn hàng</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelStatusBtn">Hủy</button>
                    <button class="btn btn-primary" id="confirmStatusBtn">💾 Cập Nhật</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/PetsAccessories/admin/frontend/assets/js/orders.js"></script>
</body>

</html>
