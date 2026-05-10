<?php
/**
 * Trang quản lý Banner
 * File: /admin/frontend/pages/banners/index.php
 */
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Banner - Admin</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/orders.css"> <style>
        .banner-img-preview {
            max-width: 150px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .header-actions {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><span>🖼️</span> Quản Lý Banner</h1>
            </div>
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
                <li><a href="/PetsAccessories/admin/frontend/pages/clients/index.php"><span>👥</span> Khách Hàng</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/coupons/index.php"><span>🎟️</span> Mã Giảm Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/banners/index.php" class="active"><span>🖼️</span> Banner</a></li>
            </ul>
        </div>

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
    </div>

    <script src="/PetsAccessories/admin/frontend/assets/js/banners.js"></script>
</body>

</html>
