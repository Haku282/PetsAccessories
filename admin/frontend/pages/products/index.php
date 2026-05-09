<?php
/**
 * Trang quản lý sản phẩm
 * File: /admin/frontend/pages/products/index.php
 */

// Kiểm tra quyền admin
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Admin</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/products.css">
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><span>📦</span> Quản Lý Sản Phẩm</h1>
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
                <li><a href="/PetsAccessories/admin/frontend/pages/products/index.php" class="active"><span>📦</span> Sản Phẩm</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/orders/index.php"><span>🛒</span> Đơn Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/categories/index.php"><span>📁</span> Danh Mục</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/brands/index.php"><span>🏷️</span> Thương Hiệu</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/users/index.php"><span>👥</span> Người Dùng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/clients/index.php" class="active"><span>👥</span> Khách Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons.php"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners/index.php" class="active"><span>🖼️</span> Banner</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="products-container">
            <!-- Messages Container -->
            <div id="messagesContainer"></div>

            <!-- Header Section -->
            <div class="products-header">
                <h2>📦 Sản Phẩm</h2>
                <div class="products-header-actions">
                    <button class="btn btn-primary" id="addProductBtn">
                        ➕ Thêm Sản Phẩm
                    </button>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-filter-section">
                <div class="search-filter-grid">
                    <div class="form-group">
                        <label for="searchInput">🔍 Tìm kiếm</label>
                        <input type="text" id="searchInput" placeholder="Tên sản phẩm hoặc SKU...">
                    </div>

                    <div class="form-group">
                        <label for="categoryFilter">📁 Danh Mục</label>
                        <select id="categoryFilter">
                            <option value="">-- Tất cả danh mục --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="brandFilter">🏷️ Thương Hiệu</label>
                        <select id="brandFilter">
                            <option value="">-- Tất cả thương hiệu --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="statusFilter">📊 Trạng Thái</label>
                        <select id="statusFilter">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="active">Đang bán</option>
                            <option value="inactive">Ngừng kinh doanh</option>
                            <option value="out_of_stock">Hết hàng</option>
                        </select>
                    </div>
                </div>

                <div class="search-filter-actions">
                    <button class="btn btn-secondary" id="resetBtn">↻ Làm mới</button>
                    <button class="btn btn-primary" id="filterBtn">🔍 Tìm kiếm</button>
                </div>
            </div>

            <!-- Products Table -->
            <div class="products-table-wrapper">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Tên Sản Phẩm</th>
                            <th>Danh Mục</th>
                            <th>Thương Hiệu</th>
                            <th>Giá</th>
                            <th>Giá KM</th>
                            <th>Tồn Kho</th>
                            <th>Trạng Thái</th>
                            <th>Cập Nhật</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" style="text-align: center;">
                                <div class="loading">
                                    <div class="spinner"></div>
                                    <p>Đang tải dữ liệu...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination" id="paginationContainer"></div>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h3 id="modalTitle">Thêm Sản Phẩm Mới</h3>
                <button class="modal-close" id="closeModalBtn">×</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="productForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h4>📋 Thông Tin Cơ Bản</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="productNameInput">Tên Sản Phẩm *</label>
                                <input type="text" id="productNameInput" placeholder="Nhập tên sản phẩm..." required>
                            </div>

                            <div class="form-group">
                                <label for="skuInput">SKU</label>
                                <input type="text" id="skuInput" placeholder="Ví dụ: DOG-FOOD-001">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="categoryInput">Danh Mục *</label>
                                <select id="categoryInput" required>
                                    <option value="">-- Chọn danh mục --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="brandInput">Thương Hiệu</label>
                                <select id="brandInput">
                                    <option value="">-- Không chọn --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="descriptionInput">Mô Tả</label>
                                <textarea id="descriptionInput" placeholder="Nhập mô tả sản phẩm..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="form-section">
                        <h4>💰 Giá Cả</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="priceInput">Giá Gốc (đ) *</label>
                                <input type="number" id="priceInput" placeholder="0" min="0" step="1000" required>
                            </div>

                            <div class="form-group">
                                <label for="discountPriceInput">Giá Khuyến Mãi (đ)</label>
                                <input type="number" id="discountPriceInput" placeholder="0" min="0" step="1000">
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Section -->
                    <div class="form-section">
                        <h4>📦 Tồn Kho</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="stockInput">Số Lượng *</label>
                                <input type="number" id="stockInput" placeholder="0" min="0" required>
                            </div>

                            <div class="form-group">
                                <label for="statusInput">Trạng Thái *</label>
                                <select id="statusInput" required>
                                    <option value="active">Đang bán</option>
                                    <option value="inactive">Ngừng kinh doanh</option>
                                    <option value="out_of_stock">Hết hàng</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Images Section -->
                    <div class="form-section" id="imagesSection" style="display: none;">
                        <h4>🖼️ Ảnh Sản Phẩm</h4>
                        
                        <div class="image-upload-section" id="dropZone">
                            <p>📤 Kéo thả ảnh vào đây hoặc</p>
                            <label for="imageUploadInput" class="image-upload-label">click để chọn</label>
                            <input type="file" id="imageUploadInput" class="image-upload-input" accept="image/*">
                            <div class="image-upload-hint">
                                Hỗ trợ: JPG, PNG, GIF, WebP | Max 5MB
                            </div>
                        </div>

                        <div class="image-gallery" id="imageGallery"></div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelModalBtn">Hủy</button>
                <button class="btn btn-primary" id="saveProductBtn">💾 Lưu Sản Phẩm</button>
            </div>
        </div>
    </div>

    <script src="/PetsAccessories/admin/frontend/assets/js/products.js"></script>
</body>

</html>
