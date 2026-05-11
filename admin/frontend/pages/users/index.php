<?php require_once __DIR__ . '/../../../backend/middleware/check_admin.php'; 
$pageTitle = 'Quản Lý Người Dùng - Admin';
$extraCss = '<link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/users.css">';
$extraJs = '<script src="/PetsAccessories/admin/frontend/assets/js/users.js"></script>';
require_once __DIR__ . '/../../layout/header.php';
?>

<!-- Main Content -->
        <div class="users-container">
            <!-- Messages Container -->
            <div id="messagesContainer"></div>

            <!-- Header Section -->
            <div class="users-header">
                <h2>👥 Người Dùng</h2>
                <div class="users-header-actions">
                    <button class="btn btn-primary" id="addUserBtn">
                        ➕ Thêm Người Dùng
                    </button>
                </div>
            </div>

            <!-- Search & Filter Section -->
            <div class="search-filter-section">
                <div class="search-filter-grid">
                    <div class="form-group">
                        <label for="searchInput">🔍 Tìm kiếm</label>
                        <input type="text" id="searchInput" placeholder="Tên, email, số điện thoại...">
                    </div>

                    <div class="form-group">
                        <label for="roleFilter">👤 Vai trò</label>
                        <select id="roleFilter">
                            <option value="">-- Tất cả vai trò --</option>
                            <option value="admin">Admin</option>
                            <option value="customer">Khách hàng</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="statusFilter">📊 Trạng thái</label>
                        <select id="statusFilter">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Bị khóa</option>
                        </select>
                    </div>
                </div>

                <div class="search-filter-actions">
                    <button class="btn btn-secondary" id="resetBtn">↻ Làm mới</button>
                    <button class="btn btn-primary" id="filterBtn">🔍 Tìm kiếm</button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="users-table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Tài Khoản</th>
                            <th>Email</th>
                            <th>Họ Tên</th>
                            <th>Điện Thoại</th>
                            <th>Vai Trò</th>
                            <th>Trạng Thái</th>
                            <th>Ngày Tạo</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" style="text-align: center;">
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

    <!-- User Modal -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h3 id="modalTitle">Thêm Người Dùng Mới</h3>
                <button class="modal-close" id="closeModalBtn">×</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="userForm">
                    <!-- Account Information -->
                    <div class="form-section">
                        <h4>📋 Thông Tin Tài Khoản</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="usernameInput">Tên đăng nhập *</label>
                                <input type="text" id="usernameInput" placeholder="Nhập tên đăng nhập..." required>
                            </div>

                            <div class="form-group">
                                <label for="emailInput">Email *</label>
                                <input type="email" id="emailInput" placeholder="Nhập email..." required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="passwordInput">Mật khẩu *</label>
                                <input type="password" id="passwordInput" placeholder="Tối thiểu 6 ký tự..." required>
                            </div>

                            <div class="form-group">
                                <label for="roleInput">Vai trò *</label>
                                <select id="roleInput" required>
                                    <option value="customer">Khách hàng</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="form-section">
                        <h4>👤 Thông Tin Cá Nhân</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fullnameInput">Họ tên</label>
                                <input type="text" id="fullnameInput" placeholder="Nhập họ tên...">
                            </div>

                            <div class="form-group">
                                <label for="phoneInput">Số điện thoại</label>
                                <input type="tel" id="phoneInput" placeholder="Nhập số điện thoại...">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="addressInput">Địa chỉ</label>
                                <textarea id="addressInput" placeholder="Nhập địa chỉ..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div class="form-section">
                        <h4>📊 Trạng Thái</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="statusInput">Trạng thái *</label>
                                <select id="statusInput" required>
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Bị khóa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelModalBtn">Hủy</button>
                <button class="btn btn-primary" id="saveUserBtn">💾 Lưu Người Dùng</button>
            </div>
        </div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>