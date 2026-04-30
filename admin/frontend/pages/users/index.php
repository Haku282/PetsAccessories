<?php
/**
 * Trang Quản Lý Tài Khoản
 * File: /admin/frontend/pages/users/index.php
 */

require_once __DIR__ . '/../../../../backend/config/database.php';
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tài Khoản - Admin</title>
    <link rel="stylesheet" href="../../assets/css/users.css">
</head>
<body>
    <div style="padding: 20px;">
        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Header -->
        <div class="admin-header">
            <div>
                <h1>👥 Quản Lý Tài Khoản</h1>
                <p style="color: #7f8c8d; margin-top: 5px;">Quản lý tài khoản khách hàng và quản trị viên</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" id="addUserBtn">+ Thêm Tài Khoản</button>
                <a href="/PetsAccessories/admin/frontend/index_admin.php" class="btn btn-secondary">← Quay Lại</a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="searchInput">🔍 Tìm Kiếm</label>
                    <input type="text" id="searchInput" placeholder="Tìm theo username, email, tên..." />
                </div>
                <div class="filter-group">
                    <label for="roleFilter">👤 Vai Trò</label>
                    <select id="roleFilter">
                        <option value="">Tất Cả</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Khách Hàng</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="statusFilter">📊 Trạng Thái</label>
                    <select id="statusFilter">
                        <option value="">Tất Cả</option>
                        <option value="1">Hoạt Động</option>
                        <option value="0">Bị Khóa</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" id="filterBtn">🔍 Lọc</button>
                <button class="btn btn-secondary" id="resetBtn">↻ Làm Mới</button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tài Khoản</th>
                        <th>Họ Tên</th>
                        <th>Điện Thoại</th>
                        <th>Vai Trò</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="7" class="loading">Đang tải...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <div id="pagination"></div>
        </div>
    </div>

    <!-- Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Thêm Tài Khoản Mới</div>
            
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId" value="">
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" placeholder="Nhập username" required />
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" placeholder="Nhập email" required />
                    </div>

                    <div class="form-group">
                        <label for="password">Mật Khẩu *</label>
                        <input type="password" id="password" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" />
                    </div>

                    <div class="form-group">
                        <label for="fullname">Họ Tên</label>
                        <input type="text" id="fullname" placeholder="Nhập họ tên" />
                    </div>

                    <div class="form-group">
                        <label for="phone">Điện Thoại</label>
                        <input type="tel" id="phone" placeholder="Nhập số điện thoại" />
                    </div>

                    <div class="form-group">
                        <label for="address">Địa Chỉ</label>
                        <textarea id="address" placeholder="Nhập địa chỉ" style="resize: vertical; min-height: 80px;"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="role">Vai Trò *</label>
                        <select id="role" required>
                            <option value="customer">Khách Hàng</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-close" id="cancelModalBtn">Hủy</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">💾 Lưu</button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/users.js"></script>
</body>
</html>
