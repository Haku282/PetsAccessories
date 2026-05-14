/**
 * Quản lý Khách Hàng (Clients)
 * File: /admin/frontend/assets/js/clients.js
 */

const clientManager = {
    apiBaseUrl: '/PetsAccessories/admin/backend/api/clients',
    currentClientId: null, // Lưu ID client đang xem

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
                document.getElementById('potentialClients').textContent = data.stats.potential_clients || 0;
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
        this.currentClientId = id; // Lưu ID client
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
                                    <th style="text-align: center;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${orders.map(o => `
                                    <tr>
                                        <td>#${o.order_id}</td>
                                        <td>${new Date(o.created_at).toLocaleDateString('vi-VN')}</td>
                                        <td>${Number(o.total_price).toLocaleString('vi-VN')} đ</td>
                                        <td><span class="status-badge status-${o.status.toLowerCase()}">${o.status}</span></td>
                                        <td style="text-align: center;">
                                            <button class="action-btn view" onclick="clientManager.viewOrderDetails(${o.order_id})" style="padding: 5px 10px; font-size: 12px;">
                                                👁️ Chi Tiết
                                            </button>
                                        </td>
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

    async viewOrderDetails(orderId) {
        // Show loading state
        const loadingContent = `
            <div style="text-align: center; padding: 20px;">
                <p>Đang tải chi tiết đơn hàng...</p>
            </div>
        `;
        this.detailsContent.innerHTML = loadingContent;

        try {
            const response = await fetch(`/PetsAccessories/admin/backend/api/orders/get.php?id=${orderId}`);
            const data = await response.json();

            if (data.success) {
                const order = data.data; // Note: API returns data.data, not data.order
                const items = order.items || [];

                let itemsHtml = '';
                if (items.length > 0) {
                    itemsHtml = `
                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <thead style="background-color: #f5f5f5;">
                                <tr>
                                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Sản Phẩm</th>
                                    <th style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">Số Lượng</th>
                                    <th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">Giá</th>
                                    <th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">Thành Tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map(item => `
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 10px;">${item.product_name || 'Sản phẩm'}</td>
                                        <td style="padding: 10px; text-align: center;">${item.quantity}</td>
                                        <td style="padding: 10px; text-align: right;">${Number(item.price_at_purchase).toLocaleString('vi-VN')} đ</td>
                                        <td style="padding: 10px; text-align: right; font-weight: bold;">${Number(item.subtotal).toLocaleString('vi-VN')} đ</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    itemsHtml = '<p style="color: #666; margin-top: 10px;">Không có chi tiết sản phẩm.</p>';
                }

                const shippingInfo = order.shipping_method || 'Chưa xác định';
                const paymentMethod = order.payment_method || 'Chưa xác định';

                this.detailsContent.innerHTML = `
                    <div style="max-height: 600px; overflow-y: auto;">
                        <div style="margin-bottom: 20px;">
                            <h4>📦 Chi Tiết Đơn Hàng #${order.order_id}</h4>
                            <p><strong>Ngày đặt:</strong> ${new Date(order.created_at).toLocaleDateString('vi-VN')} ${new Date(order.created_at).toLocaleTimeString('vi-VN')}</p>
                            <p><strong>Trạng thái:</strong> <span class="status-badge" style="background-color: ${order.order_status_color}; padding: 5px 10px; border-radius: 3px; color: white;">${order.order_status_label}</span></p>
                            <p><strong>Thanh toán:</strong> <span class="status-badge" style="background-color: ${order.payment_status_color}; padding: 5px 10px; border-radius: 3px; color: white;">${order.payment_status_label}</span></p>
                        </div>

                        <hr>

                        <div style="margin-top: 15px; margin-bottom: 20px;">
                            <h4>👥 Thông tin giao hàng</h4>
                            <p><strong>Người nhận:</strong> ${order.fullname || 'Chưa cập nhật'}</p>
                            <p><strong>SĐT:</strong> ${order.phone || 'Chưa cập nhật'}</p>
                            <p><strong>Địa chỉ:</strong> ${order.shipping_address || 'Chưa cập nhật'}</p>
                            <p><strong>Phương thức:</strong> ${shippingInfo}</p>
                            <p><strong>Phí vận chuyển:</strong> ${Number(order.shipping_fee || 0).toLocaleString('vi-VN')} đ</p>
                        </div>

                        <hr>

                        <div style="margin-top: 15px; margin-bottom: 20px;">
                            <h4>🛍️ Chi Tiết Sản Phẩm</h4>
                            ${itemsHtml}
                        </div>

                        <hr>

                        <div style="margin-top: 15px; margin-bottom: 20px;">
                            <h4>💳 Thông tin thanh toán</h4>
                            <p><strong>Phương thức:</strong> ${paymentMethod}</p>
                            <div style="margin-top: 15px; background-color: #f9f9f9; padding: 10px; border-radius: 5px;">
                                <p style="margin: 5px 0;"><strong>Tổng tiền hàng:</strong> ${(Number(order.total_price) - Number(order.shipping_fee) + Number(order.discount_amount)).toLocaleString('vi-VN')} đ</p>
                                ${Number(order.discount_amount) > 0 ? `<p style="margin: 5px 0; color: #4CAF50;"><strong>- Giảm giá:</strong> ${Number(order.discount_amount).toLocaleString('vi-VN')} đ</p>` : ''}
                                <p style="margin: 5px 0;"><strong>+ Phí vận chuyển:</strong> ${Number(order.shipping_fee).toLocaleString('vi-VN')} đ</p>
                                <p style="margin-top: 10px; font-size: 16px; color: #E53935;">
                                    <strong>Tổng cộng: ${Number(order.total_price).toLocaleString('vi-VN')} đ</strong>
                                </p>
                            </div>
                        </div>

                        <div style="margin-top: 20px; text-align: center;">
                            <button class="btn-back" onclick="clientManager.backToClientHistory()">
                                ← Quay Lại
                            </button>
                        </div>
                    </div>
                `;
            } else {
                this.detailsContent.innerHTML = `<p style="color:red;">Lỗi: ${data.message}</p>`;
            }
        } catch (error) {
            console.error('Lỗi:', error);
            this.detailsContent.innerHTML = `<p style="color:red;">Lỗi kết nối máy chủ</p>`;
        }
    },

    backToClientHistory() {
        // Quay lại xem danh sách đơn hàng của client
        if (this.currentClientId) {
            this.viewHistory(this.currentClientId);
        } else {
            this.closeModal();
        }
    },

    closeModal() {
        this.modal.classList.remove('show');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    clientManager.init();
});
