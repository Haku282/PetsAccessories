<?php require_once __DIR__ . '/../../../backend/middleware/check_admin.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Bài Viết</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/categories.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <div><h1><span>📰</span> CMS Bài Viết</h1></div>
            <div class="user-info">
                <span>Xin chào: <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong></span>
                <a href="/PetsAccessories/frontend/components/logout.php" class="logout-btn">🚪 Đăng Xuất</a>
            </div>
        </div>

        <div class="menu">
            <ul>
                <li><a href="/PetsAccessories/admin/frontend/index_admin.php">Dashboard</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/cms_pages/index.php">Trang CMS</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/cms_posts/index.php" class="active">Bài Viết</a></li>
            </ul>
        </div>

        <div class="brands-container">
            <div id="messagesContainer"></div>

            <div class="brands-header">
                <h2>📰 Danh Sách Bài Viết</h2>
                <div class="brands-header-actions">
                    <button class="btn btn-primary" id="addPostBtn">➕ Thêm Bài Viết</button>
                </div>
            </div>

            <div class="search-filter-section">
                <div class="search-filter-grid">
                    <div class="form-group">
                        <label>Tìm kiếm bài viết</label>
                        <input id="postSearch" placeholder="Tiêu đề, nội dung...">
                    </div>
                </div>
                <div class="search-filter-actions">
                    <button class="btn btn-secondary" id="resetPostsBtn">Làm mới</button>
                    <button class="btn btn-primary" id="filterPostsBtn">Tìm kiếm</button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="brands-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề</th>
                            <th>Loại</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="postsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="postModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 760px;">
            <div class="modal-header">
                <h3 id="postModalTitle">➕ Thêm Bài Viết</h3>
                <button type="button" class="close-btn" id="closePostModalBtn">×</button>
            </div>
            <div class="modal-body">
                <form id="postForm">
                    <input type="hidden" id="postId">
                    <div class="form-group-vertical">
                        <label>Tiêu đề *</label>
                        <input type="text" id="postTitle" required>
                    </div>
                    <div class="form-group-vertical">
                        <label>Slug *</label>
                        <input type="text" id="postSlug" required>
                    </div>
                    <div class="form-group-vertical">
                        <label>Danh mục *</label>
                        <select id="postCategory" required>
                            <option value="blog">Blog</option>
                            <option value="news">News</option>
                        </select>
                    </div>
                    <div class="form-group-vertical">
                        <label>Trạng thái *</label>
                        <select id="postStatus" required>
                            <option value="1">Hiện</option>
                            <option value="0">Ẩn</option>
                        </select>
                    </div>
                    <div class="form-group-vertical">
                        <label>Ảnh đại diện (URL)</label>
                        <input type="text" id="postThumbnail" placeholder="/uploads/... hoặc URL ảnh">
                    </div>
                    <div class="form-group-vertical">
                        <label>Nội dung *</label>
                        <textarea id="postContent" rows="12" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelPostBtn">Hủy</button>
                <button type="submit" form="postForm" class="btn btn-primary" id="savePostBtn">💾 Lưu Bài Viết</button>
            </div>
        </div>
    </div>

    <script src="/PetsAccessories/admin/frontend/assets/js/cms.js"></script>
</body>
</html>
