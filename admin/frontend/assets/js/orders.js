/**
 * Admin Orders Management - JavaScript
 * File: /admin/frontend/assets/js/orders.js
 */

class OrdersManager {
    constructor() {
        this.apiBase = '/PetsAccessories/admin/backend/api/orders';
        this.currentPage = 1;
        this.currentFilters = {
            status: '',
            search: ''
        };
        this.currentOrder = null;
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.loadOrders();
    }

    attachEventListeners() {
        // Search & Filter
        document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.applyFilters();
        });
        document.getElementById('filterBtn')?.addEventListener('click', () => this.applyFilters());
        document.getElementById('resetBtn')?.addEventListener('click', () => this.resetFilters());

        // Modal
        document.getElementById('closeModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cancelModalBtn')?.addEventListener('click', () => this.closeModal());

        // Modal backdrop
        document.getElementById('orderModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'orderModal') this.closeModal();
        });

        // Status update buttons
        document.getElementById('updateStatusBtn')?.addEventListener('click', () => this.showStatusModal());
        document.getElementById('confirmStatusBtn')?.addEventListener('click', () => this.updateStatus());
        document.getElementById('cancelStatusBtn')?.addEventListener('click', () => this.closeStatusModal());

        // Export buttons
        document.getElementById('exportPdfBtn')?.addEventListener('click', () => this.exportOrder('pdf'));
        document.getElementById('exportExcelBtn')?.addEventListener('click', () => this.exportOrder('excel'));

        // Status modal backdrop
        document.getElementById('statusModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'statusModal') this.closeStatusModal();
        });
    }

    loadOrders(page = 1) {
        const params = new URLSearchParams({
            page: page,
            status: this.currentFilters.status,
            search: this.currentFilters.search
        });

        this.showLoading();
        fetch(`${this.apiBase}/list.php?${params}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.renderTable(data.data);
                    this.renderPagination(data.pagination);
                    this.currentPage = page;
                } else {
                    this.showMessage('Lỗi: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                this.showMessage('Lỗi tải dữ liệu', 'error');
            })
            .finally(() => this.hideLoading());
    }

    renderTable(orders) {
        const tableBody = document.querySelector('.orders-table tbody');
        if (!tableBody) return;

        if (orders.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <h3>Không có đơn hàng</h3>
                            <p>Chưa có đơn hàng nào trong hệ thống</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = orders.map(order => `
            <tr>
                <td>#${order.order_id}</td>
                <td>
                    <strong>${order.user_name}</strong><br/>
                    <small>${order.email}</small>
                </td>
                <td>${order.item_count} sản phẩm</td>
                <td style="text-align: right;">${this.formatCurrency(order.total_price)}</td>
                <td>
                    <span class="status-badge status-${this.getStatusClass(order.order_status)}">
                        ${order.order_status_label}
                    </span>
                </td>
                <td>
                    <span class="status-badge payment-status-${this.getPaymentStatusClass(order.payment_status)}">
                        ${order.payment_status_label}
                    </span>
                </td>
                <td>${this.formatDate(order.created_at)}</td>
                <td>
                    <div class="actions-cell">
                        <button class="action-btn edit" onclick="ordersManager.viewOrder(${order.order_id})">👁️ Xem</button>
                        <button class="action-btn delete" onclick="ordersManager.deleteOrder(${order.order_id})">🗑️ Xóa</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderPagination(pagination) {
        const paginationContainer = document.querySelector('.pagination-container');
        if (!paginationContainer) return;

        let html = '<div class="pagination">';

        if (pagination.page > 1) {
            html += `<button onclick="ordersManager.loadOrders(1)">⏮️</button>`;
            html += `<button onclick="ordersManager.loadOrders(${pagination.page - 1})">◀</button>`;
        }

        for (let i = 1; i <= pagination.pages; i++) {
            if (i === pagination.page) {
                html += `<button class="active">${i}</button>`;
            } else if (i <= 5 || i === pagination.pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
                html += `<button onclick="ordersManager.loadOrders(${i})">${i}</button>`;
            } else if (i === 6 || i === pagination.pages - 1) {
                html += `<span>...</span>`;
            }
        }

        if (pagination.page < pagination.pages) {
            html += `<button onclick="ordersManager.loadOrders(${pagination.page + 1})">▶</button>`;
            html += `<button onclick="ordersManager.loadOrders(${pagination.pages})">⏭️</button>`;
        }

        html += '</div>';
        paginationContainer.innerHTML = html;
    }

    viewOrder(id) {
        this.showLoading();
        fetch(`${this.apiBase}/get.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.currentOrder = data.data;
                    this.renderOrderDetail(data.data);
                    this.openModal();
                } else {
                    this.showMessage('Lỗi: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                this.showMessage('Lỗi tải dữ liệu', 'error');
            })
            .finally(() => this.hideLoading());
    }

    renderOrderDetail(order) {
        const modalBody = document.querySelector('.modal-body');
        if (!modalBody) return;

        const itemsHtml = order.items.map(item => `
            <tr>
                <td>${item.product_name}</td>
                <td style="text-align: center;">${item.quantity}</td>
                <td style="text-align: right;">${this.formatCurrency(item.price_at_purchase)}</td>
                <td style="text-align: right;"><strong>${this.formatCurrency(item.subtotal)}</strong></td>
            </tr>
        `).join('');

        const historyHtml = order.status_history.map(h => `
            <div style="padding: 10px; border-bottom: 1px solid #eee;">
                <span class="status-badge" style="background: #e3f2fd; color: #1976d2;">${h.status_label}</span>
                <small style="color: #999; margin-left: 10px;">${this.formatDateTime(h.changed_at)}</small>
            </div>
        `).join('');

        modalBody.innerHTML = `
            <div class="order-detail-wrapper">
                <!-- Order Info Section -->
                <div class="detail-section">
                    <h3>📋 Thông tin đơn hàng #${order.order_id}</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Ngày tạo</label>
                            <div class="value">${this.formatDateTime(order.created_at)}</div>
                        </div>
                        <div class="detail-item">
                            <label>Trạng thái đơn hàng</label>
                            <div class="value">
                                <span class="status-badge status-${this.getStatusClass(order.order_status)}">
                                    ${order.order_status_label}
                                </span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <label>Trạng thái thanh toán</label>
                            <div class="value">
                                <span class="status-badge payment-status-${this.getPaymentStatusClass(order.payment_status)}">
                                    ${order.payment_status_label}
                                </span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <label>Phương thức thanh toán</label>
                            <div class="value">${order.payment_method || '-'}</div>
                        </div>
                    </div>
                </div>

                <!-- Customer Info Section -->
                <div class="detail-section">
                    <h3>👤 Thông tin khách hàng</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Tên khách hàng</label>
                            <div class="value">${order.user_name}</div>
                        </div>
                        <div class="detail-item">
                            <label>Email</label>
                            <div class="value">${order.email}</div>
                        </div>
                        <div class="detail-item">
                            <label>Điện thoại</label>
                            <div class="value">${order.phone || '-'}</div>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <label style="font-weight: 600; font-size: 12px; color: #999;">Địa chỉ giao hàng</label>
                        <div style="margin-top: 5px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                            ${order.shipping_address}
                        </div>
                    </div>
                </div>

                <!-- Order Items Section -->
                <div class="detail-section">
                    <h3>📦 Chi tiết sản phẩm (${order.items.length})</h3>
                    <table class="order-items-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th style="text-align: center;">Số lượng</th>
                                <th style="text-align: right;">Giá</th>
                                <th style="text-align: right;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>

                <!-- Order Summary Section -->
                <div class="detail-section">
                    <h3>💰 Tổng kết</h3>
                    <div style="background: #f5f5f5; padding: 15px; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px;">
                            <span>Tổng tiền hàng:</span>
                            <strong>${this.formatCurrency(order.items.reduce((sum, i) => sum + i.subtotal, 0))}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px;">
                            <span>Phí vận chuyển:</span>
                            <strong>${this.formatCurrency(order.shipping_fee)}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px;">
                            <span>Giảm giá:</span>
                            <strong style="color: #4CAF50;">-${this.formatCurrency(order.discount_amount)}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; border-top: 2px solid #ddd; padding-top: 10px;">
                            <span>Tổng cộng:</span>
                            <strong style="color: #ff6b6b; font-size: 16px;">${this.formatCurrency(order.total_price)}</strong>
                        </div>
                    </div>
                </div>

                <!-- Status History Section -->
                ${order.status_history && order.status_history.length > 0 ? `
                <div class="detail-section">
                    <h3>📜 Lịch sử cập nhật</h3>
                    ${historyHtml}
                </div>
                ` : ''}

                <!-- Notes Section -->
                ${order.notes ? `
                <div class="detail-section">
                    <h3>📝 Ghi chú</h3>
                    <div style="padding: 10px; background: #fff9e6; border-radius: 4px; border-left: 3px solid #ff9800;">
                        ${order.notes}
                    </div>
                </div>
                ` : ''}
            </div>
        `;
    }

    showStatusModal() {
        if (!this.currentOrder) return;

        const modal = document.getElementById('statusModal');
        const statusSelect = document.getElementById('newStatusSelect');
        const reasonInput = document.getElementById('reasonInput');

        statusSelect.value = '';
        reasonInput.value = '';

        modal.classList.add('show');
    }

    closeStatusModal() {
        const modal = document.getElementById('statusModal');
        modal.classList.remove('show');
    }

    updateStatus() {
        if (!this.currentOrder) return;

        const newStatus = document.getElementById('newStatusSelect').value;
        const reason = document.getElementById('reasonInput').value;

        if (!newStatus) {
            this.showMessage('Vui lòng chọn trạng thái mới', 'warning');
            return;
        }

        this.showLoading();
        fetch(`${this.apiBase}/update-status.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                order_id: this.currentOrder.order_id,
                new_status: newStatus,
                reason: reason
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showMessage('Cập nhật trạng thái thành công', 'success');
                    this.closeStatusModal();
                    this.viewOrder(this.currentOrder.order_id);
                    this.loadOrders(this.currentPage);
                } else {
                    this.showMessage('Lỗi: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                this.showMessage('Lỗi cập nhật trạng thái', 'error');
            })
            .finally(() => this.hideLoading());
    }

    exportOrder(format) {
        if (!this.currentOrder) return;

        const link = document.createElement('a');
        link.href = `${this.apiBase}/export.php?id=${this.currentOrder.order_id}&format=${format}`;
        link.click();
    }

    deleteOrder(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa đơn hàng này? Hành động này không thể hoàn tác.')) {
            return;
        }

        // TODO: Implement delete API
        this.showMessage('Chức năng xóa đang được phát triển', 'warning');
    }

    applyFilters() {
        this.currentFilters.status = document.getElementById('statusFilter').value;
        this.currentFilters.search = document.getElementById('searchInput').value;
        this.loadOrders(1);
    }

    resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        this.currentFilters = { status: '', search: '' };
        this.loadOrders(1);
    }

    openModal() {
        const modal = document.getElementById('orderModal');
        modal.classList.add('show');
    }

    closeModal() {
        const modal = document.getElementById('orderModal');
        modal.classList.remove('show');
        this.currentOrder = null;
    }

    showMessage(message, type = 'info') {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        const messageEl = document.createElement('div');
        messageEl.className = `message ${type}`;
        messageEl.innerHTML = `
            <span>${message}</span>
            <button style="background: none; border: none; cursor: pointer; font-size: 16px;" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(messageEl);

        // Auto remove after 5 seconds
        setTimeout(() => {
            messageEl.remove();
        }, 5000);
    }

    showLoading() {
        const tableBody = document.querySelector('.orders-table tbody');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;">
                        <div class="loading"></div>
                        <p style="margin-top: 15px;">Đang tải...</p>
                    </td>
                </tr>
            `;
        }
    }

    hideLoading() {
        // Loading will be replaced by actual data
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            minimumFractionDigits: 0
        }).format(value);
    }

    formatDate(date) {
        const d = new Date(date);
        return d.toLocaleDateString('vi-VN');
    }

    formatDateTime(date) {
        const d = new Date(date);
        return d.toLocaleString('vi-VN');
    }

    getStatusClass(status) {
        return status.toLowerCase().replace(/_/g, '-');
    }

    getPaymentStatusClass(status) {
        return status.toLowerCase().replace(/_/g, '-');
    }
}

// Initialize when DOM is ready
let ordersManager;
document.addEventListener('DOMContentLoaded', () => {
    ordersManager = new OrdersManager();
});
