<?php require_once __DIR__ . '/../../../backend/middleware/check_admin.php'; 
$pageTitle = 'Yêu Cầu Đổi/Trả - Admin';
$extraCss = '<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/orders.css">';
$extraJs = '<script src="/PetsAccessories/admin/frontend/assets/js/return_requests.js"></script>';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="orders-container">
    <div id="messagesContainer"></div>

    <div class="stats-container" id="statsContainer">
        <div class="stat-card">
            <h3>Tất Cả Yêu Cầu</h3>
            <p id="totalRequests">0</p>
        </div>
        <div class="stat-card" style="border-left-color: #FFC107;">
            <h3>Đang Xử Lý</h3>
            <p id="pendingRequests">0</p>
        </div>
        <div class="stat-card" style="border-left-color: #4CAF50;">
            <h3>Đã Phê Duyệt</h3>
            <p id="approvedRequests">0</p>
        </div>
        <div class="stat-card" style="border-left-color: #f44336;">
            <h3>Đã Từ Chối</h3>
            <p id="rejectedRequests">0</p>
        </div>
        <div class="stat-card" style="border-left-color: #2196F3;">
            <h3>Đã Hoàn Thành</h3>
            <p id="completedRequests">0</p>
        </div>
    </div>

    <div class="orders-filters">
        <div class="filter-group">
            <label for="statusFilter">Lọc theo trạng thái:</label>
            <select id="statusFilter" class="filter-select">
                <option value="">-- Tất cả --</option>
                <option value="pending">Đang Xử Lý</option>
                <option value="approved">Đã Phê Duyệt</option>
                <option value="rejected">Đã Từ Chối</option>
                <option value="completed">Đã Hoàn Thành</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="typeFilter">Lọc theo loại:</label>
            <select id="typeFilter" class="filter-select">
                <option value="">-- Tất cả --</option>
                <option value="return">Trả Hàng</option>
                <option value="exchange">Đổi Hàng</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="searchInput">Tìm kiếm:</label>
            <input type="text" id="searchInput" placeholder="Tên khách, email, số điện thoại...">
        </div>
    </div>

    <div class="table-wrapper">
        <table class="orders-table" id="requestsTable">
            <thead>
                <tr>
                    <th>Mã YC</th>
                    <th>Tên Khách Hàng</th>
                    <th>Số Điện Thoại</th>
                    <th>Loại</th>
                    <th>Lý Do</th>
                    <th>Trạng Thái</th>
                    <th>Ngày Tạo</th>
                    <th style="text-align: center;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="8" style="text-align: center;">Đang tải dữ liệu...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal chi tiết yêu cầu -->
<div id="requestModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Chi Tiết Yêu Cầu Đổi/Trả</h3>
            <button class="close-btn" onclick="requestManager.closeModal()">×</button>
        </div>
        <div class="modal-body" id="requestDetailsContent">
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
