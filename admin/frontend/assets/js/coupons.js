/**
 * Script quản lý mã giảm giá
 * File: /admin/frontend/assets/js/coupons.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const filterBtn = document.getElementById('filterBtn');
    const resetBtn = document.getElementById('resetBtn');
    const couponsTableBody = document.getElementById('couponsTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const messagesContainer = document.getElementById('messagesContainer');
    
    // Modal Elements
    const modal = document.getElementById('couponModal');
    const modalTitle = document.getElementById('couponModalTitle');
    const addBtn = document.getElementById('addCouponBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');
    const saveBtn = document.getElementById('saveCouponBtn');
    const form = document.getElementById('couponForm');
    
    // Form Inputs
    const codeInput = document.getElementById('codeInput');
    const discountTypeInput = document.getElementById('discountTypeInput');
    const discountValueInput = document.getElementById('discountValueInput');
    const minOrderValueInput = document.getElementById('minOrderValueInput');
    const maxDiscountInput = document.getElementById('maxDiscountInput');
    const maxDiscountContainer = document.getElementById('maxDiscountContainer');
    const usageLimitInput = document.getElementById('usageLimitInput');
    const expiryDateInput = document.getElementById('expiryDateInput');
    const statusInput = document.getElementById('statusInput');
    
    // State
    let currentPage = 1;
    let editingId = null;

    // Khởi tạo
    init();

    function init() {
        loadCoupons();
        setupEventListeners();
    }

    function setupEventListeners() {
        // Tìm kiếm & Lọc
        filterBtn.addEventListener('click', () => {
            currentPage = 1;
            loadCoupons();
        });

        resetBtn.addEventListener('click', () => {
            searchInput.value = '';
            statusFilter.value = '';
            currentPage = 1;
            loadCoupons();
        });

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadCoupons();
            }
        });

        // Đổi loại giảm giá -> Ẩn hiện giảm tối đa
        discountTypeInput.addEventListener('change', () => {
            if (discountTypeInput.value === 'fixed') {
                maxDiscountContainer.style.display = 'none';
                maxDiscountInput.value = '';
            } else {
                maxDiscountContainer.style.display = 'block';
            }
        });

        // Modal
        addBtn.addEventListener('click', openAddModal);
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Lưu dữ liệu
        saveBtn.addEventListener('click', saveCoupon);
    }

    function loadCoupons() {
        couponsTableBody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px;">
                    <div class="loading"></div>
                    <p style="margin-top: 15px;">Đang tải...</p>
                </td>
            </tr>
        `;

        const search = searchInput.value.trim();
        const status = statusFilter.value;
        const params = new URLSearchParams({ page: currentPage, search });
        if (status !== '') params.set('status', status);
        const url = `/PetsAccessories/admin/backend/api/coupons/list.php?${params.toString()}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderCoupons(data.data);
                    renderPagination(data.pagination);
                } else {
                    showMessage(data.message || 'Có lỗi xảy ra khi tải dữ liệu', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Lỗi kết nối server', 'error');
            });
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    function renderCoupons(coupons) {
        if (!coupons || coupons.length === 0) {
            couponsTableBody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px;">
                        Không tìm thấy mã giảm giá nào
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        coupons.forEach(coupon => {
            const isPercentage = coupon.discount_type === 'percentage';
            const valueStr = isPercentage ? `${coupon.discount_value}%` : formatCurrency(coupon.discount_value);
            const typeClass = isPercentage ? 'type-percentage' : 'type-fixed';
            const typeLabel = isPercentage ? 'Phần trăm' : 'Cố định';
            
            const limitStr = coupon.usage_limit ? `${coupon.used_count}/${coupon.usage_limit}` : `${coupon.used_count}/∞`;
            
            let expiryStr = '-';
            if (coupon.expiry_date) {
                const date = new Date(coupon.expiry_date);
                expiryStr = date.toLocaleDateString('vi-VN');
            }

            const statusClass = coupon.status === 1 ? 'status-active' : 'status-inactive';
            const statusText = coupon.status === 1 ? 'Kích hoạt' : 'Vô hiệu';

            html += `
                <tr>
                    <td>${coupon.coupon_id}</td>
                    <td><strong>${coupon.code}</strong></td>
                    <td>
                        <span class="type-badge ${typeClass}">${typeLabel}</span><br>
                        ${valueStr}
                    </td>
                    <td>${coupon.min_order_value > 0 ? formatCurrency(coupon.min_order_value) : '0 ₫'}</td>
                    <td>${limitStr}</td>
                    <td>${expiryStr}</td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td class="action-buttons">
                        <button class="btn btn-sm btn-info" onclick="window.editCoupon(${coupon.coupon_id})">✏️ Sửa</button>
                        <button class="btn btn-sm btn-danger" onclick="window.deleteCoupon(${coupon.coupon_id}, '${coupon.code}')">🗑️ Xóa</button>
                    </td>
                </tr>
            `;
        });

        couponsTableBody.innerHTML = html;
    }

    function renderPagination(pagination) {
        if (!pagination || pagination.total_pages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<div class="pagination">';
        
        // Prev button
        if (pagination.current_page > 1) {
            html += `<button class="page-btn" onclick="window.changePage(${pagination.current_page - 1})">«</button>`;
        }

        // Pages
        for (let i = 1; i <= pagination.total_pages; i++) {
            if (
                i === 1 || 
                i === pagination.total_pages || 
                (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)
            ) {
                const activeClass = i === pagination.current_page ? 'active' : '';
                html += `<button class="page-btn ${activeClass}" onclick="window.changePage(${i})">${i}</button>`;
            } else if (
                i === pagination.current_page - 3 || 
                i === pagination.current_page + 3
            ) {
                html += `<span class="page-dots">...</span>`;
            }
        }

        // Next button
        if (pagination.current_page < pagination.total_pages) {
            html += `<button class="page-btn" onclick="window.changePage(${pagination.current_page + 1})">»</button>`;
        }

        html += '</div>';
        paginationContainer.innerHTML = html;
    }

    window.changePage = function(page) {
        currentPage = page;
        loadCoupons();
    };

    function openAddModal() {
        editingId = null;
        modalTitle.innerHTML = '➕ Thêm Mã Giảm Giá';
        form.reset();
        maxDiscountContainer.style.display = 'block'; // Mặc định là percentage
        modal.style.display = 'block';
    }

    window.editCoupon = function(id) {
        fetch(`/PetsAccessories/admin/backend/api/coupons/get.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    const coupon = data.data;
                    editingId = id;
                    modalTitle.innerHTML = '✏️ Cập Nhật Mã Giảm Giá';

                    codeInput.value = coupon.code;
                    discountTypeInput.value = coupon.discount_type;
                    discountValueInput.value = coupon.discount_value;
                    minOrderValueInput.value = coupon.min_order_value || '';
                    maxDiscountInput.value = coupon.max_discount || '';
                    usageLimitInput.value = coupon.usage_limit || '';

                    if (coupon.expiry_date) {
                        expiryDateInput.value = coupon.expiry_date.split(' ')[0];
                    } else {
                        expiryDateInput.value = '';
                    }

                    statusInput.value = coupon.status;

                    if (coupon.discount_type === 'fixed') {
                        maxDiscountContainer.style.display = 'none';
                    } else {
                        maxDiscountContainer.style.display = 'block';
                    }

                    modal.style.display = 'block';
                }
            });
    };

    function closeModal() {
        modal.style.display = 'none';
        form.reset();
    }

    function saveCoupon() {
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const data = {
            code: codeInput.value.trim(),
            discount_type: discountTypeInput.value,
            discount_value: parseFloat(discountValueInput.value),
            min_order_value: minOrderValueInput.value ? parseFloat(minOrderValueInput.value) : 0,
            max_discount: maxDiscountInput.value ? parseFloat(maxDiscountInput.value) : null,
            usage_limit: usageLimitInput.value ? parseInt(usageLimitInput.value) : null,
            expiry_date: expiryDateInput.value ? expiryDateInput.value + ' 23:59:59' : null,
            status: parseInt(statusInput.value)
        };

        if (editingId) {
            data.coupon_id = editingId;
        }

        const url = editingId ? 
            '/PetsAccessories/admin/backend/api/coupons/update.php' : 
            '/PetsAccessories/admin/backend/api/coupons/create.php';
            
        const method = editingId ? 'PUT' : 'POST';

        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Đang lưu...';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                showMessage(result.message, 'success');
                closeModal();
                loadCoupons();
            } else {
                let errorMsg = result.message;
                if (result.errors) {
                    const firstErr = Object.values(result.errors)[0];
                    errorMsg += `: ${firstErr}`;
                }
                showMessage(errorMsg, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showMessage('Lỗi kết nối server', 'error');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '💾 Lưu';
        });
    }

    window.deleteCoupon = function(id, code) {
        if (confirm(`Bạn có chắc chắn muốn xóa mã "${code}" không? \n(Nếu mã đã được dùng, nó sẽ chỉ bị vô hiệu hóa)`)) {
            fetch('/PetsAccessories/admin/backend/api/coupons/delete.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ coupon_id: id })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    showMessage(result.message, 'success');
                    loadCoupons();
                } else {
                    showMessage(result.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showMessage('Lỗi kết nối server', 'error');
            });
        }
    };

    function showMessage(text, type = 'success') {
        const msgDiv = document.createElement('div');
        msgDiv.className = `alert alert-${type}`;
        msgDiv.style.padding = '10px 15px';
        msgDiv.style.marginBottom = '15px';
        msgDiv.style.borderRadius = '4px';
        msgDiv.style.backgroundColor = type === 'success' ? '#d4edda' : '#f8d7da';
        msgDiv.style.color = type === 'success' ? '#155724' : '#721c24';
        msgDiv.style.border = `1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'}`;
        msgDiv.textContent = text;
        
        messagesContainer.innerHTML = '';
        messagesContainer.appendChild(msgDiv);

        setTimeout(() => {
            msgDiv.remove();
        }, 5000);
    }
});
