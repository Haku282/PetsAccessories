<?php
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';

$pageTitle = 'Quản Lý Khiếu Nại / Báo Cáo';
$extraCss = '<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/orders.css">';
$extraJs = '<script src="/PetsAccessories/admin/frontend/assets/js/review_reports.js"></script>';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="orders-container">
    <div id="messagesContainer"></div>

    <div class="orders-header">
        <h2>Danh Sách Khiếu Nại / Báo Cáo Sai Phạm</h2>
    </div>

    <div class="stats-container" id="reportStatsContainer">
        <div class="stat-card">
            <h3>Tất Cả Báo Cáo</h3>
            <p id="totalReports">0</p>
        </div>
        <div class="stat-card" style="border-left-color: #FFC107;">
            <h3>Chờ Xử Lý</h3>
            <p id="pendingReports">0</p>
        </div>
        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3>Đã Xử Lý</h3>
            <p id="resolvedReports">0</p>
        </div>
        <div class="stat-card" style="border-left-color: #f44336;">
            <h3>Đã Bỏ Qua</h3>
            <p id="rejectedReports">0</p>
        </div>
    </div>

    <div class="search-filter-section">
        <div class="search-filter-grid">
            <div class="form-group">
                <label for="statusFilter">Trạng thái</label>
                <select id="statusFilter">
                    <option value="">Tất cả</option>
                    <option value="0">Chờ xử lý</option>
                    <option value="1">Đã xử lý</option>
                    <option value="2">Đã bỏ qua</option>
                </select>
            </div>
            <div class="form-group">
                <label for="searchInput">Tìm kiếm</label>
                <input type="text" id="searchInput" placeholder="Sản phẩm, người báo cáo, nội dung...">
            </div>
        </div>
        <div class="search-filter-actions">
            <button class="btn btn-secondary" id="resetBtn">Làm mới</button>
            <button class="btn btn-primary" id="filterBtn">Tìm kiếm</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="orders-table" id="reportsTable">
            <thead>
                <tr>
                    <th>Mã BC</th>
                    <th>Sản phẩm</th>
                    <th>Người báo cáo</th>
                    <th>Người đánh giá</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="8" style="text-align: center;">Đang tải dữ liệu...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="reportModal" class="modal">
    <div class="modal-content" style="max-width: 720px;">
        <div class="modal-header">
            <h3>Chi Tiết Khiếu Nại / Báo Cáo</h3>
            <button class="close-btn" type="button" onclick="reportManager.closeModal()">×</button>
        </div>
        <div class="modal-body" id="reportDetailsContent"></div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
