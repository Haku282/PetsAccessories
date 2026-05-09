/**
 * Admin Categories Management - JavaScript
 * File: /admin/frontend/assets/js/categories.js
 */

class CategoriesManager {
    constructor() {
        this.apiBase = '/PetsAccessories/admin/backend/api/categories';
        this.currentPage = 1;
        this.currentFilters = {
            search: '',
            parent_id: '',
            status: ''
        };
        this.parentCategories = [];
        this.currentCategory = null;
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.loadCategories();
        this.loadOptions();
    }

    attachEventListeners() {
        // Search & Filter
        document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.applyFilters();
        });
        document.getElementById('filterBtn')?.addEventListener('click', () => this.applyFilters());
        document.getElementById('resetBtn')?.addEventListener('click', () => this.resetFilters());

        // Add Category
        document.getElementById('addCategoryBtn')?.addEventListener('click', () => this.showAddModal());

        // Modal
        document.getElementById('closeModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cancelModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('saveCategoryBtn')?.addEventListener('click', () => this.saveCategory());

        // Modal backdrop
        document.getElementById('categoryModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'categoryModal') this.closeModal();
        });
    }

    loadOptions() {
        fetch(`${this.apiBase}/get-options.php`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.parentCategories = data.parent_categories;
                    this.populateParentSelect();
                }
            })
            .catch(err => console.error('Error loading options:', err));
    }

    populateParentSelect() {
        const parentSelect = document.getElementById('parentCategorySelect');
        if (!parentSelect) return;

        let html = '<option value="">-- Danh mục gốc --</option>';
        this.parentCategories.forEach(cat => {
            html += `<option value="${cat.category_id}">${cat.category_name}</option>`;
        });
        parentSelect.innerHTML = html;
    }

    loadCategories(page = 1) {
        const params = new URLSearchParams({
            page: page,
            search: this.currentFilters.search,
            parent_id: this.currentFilters.parent_id,
            status: this.currentFilters.status
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

    renderTable(categories) {
        const tableBody = document.querySelector('.categories-table tbody');
        if (!tableBody) return;

        if (categories.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="empty-state">
                            <div class="empty-state-icon">📁</div>
                            <h3>Không có danh mục</h3>
                            <p>Hãy thêm danh mục đầu tiên</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = categories.map(cat => `
            <tr>
                <td>${cat.category_id}</td>
                <td>
                    ${cat.parent_id ? `<span class="category-indent">${cat.category_name}</span>` : `<strong>${cat.category_name}</strong>`}
                </td>
                <td>${cat.parent_name || '-'}</td>
                <td>
                    <span class="pet-type-badge pet-type-${cat.pet_type}">
                        ${this.getPetTypeLabel(cat.pet_type)}
                    </span>
                </td>
                <td>
                    <span class="status-badge status-${cat.status === 1 ? 'active' : 'inactive'}">
                        ${cat.status_label}
                    </span>
                </td>
                <td>
                    <div class="actions-cell">
                        <button class="action-btn edit" onclick="categoriesManager.editCategory(${cat.category_id})">✏️ Sửa</button>
                        <button class="action-btn delete" onclick="categoriesManager.deleteCategory(${cat.category_id})">🗑️ Xóa</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    getPetTypeLabel(petType) {
        const labels = {
            'dog': '🐕 Chó',
            'cat': '🐱 Mèo',
            'all': '🐾 Tất cả'
        };
        return labels[petType] || petType;
    }

    renderPagination(pagination) {
        const paginationContainer = document.querySelector('.pagination-container');
        if (!paginationContainer) return;

        let html = '<div class="pagination">';

        if (pagination.page > 1) {
            html += `<button onclick="categoriesManager.loadCategories(1)">⏮️</button>`;
            html += `<button onclick="categoriesManager.loadCategories(${pagination.page - 1})">◀</button>`;
        }

        for (let i = 1; i <= pagination.pages; i++) {
            if (i === pagination.page) {
                html += `<button class="active">${i}</button>`;
            } else if (i <= 5 || i === pagination.pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
                html += `<button onclick="categoriesManager.loadCategories(${i})">${i}</button>`;
            } else if (i === 6 || i === pagination.pages - 1) {
                html += `<span>...</span>`;
            }
        }

        if (pagination.page < pagination.pages) {
            html += `<button onclick="categoriesManager.loadCategories(${pagination.page + 1})">▶</button>`;
            html += `<button onclick="categoriesManager.loadCategories(${pagination.pages})">⏭️</button>`;
        }

        html += '</div>';
        paginationContainer.innerHTML = html;
    }

    showAddModal() {
        this.currentCategory = null;
        document.getElementById('categoryModalTitle').textContent = '➕ Thêm Danh Mục';
        document.getElementById('categoryForm').reset();
        this.openModal();
    }

    editCategory(id) {
        fetch(`${this.apiBase}/get.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.currentCategory = data.data;
                    this.populateForm(data.data);
                    document.getElementById('categoryModalTitle').textContent = '✏️ Chỉnh Sửa Danh Mục';
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

    populateForm(category) {
        document.getElementById('categoryNameInput').value = category.category_name;
        document.getElementById('parentCategorySelect').value = category.parent_id || '';
        document.getElementById('petTypeSelect').value = category.pet_type;
        document.getElementById('statusSelect').value = category.status;
    }

    saveCategory() {
        const formData = {
            category_name: document.getElementById('categoryNameInput').value,
            parent_id: document.getElementById('parentCategorySelect').value,
            pet_type: document.getElementById('petTypeSelect').value,
            status: document.getElementById('statusSelect').value
        };

        if (!formData.category_name.trim()) {
            this.showMessage('Tên danh mục không được để trống', 'error');
            return;
        }

        const url = this.currentCategory ? `${this.apiBase}/update.php` : `${this.apiBase}/add.php`;
        const method = this.currentCategory ? 'PUT' : 'POST';

        if (this.currentCategory) {
            formData.category_id = this.currentCategory.category_id;
        }

        const submitBtn = document.getElementById('saveCategoryBtn');
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
                    this.loadCategories(this.currentPage);
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

    deleteCategory(id) {
        if (!confirm('Bạn chắc chắn muốn xóa danh mục này?')) return;

        fetch(`${this.apiBase}/delete.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ category_id: id })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showMessage(data.message, 'success');
                    this.loadCategories(this.currentPage);
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
        this.currentFilters.parent_id = document.getElementById('parentFilter').value;
        this.currentFilters.status = document.getElementById('statusFilter').value;
        this.loadCategories(1);
    }

    resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('parentFilter').value = '';
        document.getElementById('statusFilter').value = '';
        this.currentFilters = { search: '', parent_id: '', status: '' };
        this.loadCategories(1);
    }

    openModal() {
        document.getElementById('categoryModal').classList.add('active');
    }

    closeModal() {
        document.getElementById('categoryModal').classList.remove('active');
    }

    showLoading() {
        const tableBody = document.querySelector('.categories-table tbody');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">
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
let categoriesManager;
document.addEventListener('DOMContentLoaded', () => {
    categoriesManager = new CategoriesManager();
});
