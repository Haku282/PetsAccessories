class ReviewReportManager {
    constructor() {
        this.apiBase = '/PetsAccessories/admin/backend/api/review_reports';
        this.reports = [];
        this.currentPage = 1;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadReports();
        this.loadStats();
    }

    bindEvents() {
        document.getElementById('filterBtn')?.addEventListener('click', () => this.loadReports(1));
        document.getElementById('resetBtn')?.addEventListener('click', () => {
            document.getElementById('statusFilter').value = '';
            document.getElementById('searchInput').value = '';
            this.loadReports(1);
        });
        document.getElementById('statusFilter')?.addEventListener('change', () => this.loadReports(1));
        document.getElementById('searchInput')?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                this.loadReports(1);
            }
        });
    }

    async loadReports(page = 1) {
        try {
            this.currentPage = page;
            const params = new URLSearchParams({
                page,
                status: document.getElementById('statusFilter').value,
                search: document.getElementById('searchInput').value.trim()
            });

            const response = await fetch(`${this.apiBase}/list.php?${params}`);
            const data = await response.json();

            if (!data.success) {
                this.showMessage(data.message || 'Không thể tải danh sách báo cáo', 'error');
                return;
            }

            this.reports = data.data || [];
            this.renderTable();
            this.loadStats();
        } catch (error) {
            this.showMessage('Lỗi khi tải dữ liệu: ' + error.message, 'error');
        }
    }

    renderTable() {
        const tbody = document.querySelector('#reportsTable tbody');
        if (!tbody) return;

        if (this.reports.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">Không có báo cáo nào</td></tr>';
            return;
        }

        tbody.innerHTML = this.reports.map(report => `
            <tr>
                <td>#${report.report_id}</td>
                <td><strong>${this.escapeHtml(report.product_name || 'Không xác định')}</strong></td>
                <td>${this.escapeHtml(report.reporter_name || 'N/A')}</td>
                <td>${this.escapeHtml(report.review_user_name || 'N/A')}</td>
                <td>
                    <span style="max-width: 220px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        ${this.escapeHtml(report.reason || '')}
                    </span>
                </td>
                <td>${this.getStatusBadge(report.status)}</td>
                <td>${this.formatDate(report.created_at)}</td>
                <td style="text-align: center;">
                    <div class="actions-cell" style="justify-content: center;">
                        <button class="action-btn view" onclick="reportManager.showDetails(${report.report_id})" title="Xem chi tiết">👁️</button>
                        <select class="filter-select" style="height: 32px; min-width: 120px;" onchange="reportManager.updateStatus(${report.report_id}, this.value)">
                            <option value="">Cập nhật</option>
                            <option value="0" ${Number(report.status) === 0 ? 'disabled' : ''}>Chờ xử lý</option>
                            <option value="1" ${Number(report.status) === 1 ? 'disabled' : ''}>Đã xử lý</option>
                            <option value="2" ${Number(report.status) === 2 ? 'disabled' : ''}>Đã bỏ qua</option>
                        </select>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    showDetails(reportId) {
        const report = this.reports.find(item => Number(item.report_id) === Number(reportId));
        if (!report) {
            this.showMessage('Không tìm thấy dữ liệu báo cáo', 'error');
            return;
        }

        document.getElementById('reportDetailsContent').innerHTML = `
            <div style="display: grid; gap: 14px;">
                <div><strong>Mã báo cáo:</strong> #${report.report_id}</div>
                <div><strong>Sản phẩm:</strong> ${this.escapeHtml(report.product_name || 'Không xác định')}</div>
                <div><strong>Người báo cáo:</strong> ${this.escapeHtml(report.reporter_name || 'N/A')}</div>
                <div><strong>Người đánh giá:</strong> ${this.escapeHtml(report.review_user_name || 'N/A')}</div>
                <div><strong>Rating:</strong> ${report.rating ? `${report.rating}/5` : '-'}</div>
                <div>
                    <strong>Nội dung đánh giá:</strong>
                    <p style="margin: 6px 0 0; padding: 12px; background: #f8fafc; border-radius: 6px;">
                        ${this.escapeHtml(report.comment || 'Không có nội dung')}
                    </p>
                </div>
                <div>
                    <strong>Lý do báo cáo:</strong>
                    <p style="margin: 6px 0 0; padding: 12px; background: #fff7ed; border-radius: 6px;">
                        ${this.escapeHtml(report.reason || '')}
                    </p>
                </div>
                <div><strong>Trạng thái:</strong> ${this.getStatusBadge(report.status)}</div>
                <div><strong>Ngày tạo:</strong> ${this.formatDateTime(report.created_at)}</div>
                <div class="modal-footer" style="padding-left: 0; padding-right: 0; margin-top: 8px;">
                    <button class="btn btn-secondary" type="button" onclick="reportManager.closeModal()">Đóng</button>
                    <button class="btn btn-primary" type="button" onclick="reportManager.updateStatus(${report.report_id}, 1)">Đánh dấu đã xử lý</button>
                    <button class="btn btn-danger" type="button" onclick="reportManager.updateStatus(${report.report_id}, 2)">Bỏ qua</button>
                </div>
            </div>
        `;
        document.getElementById('reportModal').style.display = 'block';
    }

    async updateStatus(reportId, status) {
        if (status === '') return;

        const statusText = this.getStatusText(status);
        if (!confirm(`Cập nhật báo cáo #${reportId} thành "${statusText}"?`)) {
            this.renderTable();
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/update-status.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ report_id: reportId, status: Number(status) })
            });
            const data = await response.json();

            this.showMessage(data.message || (data.success ? 'Cập nhật thành công' : 'Cập nhật thất bại'), data.success ? 'success' : 'error');
            if (data.success) {
                this.closeModal();
                this.loadReports(this.currentPage);
            }
        } catch (error) {
            this.showMessage('Lỗi khi cập nhật trạng thái: ' + error.message, 'error');
        }
    }

    async loadStats() {
        try {
            const response = await fetch(`${this.apiBase}/list.php?action=stats`);
            const data = await response.json();
            if (!data.success || !data.stats) return;

            document.getElementById('totalReports').textContent = data.stats.total || 0;
            document.getElementById('pendingReports').textContent = data.stats.pending || 0;
            document.getElementById('resolvedReports').textContent = data.stats.resolved || 0;
            document.getElementById('rejectedReports').textContent = data.stats.rejected || 0;
        } catch (error) {
            console.error('Error loading report stats:', error);
        }
    }

    getStatusBadge(status) {
        const value = Number(status);
        if (value === 1) return '<span class="status-badge status-completed">Đã xử lý</span>';
        if (value === 2) return '<span class="status-badge status-cancelled">Đã bỏ qua</span>';
        return '<span class="status-badge status-pending">Chờ xử lý</span>';
    }

    getStatusText(status) {
        const labels = {
            0: 'Chờ xử lý',
            1: 'Đã xử lý',
            2: 'Đã bỏ qua'
        };
        return labels[Number(status)] || 'Không xác định';
    }

    formatDate(value) {
        if (!value) return '-';
        return new Date(value.replace(' ', 'T')).toLocaleDateString('vi-VN');
    }

    formatDateTime(value) {
        if (!value) return '-';
        return new Date(value.replace(' ', 'T')).toLocaleString('vi-VN');
    }

    closeModal() {
        const modal = document.getElementById('reportModal');
        if (modal) modal.style.display = 'none';
    }

    showMessage(message, type = 'success') {
        const container = document.getElementById('messagesContainer');
        if (!container) return;
        container.innerHTML = `<div class="message ${type}">${this.escapeHtml(message)}</div>`;
        setTimeout(() => {
            container.innerHTML = '';
        }, 4000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

const reportManager = new ReviewReportManager();
