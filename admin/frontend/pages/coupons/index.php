<?php
/**
 * Trang quản lý mã giảm giá
 * File: /admin/frontend/pages/coupons/index.php
 */

// Kiểm tra quyền admin
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Mã Giảm Giá - Admin</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/categories.css">
    <style>
        .type-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .type-fixed { background-color: #e3f2fd; color: #1565c0; }
        .type-percentage { background-color: #f3e5f5; color: #4a148c; }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><span>🎟️</span> Quản Lý Mã Giảm Giá</h1>
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
                <li><a href="/PetsAccessories/admin/frontend/pages/orders/index.php"><span>🛒</span> Đơn Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/categories/index.php"><span>📁</span> Danh Mục</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/brands/index.php"><span>🏷️</span> Thương Hiệu</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php"><span>👥</span> Người Dùng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/clients/index.php"><span>👥</span> Khách Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons/index.php" class="active"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners/index.php"><span>🖼️</span> Banner</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="brands-container">
            <!-- Messages Container -->
            <div id="messagesContainer"></div>

            <!-- Header Section -->
            <div class="brands-header">
                <h2>🎟️ Danh Sách Mã Giảm Giá</h2>
                <div class="brands-header-actions">
                    <button class="btn btn-primary" id="addCouponBtn">
                        ➕ Thêm Mã Giảm Giá
                    </button>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-filter-section">
                <div class="search-filter-grid">
                    <div class="form-group">
                        <label for="searchInput">🔍 Tìm kiếm mã</label>
                        <input type="text" id="searchInput" placeholder="Nhập mã giảm giá...">
                    </div>
                    <div class="form-group">
                        <label for="statusFilter">Trạng thái</label>
                        <select id="statusFilter">
                            <option value="">Tất cả</option>
                            <option value="1">Kích hoạt</option>
                            <option value="0">Vô hiệu hóa</option>
                        </select>
                    </div>
                </div>

                <div class="search-filter-actions">
                    <button class="btn btn-secondary" id="resetBtn">↻ Làm mới</button>
                    <button class="btn btn-primary" id="filterBtn">🔍 Tìm kiếm</button>
                </div>
            </div>

            <!-- Coupons Table -->
            <div class="table-wrapper">
                <table class="brands-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Mã Coupon</th>
                            <th>Loại / Giá trị</th>
                            <th>Đơn tối thiểu</th>
                            <th>Giới hạn / Đã dùng</th>
                            <th>Ngày hết hạn</th>
                            <th>Trạng thái</th>
                            <th style="width: 150px;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="couponsTableBody">
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
            <div class="pagination-container" id="paginationContainer"></div>
        </div>

        <!-- Coupon Modal -->
        <div id="couponModal" class="modal">
            <div class="modal-content" style="max-width: 600px;">
                <div class="modal-header">
                    <h3 id="couponModalTitle">➕ Thêm Mã Giảm Giá</h3>
                    <button class="close-btn" id="closeModalBtn">×</button>
                </div>
                <div class="modal-body">
                    <form id="couponForm">
                        <div class="form-group-vertical">
                            <label for="codeInput">Mã Coupon *</label>
                            <input type="text" id="codeInput" placeholder="Vd: SUMMER2026" required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                            <div class="form-group-vertical">
                                <label for="discountTypeInput">Loại giảm giá *</label>
                                <select id="discountTypeInput" required>
                                    <option value="percentage">Phần trăm (%)</option>
                                    <option value="fixed">Số tiền cố định</option>
                                </select>
                            </div>
                            <div class="form-group-vertical">
                                <label for="discountValueInput">Giá trị giảm *</label>
                                <input type="number" id="discountValueInput" min="0" step="any" placeholder="Vd: 10 hoặc 50000" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                            <div class="form-group-vertical">
                                <label for="minOrderValueInput">Đơn hàng tối thiểu (₫)</label>
                                <input type="number" id="minOrderValueInput" min="0" placeholder="Vd: 200000">
                            </div>
                            <div class="form-group-vertical" id="maxDiscountContainer">
                                <label for="maxDiscountInput">Giảm tối đa (₫)</label>
                                <input type="number" id="maxDiscountInput" min="0" placeholder="Dùng cho giảm %">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                            <div class="form-group-vertical">
                                <label for="usageLimitInput">Số lần dùng tối đa</label>
                                <input type="number" id="usageLimitInput" min="1" placeholder="Để trống nếu không giới hạn">
                            </div>
                            <div class="form-group-vertical">
                                <label for="expiryDateInput">Ngày hết hạn</label>
                                <input type="date" id="expiryDateInput">
                            </div>
                        </div>
                        
                        <div class="form-group-vertical" style="margin-top: 15px;">
                            <label for="statusInput">Trạng thái</label>
                            <select id="statusInput">
                                <option value="1">Kích hoạt</option>
                                <option value="0">Vô hiệu hóa</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelModalBtn">Hủy</button>
                    <button class="btn btn-primary" id="saveCouponBtn">💾 Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/PetsAccessories/admin/frontend/assets/js/coupons.js"></script>
</body>

</html>
