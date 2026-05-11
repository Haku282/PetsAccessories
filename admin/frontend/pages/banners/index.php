<?php require_once __DIR__ . '/../../../backend/middleware/check_admin.php'; 
$pageTitle = 'Quản Lý Banner - Admin';
$extraCss = '<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/orders.css">';
$extraJs = '<script src="/PetsAccessories/admin/frontend/assets/js/banners.js"></script>';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="orders-container">
            <div id="messagesContainer"></div>

            <div class="header-actions">
                <button class="btn btn-primary" id="addBannerBtn">➕ Thêm Banner Mới</button>
            </div>

            <div class="table-wrapper">
                <table class="orders-table" id="bannersTable">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 180px;">Hình Ảnh</th>
                            <th>Tiêu Đề</th>
                            <th>Liên Kết (Link)</th>
                            <th style="width: 120px;">Trạng Thái</th>
                            <th style="width: 150px; text-align: center;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <div class="loading"></div>
                                <p style="margin-top: 15px;">Đang tải danh sách banner...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="bannerModal" class="modal">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3 id="modalTitle">➕ Thêm Banner</h3>
                    <button class="close-btn" id="closeBannerModalBtn">×</button>
                </div>
                <div class="modal-body">
                    <form id="bannerForm" enctype="multipart/form-data">
                        <input type="hidden" id="bannerId" name="id">
                        
                        <div class="form-group-vertical">
                            <label for="bannerTitle">Tiêu Đề Banner *</label>
                            <input type="text" id="bannerTitle" name="title" required placeholder="Nhập tiêu đề banner">
                        </div>

                        <div class="form-group-vertical">
                            <label for="bannerImage">Hình Ảnh *</label>
                            <input type="file" id="bannerImage" name="image" accept="image/*">
                            <div id="currentImageContainer" style="margin-top: 10px; display: none;">
                                <p style="font-size: 12px; color: #666; margin-bottom: 5px;">Ảnh hiện tại:</p>
                                <img id="currentImage" src="" alt="Current Banner" class="banner-img-preview">
                            </div>
                        </div>

                        <div class="form-group-vertical">
                            <label for="bannerLink">Liên Kết (Link) </label>
                            <input type="text" id="bannerLink" name="link" placeholder="VD: /PetsAccessories/frontend/pages/sale.php">
                        </div>

                        <div class="form-group-vertical">
                            <label for="bannerStatus">Trạng Thái</label>
                            <select id="bannerStatus" name="status">
                                <option value="1">✅ Hiển thị</option>
                                <option value="0">❌ Ẩn</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancelBannerBtn">Hủy</button>
                    <button class="btn btn-primary" id="saveBannerBtn">💾 Lưu Banner</button>
                </div>
            </div>
        </div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>