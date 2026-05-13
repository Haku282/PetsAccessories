/**
 * Admin Users Management - JavaScript
 * File: /admin/frontend/assets/js/users.js
 * * Đã Sửa:
 * 1. Đổi hiển thị từ 'updated_at' sang 'created_at' (Dòng 228)
 * 2. Sửa hàm saveUser() gửi user_id trong body JSON khi update (Dòng 140, 151)
 */

class UsersManager {
    constructor() {
        this.apiBase = '/PetsAccessories/admin/backend/api/users';
        this.currentPage = 1;
        this.currentFilters = {
            search: '',
            role: '',
            status: ''
        };
        this.currentUser = null;
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.loadUsers();
    }

    attachEventListeners() {
        // Filter & Search
        document.getElementById('filterBtn')?.addEventListener('click', () => this.applyFilters());
        document.getElementById('resetBtn')?.addEventListener('click', () => this.resetFilters());
        document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.applyFilters();
        });

        // Add User
        document.getElementById('addUserBtn')?.addEventListener('click', () => this.showAddModal());

        // Modal
        document.getElementById('closeModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cancelModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('saveUserBtn')?.addEventListener('click', () => this.saveUser());
        document.getElementById('userModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'userModal') this.closeModal();
        });

        // Lock Reason Modal
        document.getElementById('closeLockReasonModalBtn')?.addEventListener('click', () => this.closeLockReasonModal());
        document.getElementById('cancelLockBtn')?.addEventListener('click', () => this.closeLockReasonModal());
        document.getElementById('confirmLockBtn')?.addEventListener('click', () => this.confirmLockUser());
        document.getElementById('lockReasonModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'lockReasonModal') this.closeLockReasonModal();
        });
    }

    applyFilters() {
        this.currentFilters.search = document.getElementById('searchInput')?.value || '';
        this.currentFilters.role = document.getElementById('roleFilter')?.value || '';
        this.currentFilters.status = document.getElementById('statusFilter')?.value || '';
        this.currentPage = 1;
        this.loadUsers();
    }

    resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('roleFilter').value = '';
        document.getElementById('statusFilter').value = '';
        this.currentFilters = { search: '', role: '', status: '' };
        this.currentPage = 1;
        this.loadUsers();
    }

    async loadUsers(page = 1) {
        try {
            this.showLoading();
            this.currentPage = page;

            const params = new URLSearchParams({
                page: this.currentPage,
                limit: 10,
                search: this.currentFilters.search,
                role: this.currentFilters.role,
                status: this.currentFilters.status
            });

            const response = await fetch(`${this.apiBase}/list.php?${params}`);
            
            // Kiểm tra nếu response không ok (ví dụ lỗi 500 do SQL)
            if (!response.ok) {
                throw new Error(`Lỗi hệ thống backend (Status: ${response.status})`);
            }

            const data = await response.json();

            if (data.success) {
                this.renderUsers(data.data);
                this.renderPagination(data.pagination);
            } else {
                this.showMessage('error', data.message || 'Lỗi tải dữ liệu');
                this.renderEmptyState(data.message);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            this.showMessage('error', 'Lỗi kết nối hoặc dữ liệu không hợp lệ. Vui lòng kiểm tra Console.');
            this.renderEmptyState('Không thể tải dữ liệu');
        }
    }

    showAddModal() {
        this.currentUser = null;
        document.getElementById('modalTitle').textContent = '➕ Thêm Người Dùng Mới';
        this.resetForm();
        document.getElementById('passwordInput').required = true;
        document.getElementById('passwordInput').placeholder = 'Tối thiểu 6 ký tự...';
        this.openModal(); // Đã sửa từ showModal thành openModal cho đúng tên hàm ở dưới
    }

    async editUser(userId) {
        try {
            const response = await fetch(`${this.apiBase}/get.php?id=${userId}`);
            const data = await response.json();

            if (data.success) {
                this.currentUser = data.data;
                document.getElementById('modalTitle').textContent = '✏️ Sửa Người Dùng';
                this.populateForm(data.data);
                document.getElementById('passwordInput').required = false;
                document.getElementById('passwordInput').placeholder = 'Bỏ trống để giữ nguyên mật khẩu';
                this.openModal(); // Đã sửa từ showModal thành openModal
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi: ' + error.message);
        }
    }

    populateForm(user) {
        document.getElementById('usernameInput').value = user.username;
        document.getElementById('emailInput').value = user.email;
        // Backend alias 'fullname as full_name', nên ở đây dùng full_name
        document.getElementById('fullnameInput').value = user.full_name || ''; 
        document.getElementById('phoneInput').value = user.phone || '';
        document.getElementById('addressInput').value = user.address || '';
        document.getElementById('roleInput').value = user.role;
        document.getElementById('statusInput').value = user.status;
    }

    resetForm() {
        document.getElementById('userForm').reset();
    }

    async saveUser() {
        if (!this.validateForm()) return;

        // FIX SỬA NGƯỜI DÙNG: Gom dữ liệu, bao gồm cả ID nếu đang sửa
        const formData = {
            // Nếu đang sửa (currentUser != null), đính kèm user_id vào body JSON
            user_id: this.currentUser ? this.currentUser.user_id : null, 
            username: document.getElementById('usernameInput').value.trim(),
            email: document.getElementById('emailInput').value.trim(),
            fullname: document.getElementById('fullnameInput').value.trim(),
            phone: document.getElementById('phoneInput').value.trim(),
            address: document.getElementById('addressInput').value.trim(),
            role: document.getElementById('roleInput').value,
            status: document.getElementById('statusInput').value
        };

        // Chỉ thêm password nếu có giá trị
        const passwordInput = document.getElementById('passwordInput').value;
        if (passwordInput) {
            formData.password = passwordInput;
        }

        // Khi thêm mới, password bắt buộc
        if (!this.currentUser && !passwordInput) {
            this.showMessage('error', 'Vui lòng nhập mật khẩu khi thêm người dùng mới');
            return;
        }

        try {
            // FIX SỬA NGƯỜI DÙNG: Đường dẫn không cần truyền ?id= nữa vì ID đã nằm trong body
            const endpoint = this.currentUser 
                ? `${this.apiBase}/update.php`
                : `${this.apiBase}/add.php`;

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            
            if (data.success) {
                this.showMessage('success', data.message || 'Lưu thành công!');
                this.closeModal();
                this.loadUsers(this.currentPage);
            } else {
                this.showMessage('error', data.message || 'Lỗi lưu dữ liệu');
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi: ' + error.message);
        }
    }

    async deleteUser(userId) {
        if (!confirm('Bạn có chắc chắn muốn xóa người dùng này? Hành động này không thể hoàn tác.')) return;

        try {
            const response = await fetch(`${this.apiBase}/delete.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', data.message || 'Xóa thành công!');
                this.loadUsers(this.currentPage);
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi: ' + error.message);
        }
    }

    async toggleUserStatus(userId) {
        try {
            // Lấy user hiện tại để biết status
            const currentRow = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (!currentRow) return;

            const currentStatus = parseInt(currentRow.querySelector('.status-badge').getAttribute('data-status'));
            const newStatus = currentStatus === 1 ? 0 : 1;
            const action = newStatus === 1 ? 'mở khóa' : 'khóa';

            // Nếu muốn khóa (status = 0), hiển thị modal nhập lý do
            if (newStatus === 0) {
                this.showLockReasonModal(userId);
                return;
            }

            // Nếu muốn mở khóa, confirm trực tiếp
            if (!confirm(`Bạn có chắc chắn muốn ${action} tài khoản này?`)) return;

            const response = await fetch(`${this.apiBase}/toggle-status.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    user_id: userId,
                    status: newStatus
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', data.message);
                this.loadUsers(this.currentPage);
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi: ' + error.message);
        }
    }

    showLockReasonModal(userId) {
        document.getElementById('lockUserId').value = userId;
        document.getElementById('lockReasonInput').value = '';
        document.getElementById('lockReasonModal').style.display = 'block';
    }

    closeLockReasonModal() {
        document.getElementById('lockReasonModal').style.display = 'none';
        document.getElementById('lockUserId').value = '';
        document.getElementById('lockReasonInput').value = '';
    }

    async confirmLockUser() {
        const userId = parseInt(document.getElementById('lockUserId').value);
        const lockReason = document.getElementById('lockReasonInput').value.trim();

        if (!lockReason) {
            this.showMessage('error', 'Vui lòng nhập lý do khóa tài khoản');
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/toggle-status.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    user_id: userId,
                    status: 0,
                    lock_reason: lockReason
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', data.message);
                this.closeLockReasonModal();
                this.loadUsers(this.currentPage);
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi: ' + error.message);
        }
    }

    renderUsers(users) {
        const tbody = document.querySelector('.users-table tbody');
        
        if (!users || users.length === 0) {
            this.renderEmptyState('Không có người dùng nào');
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr data-user-id="${user.user_id}">
                <td>
                    <div class="user-username">${this.escapeHtml(user.username)}</div>
                </td>
                <td>${this.escapeHtml(user.email)}</td>
                <td>${this.escapeHtml(user.full_name || '-')}</td>
                <td>${this.escapeHtml(user.phone || '-')}</td>
                <td>
                    <span class="role-badge role-${user.role}">
                        ${user.role === 'admin' ? '👤 Admin' : '👥 Khách hàng'}
                    </span>
                </td>
                <td>
                    <span class="status-badge status-${user.status == 1 ? 'active' : 'inactive'}" data-status="${user.status}">
                        ${user.status == 1 ? '✓ Hoạt động' : '✗ Bị khóa'}
                    </span>
                </td>
                <td>${user.created_at ? new Date(user.created_at).toLocaleDateString('vi-VN') : '-'}</td>
                <td>
                    <div class="table-actions">
                        <button class="btn btn-success btn-xs" onclick="usersManager.editUser(${user.user_id})" title="Chỉnh sửa">
                            ✏️ Sửa
                        </button>
                        <button class="btn btn-${user.status == 1 ? 'warning' : 'info'} btn-xs" onclick="usersManager.toggleUserStatus(${user.user_id})" title="${user.status == 1 ? 'Khóa tài khoản' : 'Mở khóa tài khoản'}">
                            ${user.status == 1 ? '🔒 Khóa' : '🔓 Mở khóa'}
                        </button>
                        <button class="btn btn-danger btn-xs" onclick="usersManager.deleteUser(${user.user_id})" title="Xóa">
                            🗑️ Xóa
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderPagination(pagination) {
        const container = document.getElementById('paginationContainer');
        if (!pagination || pagination.total_pages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        
        if (pagination.current_page > 1) {
            html += `<button onclick="usersManager.loadUsers(1)">« Đầu tiên</button>`;
            html += `<button onclick="usersManager.loadUsers(${pagination.current_page - 1})">‹ Trước</button>`;
        }

        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === pagination.current_page) {
                html += `<span class="active">${i}</span>`;
            } else if (i <= 3 || i > pagination.total_pages - 3 || Math.abs(i - pagination.current_page) <= 1) {
                html += `<button onclick="usersManager.loadUsers(${i})">${i}</button>`;
            } else if (i === 4 || i === pagination.total_pages - 3) {
                html += '<span>...</span>';
            }
        }

        if (pagination.current_page < pagination.total_pages) {
            html += `<button onclick="usersManager.loadUsers(${pagination.current_page + 1})">Tiếp ›</button>`;
            html += `<button onclick="usersManager.loadUsers(${pagination.total_pages})">Cuối cùng »</button>`;
        }

        container.innerHTML = html;
    }

    // Helper function để hiển thị loading
    showLoading() {
        const tbody = document.querySelector('.users-table tbody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>`;
        }
    }

    // Helper function để hiển thị trạng thái trống
    renderEmptyState(message) {
        const tbody = document.querySelector('.users-table tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <div class="empty-state">
                            <div class="empty-state-icon" style="font-size: 40px; margin-bottom: 10px;">👥</div>
                            <p>${this.escapeHtml(message)}</p>
                        </div>
                    </td>
                </tr>
            `;
        }
        const pagination = document.getElementById('paginationContainer');
        if (pagination) pagination.innerHTML = '';
    }

    validateForm() {
        const username = document.getElementById('usernameInput').value.trim();
        const email = document.getElementById('emailInput').value.trim();
        const password = document.getElementById('passwordInput').value;

        if (!username) {
            this.showMessage('error', 'Vui lòng nhập tên đăng nhập');
            return false;
        }

        if (!email || !email.includes('@')) {
            this.showMessage('error', 'Vui lòng nhập email hợp lệ');
            return false;
        }

        // Chỉ bắt buộc mật khẩu khi thêm mới
        if (!this.currentUser && !password) {
            this.showMessage('error', 'Vui lòng nhập mật khẩu');
            return false;
        }

        if (password && password.length < 6) {
            this.showMessage('error', 'Mật khẩu phải tối thiểu 6 ký tự');
            return false;
        }

        return true;
    }

    showMessage(type, message) {
        // Kiểm tra xem container có tồn tại không, nếu không thì dùng alert tạm
        const container = document.getElementById('messagesContainer');
        if (!container) {
            alert(message);
            return;
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message-toast ${type}`;
        messageDiv.style = "background: #fff; padding: 10px 20px; border-radius: 4px; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); border-left: 4px solid " + (type === 'success' ? '#2ecc71' : '#e74c3c');
        messageDiv.textContent = message;
        container.appendChild(messageDiv);

        setTimeout(() => messageDiv.remove(), 3000);
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    openModal() {
        const modal = document.getElementById('userModal');
        if (modal) modal.classList.add('show');
    }

    closeModal() {
        const modal = document.getElementById('userModal');
        if (modal) {
            modal.classList.remove('show');
            document.getElementById('userForm').reset();
            this.currentUser = null; // Reset user đang sửa
        }
    }
}

// Initialize khi DOM đã sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    window.usersManager = new UsersManager();
});