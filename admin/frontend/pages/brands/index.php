<?php
/**
 * Trang quản lý thương hiệu
 * File: /admin/frontend/pages/brands/index.php
 */

// Kiểm tra quyền admin
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thương Hiệu - Admin</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/categories.css">
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><span>🏷️</span> Quản Lý Thương Hiệu</h1>
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
                <li><a href="/PetsAccessories/admin/frontend/pages/brands/index.php" class="active"><span>🏷️</span> Thương Hiệu</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php"><span>👥</span> Người Dùng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons.php"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners.php"><span>🖼️</span> Banner</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="brands-container">
            <!-- Messages Container -->
            <div id="messagesContainer"></div>

            <!-- Header Section -->
            <div class="brands-header">
                <h2>🏷️ Thương Hiệu Sản Phẩm</h2>
                <div class="brands-header-actions">
                    <button class="btn btn-primary" id="addBrandBtn">
                        ➕ Thêm Thương Hiệu
                    </button>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-filter-section">
                <div class="search-filter-grid">
                    <div class="form-group">
                        <label for="searchInput">🔍 Tìm kiếm</label>
                        <input type="text" id="searchInput" placeholder="Tên thương hiệu hoặc mô tả...">
                    </div>
                </div>

                <div class="search-filter-actions">
                    <button class="btn btn-secondary" id="resetBtn">↻ Làm mới</button>
                    <button class="btn btn-primary" id="filterBtn">🔍 Tìm kiếm</button>
                </div>
            </div>

            <!-- Brands Table -->
            <div class="table-wrapper">
                <table class="brands-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Tên Thương Hiệu</th>
                            <th>Mô Tả</th>
                            <th style="width: 120px;">Sản Phẩm</th>
                            <th style="width: 180px;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px;">
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

        <!-- Brand Modal -->
        <div id="brandModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="brandModalTitle">➕ Thêm Thương Hiệu</h3>
                    <button class="close-btn" id="closeModalBtn">×</button>
                </div>
                <div class="modal-body">
                    <form id="brandForm">
                        <div class="form-group-vertical">
                            <label for="brandNameInput">Tên Thương Hiệu *</label>
                            <input type="text" id="brandNameInput" placeholder="Nhập tên thương hiệu..." required>
                            <div class="help-text">Tối đa 100 ký tự</div>
                        </div>

                        <div class="form-group-vertical">
                            <label for="brandDescriptionInput">Mô Tả</label>
                            <textarea id="brandDescriptionInput" placeholder="Nhập mô tả thương hiệu..."></textarea>
                            <div class="help-text">Tối đa 1000 ký tự</div>
                        </div>

                        <div class="logo-upload">
                            <label>Logo Thương Hiệu</label>
                            <div class="logo-preview" id="logoPreview"></div>
                            <button type="button" class="upload-btn" id="logoUploadBtn">
                                📁 Chọn Logo
                            </button>
                            <input type="file" id="logoUploadInput" class="logo-upload-input" accept="image/*">
                            <input type="hidden" id="brandLogoInput">
                            <div class="help-text">Hình ảnh PNG, JPG, GIF... (tối đa 2MB)</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelModalBtn">Hủy</button>
                    <button class="btn btn-primary" id="saveBrandBtn">💾 Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/PetsAccessories/admin/frontend/assets/js/brands.js"></script>
</body>

</html>
