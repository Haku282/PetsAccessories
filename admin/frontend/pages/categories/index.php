<?php
/**
 * Trang quản lý danh mục
 * File: /admin/frontend/pages/categories/index.php
 */

// Kiểm tra quyền admin
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Mục - Admin</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/categories.css">
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><span>📁</span> Quản Lý Danh Mục</h1>
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
                <li><a href="/PetsAccessories/admin/frontend/pages/categories/index.php" class="active"><span>📁</span> Danh Mục</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/brands/index.php"><span>🏷️</span> Thương Hiệu</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php"><span>👥</span> Người Dùng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons.php"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners.php"><span>🖼️</span> Banner</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="categories-container">
            <!-- Messages Container -->
            <div id="messagesContainer"></div>

            <!-- Header Section -->
            <div class="categories-header">
                <h2>📁 Danh Mục Sản Phẩm</h2>
                <div class="categories-header-actions">
                    <button class="btn btn-primary" id="addCategoryBtn">
                        ➕ Thêm Danh Mục
                    </button>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-filter-section">
                <div class="search-filter-grid">
                    <div class="form-group">
                        <label for="searchInput">🔍 Tìm kiếm</label>
                        <input type="text" id="searchInput" placeholder="Tên danh mục...">
                    </div>

                    <div class="form-group">
                        <label for="parentFilter">📁 Danh Mục Cha</label>
                        <select id="parentFilter">
                            <option value="">-- Tất cả --</option>
                            <option value="null">Danh mục gốc</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="statusFilter">📊 Trạng Thái</label>
                        <select id="statusFilter">
                            <option value="">-- Tất cả --</option>
                            <option value="1">Kích hoạt</option>
                            <option value="0">Vô hiệu</option>
                        </select>
                    </div>
                </div>

                <div class="search-filter-actions">
                    <button class="btn btn-secondary" id="resetBtn">↻ Làm mới</button>
                    <button class="btn btn-primary" id="filterBtn">🔍 Tìm kiếm</button>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="table-wrapper">
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Tên Danh Mục</th>
                            <th>Danh Mục Cha</th>
                            <th>Loại Thú Cưng</th>
                            <th>Trạng Thái</th>
                            <th style="width: 180px;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
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

        <!-- Category Modal -->
        <div id="categoryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="categoryModalTitle">➕ Thêm Danh Mục</h3>
                    <button class="close-btn" id="closeModalBtn">×</button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <div class="form-group-vertical">
                            <label for="categoryNameInput">Tên Danh Mục *</label>
                            <input type="text" id="categoryNameInput" placeholder="Nhập tên danh mục..." required>
                            <div class="help-text">Tối đa 255 ký tự</div>
                        </div>

                        <div class="form-group-vertical">
                            <label for="parentCategorySelect">Danh Mục Cha</label>
                            <select id="parentCategorySelect">
                                <option value="">-- Danh mục gốc --</option>
                            </select>
                            <div class="help-text">Để trống nếu đây là danh mục gốc</div>
                        </div>

                        <div class="form-group-vertical">
                            <label for="petTypeSelect">Loại Thú Cưng *</label>
                            <select id="petTypeSelect" required>
                                <option value="">-- Chọn loại --</option>
                                <option value="dog">🐕 Chó</option>
                                <option value="cat">🐱 Mèo</option>
                                <option value="all">🐾 Tất cả</option>
                            </select>
                        </div>

                        <div class="form-group-vertical">
                            <label for="statusSelect">Trạng Thái</label>
                            <select id="statusSelect">
                                <option value="1">✅ Kích hoạt</option>
                                <option value="0">❌ Vô hiệu</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelModalBtn">Hủy</button>
                    <button class="btn btn-primary" id="saveCategoryBtn">💾 Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/PetsAccessories/admin/frontend/assets/js/categories.js"></script>
</body>

</html>
