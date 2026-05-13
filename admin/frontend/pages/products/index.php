<?php require_once __DIR__ . '/../../../backend/middleware/check_admin.php'; 
$pageTitle = 'Quản Lý Sản Phẩm - Admin';
$extraCss = '<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/products.css">';
$extraJs = '<script src="/PetsAccessories/admin/frontend/assets/js/products.js"></script>';
require_once __DIR__ . '/../../layout/header.php';
?>

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

                    <div class="form-group">
                        <label for="discountFilter">🏷️ Giá Khuyến Mãi</label>
                        <select id="discountFilter">
                            <option value="">-- Tất cả --</option>
                            <option value="1">Có khuyến mãi</option>
                            <option value="0">Không khuyến mãi</option>
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
                            <th>Ảnh</th>
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
                    <div class="form-section">
                        <h4>🖼️ Ảnh Sản Phẩm (Thumbnail)</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="productImageInput">Chọn Ảnh Sản Phẩm *</label>
                                <input type="file" id="productImageInput" class="form-control" accept="image/*">
                                <small>JPG, PNG, GIF, WebP | Max 5MB</small>
                            </div>

                            <div class="form-group">
                                <div id="currentImageContainer" style="display: none;">
                                    <label>Xem Trước</label>
                                    <img id="currentImage" src="" alt="preview" style="max-width: 120px; max-height: 120px; object-fit: cover; border-radius: 4px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Images Gallery Section -->
                    <div class="form-section" id="imagesSection" style="display: none;">
                        <h4>🖼️ Ảnh Bổ Sung (Thêm vào gallery)</h4>
                        
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

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>