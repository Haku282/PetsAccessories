/**
 * Quản lý Khách Hàng (Clients)
 * File: /admin/frontend/assets/js/clients.js
 */

const clientManager = {
    apiBaseUrl: '/PetsAccessories/admin/backend/api/clients',

    init() {
        this.cacheDOM();
        this.bindEvents(); 
        this.loadStats();
        this.loadClients();
    },

    bindEvents() {
        // Sự kiện Click nút Tìm kiếm
        this.searchBtn.addEventListener('click', () => {
            const keyword = this.searchInput.value.trim();
            if (keyword !== "") {
                this.loadClients(keyword);
                this.resetSearchBtn.style.display = 'flex'; // Hiện nút Hủy (flex để căn giữa icon)
            }
        });

        // Sự kiện nhấn phím Enter trong ô input
        this.searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                this.searchBtn.click();
            }
        });

        // Sự kiện nút Hủy
        this.resetSearchBtn.addEventListener('click', () => {
            this.searchInput.value = '';
            this.loadClients();
            this.resetSearchBtn.style.display = 'none';
        });
    },

    cacheDOM() {
        this.tableBody = document.querySelector('#clientsTable tbody');
        this.modal = document.getElementById('clientModal');
        this.detailsContent = document.getElementById('clientDetailsContent');
        this.messagesContainer = document.getElementById('messagesContainer');

        // Thêm các nút tìm kiếm
        this.searchInput = document.getElementById('searchInput');
        this.searchBtn = document.getElementById('searchBtn');
        this.resetSearchBtn = document.getElementById('resetSearchBtn');
    },

    async loadStats() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/stats.php`);
            const data = await response.json();
            if (data.success) {
                document.getElementById('totalClients').textContent = data.stats.total;
                document.getElementById('newClients').textContent = data.stats.new_clients;
                document.getElementById('frequentClients').textContent = data.stats.frequent_clients;
            }
        } catch (error) {
            console.error('Lỗi tải thống kê:', error);
        }
    },

    async loadClients(searchKeyword = '') {
        try {
            this.tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center;">Đang tải dữ liệu...</td></tr>`;
            
            // Nếu có từ khóa tìm kiếm, gắn thêm ?search=... vào link API
            const url = searchKeyword 
                ? `${this.apiBaseUrl}/list.php?search=${encodeURIComponent(searchKeyword)}`
                : `${this.apiBaseUrl}/list.php`;

            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                this.renderTable(data.clients);
            } else {
                this.tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:red;">${data.message}</td></tr>`;
            }
        } catch (error) {
            this.tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:red;">Lỗi kết nối máy chủ</td></tr>`;
        }
    },

    renderTable(clients) {
        if (!clients || clients.length === 0) {
            this.tableBody.innerHTML = `<tr><td colspan="7" style="text-align: center;">Chưa có khách hàng nào.</td></tr>`;
            return;
        }

        this.tableBody.innerHTML = clients.map(client => `
            <tr>
                <td>#${client.id}</td>
                <td><strong>${client.name}</strong></td>
                <td>
                    📧 ${client.email || 'Không có'}<br>
                    📞 ${client.phone || 'Không có'}
                </td>
                <td>${client.total_orders} đơn</td>
                <td><strong style="color: #E53935;">${Number(client.total_spent || 0).toLocaleString('vi-VN')} đ</strong></td>
                <td>${new Date(client.created_at).toLocaleDateString('vi-VN')}</td>
                <td style="text-align: center;">
                    <button class="action-btn view" onclick="clientManager.viewHistory(${client.id})">👁️ Xem Lịch Sử</button>
                </td>
            </tr>
        `).join('');
    },

    async viewHistory(id) {
        this.detailsContent.innerHTML = '<div style="text-align:center;">Đang tải...</div>';
        this.modal.classList.add('show');

        try {
            const response = await fetch(`${this.apiBaseUrl}/get.php?id=${id}`);
            const data = await response.json();

            if (data.success) {
                const client = data.client;
                const orders = data.orders;

                let ordersHtml = '';
                if (orders.length > 0) {
                    ordersHtml = `
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Mã Đơn</th>
                                    <th>Ngày Đặt</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${orders.map(o => `
                                    <tr>
                                        <td>#${o.order_id}</td>
                                        <td>${new Date(o.created_at).toLocaleDateString('vi-VN')}</td>
                                        <td>${Number(o.total_price).toLocaleString('vi-VN')} đ</td>
                                        <td><span class="status-badge status-${o.status.toLowerCase()}">${o.status}</span></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    ordersHtml = '<p style="color: #666; margin-top: 10px;">Khách hàng này chưa có đơn hàng nào.</p>';
                }

                this.detailsContent.innerHTML = `
                    <div style="margin-bottom: 20px;">
                        <h4>👤 Thông tin khách hàng</h4>
                        <p><strong>Họ tên:</strong> ${client.name}</p>
                        <p><strong>Email:</strong> ${client.email}</p>
                        <p><strong>SĐT:</strong> ${client.phone}</p>
                        <p><strong>Địa chỉ:</strong> ${client.address || 'Chưa cập nhật'}</p>
                    </div>
                    <hr>
                    <div style="margin-top: 20px;">
                        <h4>🛒 Lịch sử mua hàng</h4>
                        ${ordersHtml}
                    </div>
                `;
            } else {
                this.detailsContent.innerHTML = `<p style="color:red;">${data.message}</p>`;
            }
        } catch (error) {
            this.detailsContent.innerHTML = `<p style="color:red;">Lỗi kết nối máy chủ</p>`;
        }
    },

    closeModal() {
        this.modal.classList.remove('show');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    clientManager.init();
});