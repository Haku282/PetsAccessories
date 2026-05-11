<?php require_once __DIR__ . '/../../../backend/middleware/check_admin.php'; 
$pageTitle = 'Quản Lý Đơn Hàng - Admin';
$extraCss = '<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/orders.css">';
$extraJs = '<script src="/PetsAccessories/admin/frontend/assets/js/orders.js"></script>';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="orders-container">
            <div id="messagesContainer"></div>

            <div class="orders-header">
                <h2>🛒 Danh Sách Đơn Hàng</h2>
            </div>

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
                            <th style="width: 220px; text-align: center;">Hành Động</th>
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

            <div class="pagination-container"></div>
        </div>

        <div id="orderModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>📋 Chi Tiết Đơn Hàng</h3>
                    <button class="close-btn" id="closeModalBtn">×</button>
                </div>
                <div class="modal-body">
                    </div>
                <div class="modal-footer">
                    <button class="btn btn-info" id="exportPdfBtn">📄 Xuất PDF</button>
                    <button class="btn btn-info" id="exportExcelBtn">📊 Xuất Excel</button>
                    <button class="btn btn-warning" id="updateStatusBtn">📝 Cập Nhật Trạng Thái</button>
                    <button class="btn btn-secondary" id="cancelModalBtn">Đóng</button>
                </div>
            </div>
        </div>

        <div id="statusModal" class="modal">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3>📝 Cập Nhật Trạng Thái Đơn Hàng</h3>
                    <button class="close-btn" id="closeStatusModalBtn" onclick="document.getElementById('statusModal').classList.remove('show')">×</button>
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

                    <div class="form-group-vertical" style="margin-top: 15px;">
                        <label for="reasonInput">Lý Do (tùy chọn)</label>
                        <textarea id="reasonInput" placeholder="Nhập lý do thay đổi trạng thái..." style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd; min-height: 80px;"></textarea>
                        <div class="help-text" style="font-size: 12px; color: #666; margin-top: 5px;">Ghi chú này sẽ được lưu vào lịch sử đơn hàng</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelStatusBtn">Hủy</button>
                    <button class="btn btn-primary" id="confirmStatusBtn">💾 Cập Nhật</button>
                </div>
            </div>
        </div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>