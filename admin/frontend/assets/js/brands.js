/**
 * Admin Brands Management - JavaScript
 * File: /admin/frontend/assets/js/brands.js
 */

class BrandsManager {
    constructor() {
        this.apiBase = '/PetsAccessories/admin/backend/api/brands';
        this.currentPage = 1;
        this.currentFilters = {
            search: ''
        };
        this.currentBrand = null;
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.loadBrands();
    }

    attachEventListeners() {
        // Search & Filter
        document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.applyFilters();
        });
        document.getElementById('filterBtn')?.addEventListener('click', () => this.applyFilters());
        document.getElementById('resetBtn')?.addEventListener('click', () => this.resetFilters());

        // Add Brand
        document.getElementById('addBrandBtn')?.addEventListener('click', () => this.showAddModal());

        // Modal
        document.getElementById('closeModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cancelModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('saveBrandBtn')?.addEventListener('click', () => this.saveBrand());

        // Modal backdrop
        document.getElementById('brandModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'brandModal') this.closeModal();
        });

        // Logo upload
        document.getElementById('logoUploadBtn')?.addEventListener('click', () => {
            document.getElementById('logoUploadInput').click();
        });

        document.getElementById('logoUploadInput')?.addEventListener('change', (e) => {
            this.handleLogoUpload(e);
        });
    }

    loadBrands(page = 1) {
        const params = new URLSearchParams({
            page: page,
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

    renderTable(brands) {
        const tableBody = document.querySelector('.brands-table tbody');
        if (!tableBody) return;

        if (brands.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="empty-state">
                            <div class="empty-state-icon">🏷️</div>
                            <h3>Không có thương hiệu</h3>
                            <p>Hãy thêm thương hiệu đầu tiên</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = brands.map(brand => `
            <tr>
                <td>${brand.brand_id}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        ${brand.brand_logo ? `<img src="/PetsAccessories/uploads/brands/${brand.brand_logo}" alt="${brand.brand_name}" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover;">` : '<div style="width: 40px; height: 40px; background: #e5e7eb; border-radius: 4px;"></div>'}
                        <strong>${brand.brand_name}</strong>
                    </div>
                </td>
                <td>${brand.description ? brand.description.substring(0, 50) + '...' : '-'}</td>
                <td><span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">${brand.product_count} sản phẩm</span></td>
                <td>
                    <div class="actions-cell">
                        <button class="action-btn edit" onclick="brandsManager.editBrand(${brand.brand_id})">✏️ Sửa</button>
                        <button class="action-btn delete" onclick="brandsManager.deleteBrand(${brand.brand_id})">🗑️ Xóa</button>
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
            html += `<button onclick="brandsManager.loadBrands(1)">⏮️</button>`;
            html += `<button onclick="brandsManager.loadBrands(${pagination.page - 1})">◀</button>`;
        }

        for (let i = 1; i <= pagination.pages; i++) {
            if (i === pagination.page) {
                html += `<button class="active">${i}</button>`;
            } else if (i <= 5 || i === pagination.pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
                html += `<button onclick="brandsManager.loadBrands(${i})">${i}</button>`;
            } else if (i === 6 || i === pagination.pages - 1) {
                html += `<span>...</span>`;
            }
        }

        if (pagination.page < pagination.pages) {
            html += `<button onclick="brandsManager.loadBrands(${pagination.page + 1})">▶</button>`;
            html += `<button onclick="brandsManager.loadBrands(${pagination.pages})">⏭️</button>`;
        }

        html += '</div>';
        paginationContainer.innerHTML = html;
    }

    showAddModal() {
        this.currentBrand = null;
        document.getElementById('brandModalTitle').textContent = '➕ Thêm Thương Hiệu';
        document.getElementById('brandForm').reset();
        document.getElementById('logoPreview').innerHTML = '';
        this.openModal();
    }

    editBrand(id) {
        fetch(`${this.apiBase}/get.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.currentBrand = data.data;
                    this.populateForm(data.data);
                    document.getElementById('brandModalTitle').textContent = '✏️ Chỉnh Sửa Thương Hiệu';
                    this.openModal();
                } else {
                    this.showMessage('Lỗi: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                this.showMessage('Lỗi tải dữ liệu', 'error');
            });
    }

    populateForm(brand) {
        document.getElementById('brandNameInput').value = brand.brand_name;
        document.getElementById('brandDescriptionInput').value = brand.description || '';
        document.getElementById('brandLogoInput').value = brand.brand_logo || '';

        // Show logo preview
        if (brand.brand_logo) {
            const logoPreview = document.getElementById('logoPreview');
            logoPreview.innerHTML = `
                <img src="/PetsAccessories/uploads/brands/${brand.brand_logo}" alt="${brand.brand_name}" style="max-width: 100px; height: 100px; border-radius: 6px; object-fit: cover; border: 2px solid #e5e7eb;">
            `;
        }
    }

    handleLogoUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Simple preview - in production, you'd upload to server
        const reader = new FileReader();
        reader.onload = (event) => {
            const logoPreview = document.getElementById('logoPreview');
            logoPreview.innerHTML = `
                <img src="${event.target.result}" style="max-width: 100px; height: 100px; border-radius: 6px; object-fit: cover; border: 2px solid #e5e7eb;">
            `;
            document.getElementById('brandLogoInput').value = file.name;
        };
        reader.readAsDataURL(file);
    }

    saveBrand() {
        const formData = {
            brand_name: document.getElementById('brandNameInput').value,
            description: document.getElementById('brandDescriptionInput').value,
            brand_logo: document.getElementById('brandLogoInput').value
        };

        if (!formData.brand_name.trim()) {
            this.showMessage('Tên thương hiệu không được để trống', 'error');
            return;
        }

        const url = this.currentBrand ? `${this.apiBase}/update.php` : `${this.apiBase}/add.php`;
        const method = this.currentBrand ? 'PUT' : 'POST';

        if (this.currentBrand) {
            formData.brand_id = this.currentBrand.brand_id;
        }

        const submitBtn = document.getElementById('saveBrandBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="loading"></div> Đang lưu...';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showMessage(data.message, 'success');
                    this.closeModal();
                    this.loadBrands(this.currentPage);
                } else {
                    this.showMessage('Lỗi: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                this.showMessage('Lỗi: ' + err.message, 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '💾 Lưu';
            });
    }

    deleteBrand(id) {
        if (!confirm('Bạn chắc chắn muốn xóa thương hiệu này?')) return;

        fetch(`${this.apiBase}/delete.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ brand_id: id })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showMessage(data.message, 'success');
                    this.loadBrands(this.currentPage);
                } else {
                    this.showMessage('Lỗi: ' + data.message, 'error');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                this.showMessage('Lỗi: ' + err.message, 'error');
            });
    }

    applyFilters() {
        this.currentFilters.search = document.getElementById('searchInput').value;
        this.loadBrands(1);
    }

    resetFilters() {
        document.getElementById('searchInput').value = '';
        this.currentFilters = { search: '' };
        this.loadBrands(1);
    }

    openModal() {
        document.getElementById('brandModal').classList.add('active');
    }

    closeModal() {
        document.getElementById('brandModal').classList.remove('active');
    }

    showLoading() {
        const tableBody = document.querySelector('.brands-table tbody');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px;">
                        <div class="loading" style="display: inline-block;"></div>
                        <p style="margin-top: 15px;">Đang tải...</p>
                    </td>
                </tr>
            `;
        }
    }

    hideLoading() {
        // Handled by render functions
    }

    showMessage(message, type = 'info') {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        const messageEl = document.createElement('div');
        messageEl.className = `message ${type}`;
        messageEl.innerHTML = `
            ${message}
            <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; font-size: 20px; color: inherit;">×</button>
        `;
        container.appendChild(messageEl);

        setTimeout(() => {
            messageEl.remove();
        }, 5000);
    }
}

// Initialize when DOM is ready
let brandsManager;
document.addEventListener('DOMContentLoaded', () => {
    brandsManager = new BrandsManager();
});
