<?php require_once __DIR__ . '/../../../backend/middleware/check_admin.php'; 
$pageTitle = 'CMS Trang';
$extraCss = '<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/categories.css">';
$extraJs = '<script src="/PetsAccessories/admin/frontend/assets/js/cms.js"></script>';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="brands-container">
            <div id="messagesContainer"></div>

            <div class="brands-header">
                <h2>📄 Danh Sách Trang CMS</h2>
                <div class="brands-header-actions">
                    <button class="btn btn-primary" id="addPageBtn">➕ Thêm Trang</button>
                </div>
            </div>

            <div class="search-filter-section">
                <div class="search-filter-grid">
                    <div class="form-group">
                        <label>Tìm kiếm trang</label>
                        <input id="pageSearch" placeholder="Tiêu đề, slug hoặc nội dung...">
                    </div>
                </div>
                <div class="search-filter-actions">
                    <button class="btn btn-secondary" id="resetPagesBtn">Làm mới</button>
                    <button class="btn btn-primary" id="filterPagesBtn">Tìm kiếm</button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="brands-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề</th>
                            <th>Slug</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="pagesTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="pageModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 720px;">
            <div class="modal-header">
                <h3 id="pageModalTitle">➕ Thêm Trang</h3>
                <button type="button" class="close-btn" id="closePageModalBtn">×</button>
            </div>
            <div class="modal-body">
                <form id="pageForm">
                    <input type="hidden" id="pageId">
                    <div class="form-group-vertical">
                        <label>Tiêu đề trang *</label>
                        <input type="text" id="pageTitle" required>
                    </div>
                    <div class="form-group-vertical">
                        <label>Slug *</label>
                        <input type="text" id="pageSlug" required placeholder="gioi-thieu, chinh-sach...">
                    </div>
                    <div class="form-group-vertical">
                        <label>Nội dung *</label>
                        <textarea id="pageContent" rows="12" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelPageBtn">Hủy</button>
                <button type="submit" form="pageForm" class="btn btn-primary" id="savePageBtn">💾 Lưu Trang</button>
            </div>
        </div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>