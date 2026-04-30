/**
 * Admin Users Management - JavaScript
 * File: /admin/frontend/assets/js/users.js
 */

class UsersManager {
    constructor() {
        this.apiBase = '/PetsAccessories/admin/backend/api/users';
        this.currentPage = 1;
        this.currentFilters = {
            role: '',
            status: '',
            search: ''
        };
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.loadUsers();
    }

    attachEventListeners() {
        // Filter
        document.getElementById('filterBtn')?.addEventListener('click', () => this.applyFilters());
        document.getElementById('resetBtn')?.addEventListener('click', () => this.resetFilters());
        
        // Search
        document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.applyFilters();
        });

        // Add User
        document.getElementById('addUserBtn')?.addEventListener('click', () => this.showAddModal());
        
        // Modal buttons
        document.getElementById('closeModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cancelModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('saveUserBtn')?.addEventListener('click', () => this.saveUser());

        // Modal backdrop
        document.getElementById('userModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'userModal') this.closeModal();
        });
    }

    async loadUsers(page = 1) {
        try {
            this.showLoading();
            
            const params = new URLSearchParams({
                page: page,
                role: this.currentFilters.role,
                status: this.currentFilters.status,
                search: this.currentFilters.search,
                limit: 10
            });

            const response = await fetch(`${this.apiBase}/list.php?${params}`);
            const result = await response.json();

            if (result.success) {
                this.renderTable(result.data);
                this.renderPagination(result.pagination);
                this.currentPage = page;
            } else {
                this.showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error loading users:', error);
            this.showAlert('Lỗi khi tải danh sách tài khoản', 'danger');
        }
    }

    renderTable(users) {
        const tbody = document.getElementById('usersTableBody');
        if (!tbody) return;

        if (users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center empty-state">
                        <div class="empty-state-icon">📭</div>
                        <h3>Không có tài khoản</h3>
                        <p>Hãy thêm tài khoản mới để bắt đầu</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr class="status-${user.status === 1 ? 'active' : 'inactive'}">
                <td>${user.user_id}</td>
                <td>
                    <strong>${this.escape(user.username)}</strong><br>
                    <small class="text-muted">${this.escape(user.email)}</small>
                </td>
                <td>${this.escape(user.fullname || '-')}</td>
                <td>${this.escape(user.phone || '-')}</td>
                <td>
                    <span class="badge ${user.role === 'admin' ? 'badge-admin' : 'badge-customer'}">
                        ${user.role === 'admin' ? 'Admin' : 'Khách Hàng'}
                    </span>
                </td>
                <td>
                    <span class="badge ${user.status === 1 ? 'badge-success' : 'badge-danger'}">
                        ${user.status === 1 ? '✓ Hoạt Động' : '✗ Bị Khóa'}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-action btn-action-edit" onclick="usersManager.showEditModal(${user.user_id})" title="Sửa">
                            ✎ Sửa
                        </button>
                        <button class="btn-action btn-action-lock" onclick="usersManager.toggleStatus(${user.user_id}, ${user.status === 1 ? 0 : 1})" title="${user.status === 1 ? 'Khóa' : 'Mở khóa'}">
                            ${user.status === 1 ? '🔒 Khóa' : '🔓 Mở'}
                        </button>
                        <button class="btn-action btn-action-delete" onclick="usersManager.deleteUser(${user.user_id})" title="Xóa">
                            🗑 Xóa
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderPagination(pagination) {
        const container = document.getElementById('pagination');
        if (!container) return;

        const { page, totalPages } = pagination;
        let html = '<ul class="pagination">';

        // Previous
        if (page > 1) {
            html += `<li><a href="javascript:usersManager.loadUsers(${page - 1})">← Trước</a></li>`;
        } else {
            html += `<li class="disabled"><span>← Trước</span></li>`;
        }

        // Pages
        const startPage = Math.max(1, page - 2);
        const endPage = Math.min(totalPages, page + 2);

        if (startPage > 1) {
            html += `<li><a href="javascript:usersManager.loadUsers(1)">1</a></li>`;
            if (startPage > 2) html += `<li class="disabled"><span>...</span></li>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === page) {
                html += `<li class="active"><span>${i}</span></li>`;
            } else {
                html += `<li><a href="javascript:usersManager.loadUsers(${i})">${i}</a></li>`;
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<li class="disabled"><span>...</span></li>`;
            html += `<li><a href="javascript:usersManager.loadUsers(${totalPages})">${totalPages}</a></li>`;
        }

        // Next
        if (page < totalPages) {
            html += `<li><a href="javascript:usersManager.loadUsers(${page + 1})">Sau →</a></li>`;
        } else {
            html += `<li class="disabled"><span>Sau →</span></li>`;
        }

        html += '</ul>';
        container.innerHTML = html;
    }

    applyFilters() {
        this.currentFilters.role = document.getElementById('roleFilter')?.value || '';
        this.currentFilters.status = document.getElementById('statusFilter')?.value || '';
        this.currentFilters.search = document.getElementById('searchInput')?.value || '';
        this.loadUsers(1);
    }

    resetFilters() {
        this.currentFilters = { role: '', status: '', search: '' };
        document.getElementById('roleFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('searchInput').value = '';
        this.loadUsers(1);
    }

    showAddModal() {
        document.getElementById('modalTitle').textContent = 'Thêm Tài Khoản Mới';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('userModal').classList.add('show');
    }

    async showEditModal(userId) {
        try {
            const response = await fetch(`${this.apiBase}/get.php?id=${userId}`);
            const result = await response.json();

            if (result.success) {
                const user = result.data;
                document.getElementById('modalTitle').textContent = 'Sửa Tài Khoản';
                document.getElementById('userId').value = user.user_id;
                document.getElementById('username').value = user.username;
                document.getElementById('username').disabled = true;
                document.getElementById('email').value = user.email;
                document.getElementById('fullname').value = user.fullname || '';
                document.getElementById('phone').value = user.phone || '';
                document.getElementById('address').value = user.address || '';
                document.getElementById('role').value = user.role;
                document.getElementById('password').value = '';
                document.getElementById('password').placeholder = 'Để trống nếu không đổi';
                document.getElementById('userModal').classList.add('show');
            } else {
                this.showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error loading user:', error);
            this.showAlert('Lỗi khi tải thông tin tài khoản', 'danger');
        }
    }

    async saveUser() {
        const userId = document.getElementById('userId').value;
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        const fullname = document.getElementById('fullname').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        const role = document.getElementById('role').value;

        // Validation
        if (!username || !email) {
            this.showAlert('Vui lòng điền username và email', 'warning');
            return;
        }

        if (!this.validateEmail(email)) {
            this.showAlert('Email không hợp lệ', 'warning');
            return;
        }

        const data = {
            username,
            email,
            fullname,
            phone,
            address,
            role
        };

        try {
            let endpoint = `${this.apiBase}/add.php`;
            let method = 'POST';

            if (userId) {
                endpoint = `${this.apiBase}/update.php`;
                method = 'PUT';
                data.user_id = userId;
                if (password) data.password = password;
            } else {
                if (!password) {
                    this.showAlert('Vui lòng nhập mật khẩu', 'warning');
                    return;
                }
                if (password.length < 6) {
                    this.showAlert('Mật khẩu phải có ít nhất 6 ký tự', 'warning');
                    return;
                }
                data.password = password;
            }

            const response = await fetch(endpoint, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');
                this.closeModal();
                this.loadUsers(this.currentPage);
            } else {
                this.showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error saving user:', error);
            this.showAlert('Lỗi khi lưu tài khoản', 'danger');
        }
    }

    async toggleStatus(userId, newStatus) {
        if (!confirm('Bạn chắc chắn muốn thay đổi trạng thái tài khoản này?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/toggle-status.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, status: newStatus })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');
                this.loadUsers(this.currentPage);
            } else {
                this.showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            this.showAlert('Lỗi khi cập nhật trạng thái', 'danger');
        }
    }

    async deleteUser(userId) {
        if (!confirm('Bạn chắc chắn muốn xóa tài khoản này? Hành động này không thể hoàn tác!')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/delete.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');
                this.loadUsers(this.currentPage);
            } else {
                this.showAlert(result.message, 'danger');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            this.showAlert('Lỗi khi xóa tài khoản', 'danger');
        }
    }

    closeModal() {
        document.getElementById('userModal').classList.remove('show');
        document.getElementById('userForm').reset();
        document.getElementById('username').disabled = false;
    }

    showLoading() {
        const tbody = document.getElementById('usersTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="loading">
                        <div>Đang tải...</div>
                    </td>
                </tr>
            `;
        }
    }

    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) return;

        const alertId = 'alert-' + Date.now();
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type}" role="alert">
                <strong>${type === 'success' ? '✓' : type === 'danger' ? '✗' : type === 'warning' ? '⚠' : 'ℹ'}</strong>
                ${this.escape(message)}
                <button type="button" class="btn-close-alert" onclick="document.getElementById('${alertId}').remove()" style="float: right; background: none; border: none; cursor: pointer; font-size: 18px;">×</button>
            </div>
        `;

        alertContainer.innerHTML += alertHtml;

        setTimeout(() => {
            const element = document.getElementById(alertId);
            if (element) element.remove();
        }, 5000);
    }

    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    escape(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.usersManager = new UsersManager();
});
