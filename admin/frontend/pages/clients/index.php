<?php
/**
 * Trang quản lý Khách Hàng (Clients)
 * File: /admin/frontend/pages/clients/index.php
 */

// Thêm 1 cấp "../" vì file index.php nằm trong thư mục clients/
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khách Hàng - Admin</title>
    
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/orders.css"> 
    
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/clients.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div><h1><span>👥</span> Quản Lý Khách Hàng</h1></div>
            <div class="user-info">
                <span>Xin chào: <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong></span>
                <a href="/PetsAccessories/frontend/components/logout.php" class="logout-btn">🚪 Đăng Xuất</a>
            </div>
        </div>

        <div class="menu">
            <ul>
                <li><a href="/PetsAccessories/admin/frontend/index_admin.php"><span>📊</span> Dashboard</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/products/index.php"><span>📦</span> Sản Phẩm</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/orders/index.php"><span>🛒</span> Đơn Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/categories/index.php"><span>📁</span> Danh Mục</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/brands/index.php"><span>🏷️</span> Thương Hiệu</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php"><span>👥</span> Người Dùng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/clients/index.php" class="active"><span>👥</span> Khách Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons/index.php"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners/index.php"><span>🖼️</span> Banner</a></li>
            </ul>
        </div>

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
    </div>

    <script src="/PetsAccessories/admin/frontend/assets/js/clients.js"></script>
</body>
</html>