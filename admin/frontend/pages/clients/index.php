<?php require_once __DIR__ . '/../../../backend/middleware/check_admin.php'; 
$pageTitle = 'Quản Lý Khách Hàng - Admin';
$extraCss = '<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/orders.css">
<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/clients.css">';
$extraJs = '<script src="/PetsAccessories/admin/frontend/assets/js/clients.js"></script>';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="orders-container">
            <div id="messagesContainer"></div>

            <div class="stats-container" id="clientStats">
                <div class="stat-card">
                    <h3>Tổng Khách Hàng</h3>
                    <p id="totalClients">0</p>
                </div>
                <div class="stat-card" style="border-left-color: #2196F3;">
                    <h3>Khách Hàng Mới (30 ngày)</h3>
                    <p id="newClients">0</p>
                </div>
                <div class="stat-card" style="border-left-color: #FF9800;">
                    <h3>Khách Thường Xuyên (>2 đơn)</h3>
                    <p id="frequentClients">0</p>
                </div>
                <div class="stat-card" style="border-left-color: #9C27B0;">
                    <h3>Khách Tiềm Năng (1-2 đơn)</h3>
                    <p id="potentialClients">0</p>
                </div>
            </div>

            <div class="orders-filters">
                <div class="filter-group">
                    <label for="searchInput">Tìm kiếm khách hàng:</label>
                    <div class="search-wrapper" style="display: flex; gap: 10px;">
                        <input type="text" id="searchInput" placeholder="Nhập tên, email hoặc số điện thoại..." 
                            style="flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px;">
                        
                        <button id="searchBtn" class="action-btn view" style="padding: 0 20px; height: 40px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                            <span>🔍</span> Tìm Kiếm
                        </button>
                        
                        <button id="resetSearchBtn" class="action-btn delete" style="padding: 0 15px; height: 40px; border-radius: 5px; cursor: pointer; display: none; align-items: center; justify-content: center;">
                            ✕ Hủy
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="orders-table" id="clientsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ Tên</th>
                            <th>Thông Tin Liên Hệ</th>
                            <th>Tổng Đơn</th>
                            <th>Tổng Chi Tiêu</th>
                            <th>Ngày Tham Gia</th>
                            <th style="text-align: center;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" style="text-align: center;">Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="clientModal" class="modal">
            <div class="modal-content" style="max-width: 700px;">
                <div class="modal-header">
                    <h3>📋 Chi Tiết Khách Hàng & Lịch Sử Mua</h3>
                    <button class="close-btn" onclick="clientManager.closeModal()">×</button>
                </div>
                <div class="modal-body" id="clientDetailsContent">
                    </div>
            </div>
        </div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
