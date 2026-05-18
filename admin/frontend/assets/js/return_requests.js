class ReturnRequestManager {
    constructor() {
        this.apiBase = '/PetsAccessories/admin/backend/api/return_requests';
        this.requests = [];
        this.init();
    }

    init() {
        this.loadRequests();
        this.bindEvents();
    }

    bindEvents() {
        document.getElementById('statusFilter').addEventListener('change', () => this.loadRequests());
        document.getElementById('typeFilter').addEventListener('change', () => this.loadRequests());
        document.getElementById('searchInput').addEventListener('input', () => this.loadRequests());
    }

    async loadRequests() {
        try {
            const status = document.getElementById('statusFilter').value;
            const type = document.getElementById('typeFilter').value;
            const search = document.getElementById('searchInput').value;

            const params = new URLSearchParams();
            if (status) params.append('status', status);
            if (type) params.append('type', type);
            if (search) params.append('search', search);

            const response = await fetch(`${this.apiBase}/get.php?${params}`);
            const text = await response.text();
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Response text:', text);
                throw new Error('Phản hồi không phải JSON: ' + text.substring(0, 100));
            }

            if (data.success) {
                this.requests = data.data || [];
                this.renderTable();
                this.updateStats();
            } else {
                this.showMessage(data.message || 'Lỗi khi tải dữ liệu', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showMessage('Lỗi khi tải dữ liệu: ' + error.message, 'error');
        }
    }

    renderTable() {
        const tbody = document.querySelector('#requestsTable tbody');
        
        if (this.requests.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Không có yêu cầu nào</td></tr>';
            return;
        }

        tbody.innerHTML = this.requests.map(req => `
            <tr>
                <td>#${req.return_id}</td>
                <td><strong>${this.escapeHtml(req.fullname || 'N/A')}</strong></td>
                <td>${this.escapeHtml(req.phone || 'N/A')}</td>
                <td>${req.request_type === 'return' ? '🔄 Trả Hàng' : '🔄 Đổi Hàng'}</td>
                <td><span style="max-width: 150px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${this.escapeHtml(req.reason || '')}</span></td>
                <td>${this.getStatusBadge(req.status)}</td>
                <td>${new Date(req.created_at).toLocaleDateString('vi-VN')}</td>
                <td style="text-align: center;">
                    <button class="action-btn view" onclick="requestManager.showDetails(${req.return_id})" title="Xem chi tiết">👁️</button>
                    ${req.status === 'pending' ? `
                        <button class="action-btn edit" onclick="requestManager.updateStatus(${req.return_id}, 'approved')" title="Phê duyệt">✓</button>
                        <button class="action-btn delete" onclick="requestManager.updateStatus(${req.return_id}, 'rejected')" title="Từ chối">✕</button>
                    ` : ''}
                </td>
            </tr>
        `).join('');
    }

    getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-warning">⏳ Đang Xử Lý</span>',
            'approved': '<span class="badge badge-success">✓ Đã Phê Duyệt</span>',
            'rejected': '<span class="badge badge-danger">✕ Đã Từ Chối</span>',
            'completed': '<span class="badge badge-info">✓ Đã Hoàn Thành</span>'
        };
        return badges[status] || `<span class="badge">${status}</span>`;
    }

    async showDetails(requestId) {
        try {
            const response = await fetch(`${this.apiBase}/get.php?id=${requestId}`);
            const text = await response.text();
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Details response:', text);
                throw new Error('Phản hồi không phải JSON');
            }

            if (data.success && data.data) {
                const req = data.data;
                const content = `
                    <div style="display: grid; gap: 15px;">
                        <div>
                            <strong>Mã Yêu Cầu:</strong> #${req.return_id}
                        </div>
                        <div>
                            <strong>Khách Hàng:</strong> ${this.escapeHtml(req.fullname || 'N/A')}
                        </div>
                        <div>
                            <strong>Email:</strong> ${this.escapeHtml(req.email || 'N/A')}
                        </div>
                        <div>
                            <strong>Số Điện Thoại:</strong> ${this.escapeHtml(req.phone || 'N/A')}
                        </div>
                        <div>
                            <strong>Loại Yêu Cầu:</strong> ${req.request_type === 'return' ? 'Trả Hàng' : 'Đổi Hàng'}
                        </div>
                        <div>
                            <strong>Lý Do:</strong>
                            <p style="margin: 5px 0; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                                ${this.escapeHtml(req.reason || '')}
                            </p>
                        </div>
                        <div>
                            <strong>Trạng Thái:</strong> ${this.getStatusBadge(req.status)}
                        </div>
                        ${req.admin_note ? `
                        <div>
                            <strong>Ghi Chú Admin:</strong>
                            <p style="margin: 5px 0; padding: 10px; background: #fff3cd; border-radius: 4px;">
                                ${this.escapeHtml(req.admin_note)}
                            </p>
                        </div>
                        ` : ''}
                        <div>
                            <strong>Ngày Tạo:</strong> ${new Date(req.created_at).toLocaleString('vi-VN')}
                        </div>
                        ${req.status === 'pending' ? `
                        <div style="border-top: 1px solid #eee; padding-top: 15px;">
                            <label style="display: block; margin-bottom: 10px; font-weight: 500;">Ghi Chú Xử Lý:</label>
                            <textarea id="adminNote" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial; resize: vertical; min-height: 80px;" placeholder="Nhập ghi chú xử lý..."></textarea>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn btn-success" onclick="requestManager.updateStatusWithNote(${requestId}, 'approved')">✓ Phê Duyệt</button>
                            <button class="btn btn-danger" onclick="requestManager.updateStatusWithNote(${requestId}, 'rejected')">✕ Từ Chối</button>
                            <button class="btn btn-secondary" onclick="requestManager.closeModal()">Đóng</button>
                        </div>
                        ` : `
                        <div style="padding-top: 15px;">
                            <button class="btn btn-secondary" onclick="requestManager.closeModal()">Đóng</button>
                        </div>
                        `}
                    </div>
                `;
                document.getElementById('requestDetailsContent').innerHTML = content;
                document.getElementById('requestModal').style.display = 'block';
            }
        } catch (error) {
            this.showMessage('Lỗi: ' + error.message, 'error');
        }
    }

    async updateStatus(requestId, status) {
        if (confirm(`Bạn có chắc muốn ${status === 'approved' ? 'phê duyệt' : 'từ chối'} yêu cầu này?`)) {
            await this.updateStatusWithNote(requestId, status);
        }
    }

    async updateStatusWithNote(requestId, status) {
        try {
            const adminNote = document.getElementById('adminNote')?.value || '';
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('return_id', requestId);
            formData.append('status', status);
            if (adminNote) formData.append('admin_note', adminNote);

            const response = await fetch(`${this.apiBase}/update.php`, {
                method: 'POST',
                body: formData
            });
            const text = await response.text();
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Update response:', text);
                throw new Error('Phản hồi không phải JSON');
            }

            if (data.success) {
                this.showMessage(data.message || 'Cập nhật thành công', 'success');
                this.closeModal();
                this.loadRequests();
            } else {
                this.showMessage(data.message || 'Cập nhật thất bại', 'error');
            }
        } catch (error) {
            this.showMessage('Lỗi: ' + error.message, 'error');
        }
    }

    async updateStats() {
        try {
            const response = await fetch(`${this.apiBase}/get.php?action=stats`);
            const text = await response.text();
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Stats response:', text);
                return;
            }

            if (data.success && data.stats) {
                const stats = data.stats;
                document.getElementById('totalRequests').textContent = stats.total || 0;
                document.getElementById('pendingRequests').textContent = stats.pending || 0;
                document.getElementById('approvedRequests').textContent = stats.approved || 0;
                document.getElementById('rejectedRequests').textContent = stats.rejected || 0;
                document.getElementById('completedRequests').textContent = stats.completed || 0;
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    closeModal() {
        document.getElementById('requestModal').style.display = 'none';
    }

    showMessage(message, type = 'info') {
        const container = document.getElementById('messagesContainer');
        const alertClass = type === 'error' ? 'alert-danger' : type === 'success' ? 'alert-success' : 'alert-info';
        const html = `<div class="alert ${alertClass}">${message}</div>`;
        container.innerHTML = html;
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize manager
const requestManager = new ReturnRequestManager();

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    const modal = document.getElementById('requestModal');
    if (e.target === modal) {
        requestManager.closeModal();
    }
});
