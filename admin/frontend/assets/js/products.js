/**
 * Admin Products Management - JavaScript
 * File: /admin/frontend/assets/js/products.js
 */

class ProductsManager {
    constructor() {
        this.apiBase = '/PetsAccessories/admin/backend/api/products';
        this.currentPage = 1;
        this.currentFilters = {
            category_id: '',
            status: '',
            brand_id: '',
            discount: '',
            search: ''
        };
        this.categories = [];
        this.brands = [];
        this.currentProduct = null;
        this.init();
    }

    init() {
        this.loadOptions();
        this.attachEventListeners();
        this.loadProducts();
    }

    attachEventListeners() {
        // Filter
        document.getElementById('filterBtn')?.addEventListener('click', () => this.applyFilters());
        document.getElementById('resetBtn')?.addEventListener('click', () => this.resetFilters());
        
        // Search
        document.getElementById('searchInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.applyFilters();
        });

        // Add Product
        document.getElementById('addProductBtn')?.addEventListener('click', () => this.showAddModal());
        
        // Modal buttons
        document.getElementById('closeModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cancelModalBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('saveProductBtn')?.addEventListener('click', () => this.saveProduct());

        // Preview image when file selected
        document.getElementById('productImageInput')?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const preview = document.getElementById('currentImageContainer');
                    const img = preview.querySelector('img');
                    img.src = event.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Modal backdrop
        document.getElementById('productModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'productModal') this.closeModal();
        });

        // Image upload
        this.setupImageUpload();
    }

    setupImageUpload() {
        const dropZone = document.getElementById('dropZone');
        if (!dropZone) return;

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleImageUpload(files[0]);
            }
        });

        document.getElementById('imageUploadInput')?.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleImageUpload(e.target.files[0]);
            }
        });
    }

    async loadOptions() {
        try {
            const response = await fetch(`${this.apiBase}/get-options.php`);
            const data = await response.json();

            if (data.success) {
                this.categories = data.categories;
                this.brands = data.brands;
                this.populateFilterOptions();
            }
        } catch (error) {
            console.error('Error loading options:', error);
        }
    }

    populateFilterOptions() {
        // Populate category filter
        const categorySelect = document.getElementById('categoryFilter');
        if (categorySelect) {
            categorySelect.innerHTML = '<option value="">-- Tất cả danh mục --</option>';
            this.categories.forEach(cat => {
                if (!cat.parent_id) {
                    const option = document.createElement('option');
                    option.value = cat.category_id;
                    option.textContent = cat.category_name;
                    categorySelect.appendChild(option);
                }
            });
        }

        // Populate brand filter
        const brandSelect = document.getElementById('brandFilter');
        if (brandSelect) {
            brandSelect.innerHTML = '<option value="">-- Tất cả thương hiệu --</option>';
            this.brands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand.brand_id;
                option.textContent = brand.brand_name;
                brandSelect.appendChild(option);
            });
        }

        // Populate category in modal
        const modalCategorySelect = document.getElementById('categoryInput');
        if (modalCategorySelect) {
            modalCategorySelect.innerHTML = '<option value="">-- Chọn danh mục --</option>';
            this.categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.category_id;
                option.textContent = cat.category_name;
                modalCategorySelect.appendChild(option);
            });
        }

        // Populate brand in modal
        const modalBrandSelect = document.getElementById('brandInput');
        if (modalBrandSelect) {
            modalBrandSelect.innerHTML = '<option value="">-- Không chọn --</option>';
            this.brands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand.brand_id;
                option.textContent = brand.brand_name;
                modalBrandSelect.appendChild(option);
            });
        }
    }

    applyFilters() {
        this.currentFilters = {
            category_id: document.getElementById('categoryFilter')?.value || '',
            status: document.getElementById('statusFilter')?.value || '',
            brand_id: document.getElementById('brandFilter')?.value || '',
            discount: document.getElementById('discountFilter')?.value || '',
            search: document.getElementById('searchInput')?.value || ''
        };
        this.currentPage = 1;
        this.loadProducts();
    }

    resetFilters() {
        document.getElementById('categoryFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('brandFilter').value = '';
        document.getElementById('discountFilter').value = '';
        document.getElementById('searchInput').value = '';
        this.currentFilters = {
            category_id: '',
            status: '',
            brand_id: '',
            discount: '',
            search: ''
        };
        this.currentPage = 1;
        this.loadProducts();
    }

    async loadProducts(page = 1) {
        try {
            this.showLoading();
            
            const params = new URLSearchParams({
                page: page,
                limit: 10,
                ...this.currentFilters
            });

            const response = await fetch(`${this.apiBase}/list.php?${params}`);
            const data = await response.json();

            if (data.success) {
                this.renderProducts(data.data);
                this.renderPagination(data.pagination);
                this.currentPage = page;
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi khi tải sản phẩm: ' + error.message);
        }
    }

    renderProducts(products) {
        const tbody = document.querySelector('.products-table tbody');
        if (!tbody) return;

        if (products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" style="text-align: center; padding: 40px;">
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <p>Không có sản phẩm nào</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = products.map(product => `
            <tr>
                <td>
                    <div class="product-thumbnail">
                        <img src="${product.thumbnail ? '/PetsAccessories/admin/backend/uploads/products/' + product.thumbnail : '/PetsAccessories/frontend/public/images/default.jpg'}" 
                             alt="${this.escapeHtml(product.product_name)}"
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                    </div>
                </td>
                <td>
                    <div class="product-name">${this.escapeHtml(product.product_name)}</div>
                    <div class="product-sku">SKU: ${product.sku || '-'}</div>
                </td>
                <td>${product.category_name || '-'}</td>
                <td>${product.brand_name || '-'}</td>
                <td class="price">${this.formatPrice(product.price)}</td>
                <td>${product.discount_price > 0 ? `<span class="discount-price">${this.formatPrice(product.discount_price)}</span>` : '-'}</td>
                <td>
                    <div class="stock-quantity ${this.getStockClass(product.stock_quantity)}">
                        ${product.stock_quantity}
                    </div>
                </td>
                <td>
                    <span class="status-badge ${product.status}">
                        ${product.status_label}
                    </span>
                </td>
                <td>${new Date(product.updated_at).toLocaleDateString('vi-VN')}</td>
                <td>
                    <div class="table-actions">
                        <button class="btn btn-success btn-xs" onclick="productsManager.editProduct(${product.product_id})">
                            ✏️ Sửa
                        </button>
                        <button class="btn btn-danger btn-xs" onclick="productsManager.deleteProduct(${product.product_id})">
                            🗑️ Xóa
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderPagination(pagination) {
        const container = document.getElementById('paginationContainer');
        if (!container) return;

        let html = '';

        if (pagination.current_page > 1) {
            html += `<button onclick="productsManager.loadProducts(1)">« Đầu</button>`;
            html += `<button onclick="productsManager.loadProducts(${pagination.current_page - 1})">‹ Trước</button>`;
        }

        for (let i = 1; i <= pagination.total_pages; i++) {
            if (i === pagination.current_page) {
                html += `<span class="active">${i}</span>`;
            } else {
                html += `<button onclick="productsManager.loadProducts(${i})">${i}</button>`;
            }
        }

        if (pagination.current_page < pagination.total_pages) {
            html += `<button onclick="productsManager.loadProducts(${pagination.current_page + 1})">Sau ›</button>`;
            html += `<button onclick="productsManager.loadProducts(${pagination.total_pages})">Cuối »</button>`;
        }

        container.innerHTML = html;
    }

    showAddModal() {
        this.currentProduct = null;
        document.getElementById('modalTitle').textContent = 'Thêm Sản Phẩm Mới';
        this.resetForm();
        this.showModal();
    }

    async editProduct(productId) {
        try {
            const response = await fetch(`${this.apiBase}/get.php?id=${productId}`);
            const data = await response.json();

            if (data.success) {
                this.currentProduct = data.data;
                document.getElementById('modalTitle').textContent = 'Chỉnh Sửa Sản Phẩm';
                this.populateForm(data.data);
                this.renderProductImages(data.data.images);
                // Hiển thị section ảnh khi edit
                document.getElementById('imagesSection').style.display = 'block';
                this.showModal();
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi khi tải sản phẩm: ' + error.message);
        }
    }

    populateForm(product) {
        document.getElementById('productNameInput').value = product.product_name;
        document.getElementById('categoryInput').value = product.category_id;
        document.getElementById('brandInput').value = product.brand_id || '';
        document.getElementById('skuInput').value = product.sku || '';
        document.getElementById('priceInput').value = product.price;
        document.getElementById('discountPriceInput').value = product.discount_price;
        document.getElementById('stockInput').value = product.stock_quantity;
        document.getElementById('statusInput').value = product.status;
        document.getElementById('descriptionInput').value = product.description || '';
        
        // Show current thumbnail preview
        if (product.thumbnail) {
            const currentImageContainer = document.getElementById('currentImageContainer');
            const currentImage = document.getElementById('currentImage');
            currentImage.src = `/PetsAccessories/admin/backend/uploads/products/${product.thumbnail}`;
            currentImageContainer.style.display = 'block';
        }
    }

    resetForm() {
        document.getElementById('productForm').reset();
        document.getElementById('imageGallery').innerHTML = '';
        document.getElementById('imageUploadInput').value = '';
        document.getElementById('productImageInput').value = '';
        
        // Hide preview
        const currentImageContainer = document.getElementById('currentImageContainer');
        if (currentImageContainer) {
            currentImageContainer.style.display = 'none';
        }
    }

    renderProductImages(images) {
        const gallery = document.getElementById('imageGallery');
        gallery.innerHTML = '';

        images.forEach(image => {
            const item = document.createElement('div');
            item.className = 'image-item';
            item.innerHTML = `
                ${image.is_main ? '<div class="image-main-badge">Ảnh chính</div>' : ''}
                <img src="${image.image_url}" alt="Product image">
                <div class="image-item-overlay">
                    ${!image.is_main ? `<button class="btn btn-sm btn-success" onclick="productsManager.setMainImage(${image.image_id})">★</button>` : ''}
                    <button class="btn btn-sm btn-danger" onclick="productsManager.deleteImage(${image.image_id})">🗑️</button>
                </div>
            `;
            gallery.appendChild(item);
        });
    }

    async handleImageUpload(file) {
        if (!this.currentProduct?.product_id) {
            this.showMessage('warning', 'Vui lòng lưu sản phẩm trước khi upload ảnh');
            return;
        }

        const formData = new FormData();
        formData.append('product_id', this.currentProduct.product_id);
        formData.append('image', file);
        formData.append('is_main', 0);

        try {
            const response = await fetch(`${this.apiBase}/upload-image.php`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', 'Upload ảnh thành công');
                this.loadProductDetail();
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi upload ảnh: ' + error.message);
        }
    }

    async setMainImage(imageId) {
        try {
            const response = await fetch(`${this.apiBase}/set-main-image.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ image_id: imageId })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', data.message);
                this.loadProductDetail();
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi: ' + error.message);
        }
    }

    async deleteImage(imageId) {
        if (!confirm('Bạn chắc chắn muốn xóa ảnh này?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/delete-image.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ image_id: imageId })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', data.message);
                this.loadProductDetail();
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi: ' + error.message);
        }
    }

    async loadProductDetail() {
        if (!this.currentProduct?.product_id) return;
        
        const response = await fetch(`${this.apiBase}/get.php?id=${this.currentProduct.product_id}`);
        const data = await response.json();

        if (data.success) {
            this.currentProduct = data.data;
            this.renderProductImages(data.data.images);
        }
    }

    async saveProduct() {
        const productName = document.getElementById('productNameInput').value;
        const category = document.getElementById('categoryInput').value;
        const brand = document.getElementById('brandInput').value;
        const sku = document.getElementById('skuInput').value;
        const price = document.getElementById('priceInput').value;
        const discountPrice = document.getElementById('discountPriceInput').value;
        const stock = document.getElementById('stockInput').value;
        const status = document.getElementById('statusInput').value;
        const description = document.getElementById('descriptionInput').value;
        const thumbnailInput = document.getElementById('productImageInput');

        if (!this.validateForm(productName, category, price, stock)) {
            return;
        }

        // Check thumbnail requirement
        const isUpdate = !!this.currentProduct;
        if (!isUpdate && !thumbnailInput.files.length) {
            this.showMessage('error', 'Vui lòng chọn hình ảnh sản phẩm');
            return;
        }

        try {
            let thumbnailFilename = '';

            // Upload thumbnail if file selected
            if (thumbnailInput.files.length > 0) {
                const formData = new FormData();
                formData.append('image', thumbnailInput.files[0]);

                const uploadRes = await fetch(`${this.apiBase}/upload-image.php`, {
                    method: 'POST',
                    body: formData
                });
                const uploadData = await uploadRes.json();

                if (!uploadData.success) {
                    throw new Error(uploadData.message);
                }

                thumbnailFilename = uploadData.filename;
            } else {
                // For update without new image, keep old filename
                thumbnailFilename = this.currentProduct.thumbnail;
            }

            const endpoint = isUpdate ? `${this.apiBase}/update.php` : `${this.apiBase}/add.php`;
            const method = 'POST';

            const payload = {
                product_name: productName,
                category_id: category,
                brand_id: brand || null,
                sku: sku || null,
                price: parseFloat(price),
                discount_price: parseFloat(discountPrice) || 0,
                stock_quantity: parseInt(stock),
                status: status,
                description: description,
                thumbnail: thumbnailFilename
            };

            if (isUpdate) {
                payload.product_id = this.currentProduct.product_id;
            }

            const response = await fetch(endpoint, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', data.message);
                this.closeModal();
                this.loadProducts(this.currentPage);
            } else {
                if (data.errors && Array.isArray(data.errors)) {
                    this.showMessage('error', data.errors.join(', '));
                } else {
                    this.showMessage('error', data.message);
                }
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi khi lưu sản phẩm: ' + error.message);
        }
    }

    validateForm(productName, category, price, stock) {
        if (!productName.trim()) {
            this.showMessage('error', 'Tên sản phẩm không được để trống');
            return false;
        }

        if (!category) {
            this.showMessage('error', 'Danh mục sản phẩm không được để trống');
            return false;
        }

        if (!price || parseFloat(price) <= 0) {
            this.showMessage('error', 'Giá sản phẩm phải lớn hơn 0');
            return false;
        }

        if (stock === '' || parseInt(stock) < 0) {
            this.showMessage('error', 'Tồn kho phải là số không âm');
            return false;
        }

        return true;
    }

    async deleteProduct(productId) {
        if (!confirm('Bạn chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/delete.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ product_id: productId })
            });

            const data = await response.json();

            if (data.success) {
                this.showMessage('success', data.message);
                this.loadProducts(this.currentPage);
            } else {
                this.showMessage('error', data.message);
            }
        } catch (error) {
            this.showMessage('error', 'Lỗi khi xóa sản phẩm: ' + error.message);
        }
    }

    showModal() {
        document.getElementById('productModal').classList.add('active');
    }

    closeModal() {
        document.getElementById('productModal').classList.remove('active');
        this.resetForm();
        this.currentProduct = null;
        // Ẩn section ảnh
        document.getElementById('imagesSection').style.display = 'none';
    }

    showLoading() {
        const tbody = document.querySelector('.products-table tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center;">
                        <div class="loading">
                            <div class="spinner"></div>
                            <p>Đang tải dữ liệu...</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    showMessage(type, message) {
        const container = document.getElementById('messagesContainer');
        if (!container) return;

        const messageEl = document.createElement('div');
        messageEl.className = `message ${type}`;
        messageEl.innerHTML = `
            <span>${message}</span>
            <button class="message-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        container.appendChild(messageEl);

        setTimeout(() => messageEl.remove(), 5000);
    }

    formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }

    getStockClass(stock) {
        if (stock === 0) return 'warning';
        if (stock <= 10) return 'low';
        return 'high';
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.productsManager = new ProductsManager();
});
