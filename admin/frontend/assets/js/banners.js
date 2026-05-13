/**
 * Quản lý Banners
 * File: /admin/frontend/assets/js/banners.js
 */

const bannerManager = {
    // Cấu hình URL API (bạn cần tạo file PHP này ở backend)
    apiBaseUrl: '/PetsAccessories/admin/backend/api/banners',

    init() {
        this.cacheDOM();
        this.bindEvents();
        this.loadBanners();
    },

    cacheDOM() {
        this.tableBody = document.querySelector('#bannersTable tbody');
        this.modal = document.getElementById('bannerModal');
        this.form = document.getElementById('bannerForm');
        this.addBtn = document.getElementById('addBannerBtn');
        this.closeBtn = document.getElementById('closeBannerModalBtn');
        this.cancelBtn = document.getElementById('cancelBannerBtn');
        this.saveBtn = document.getElementById('saveBannerBtn');
        this.messagesContainer = document.getElementById('messagesContainer');
        
        // Form inputs
        this.modalTitle = document.getElementById('modalTitle');
        this.idInput = document.getElementById('bannerId');
        this.titleInput = document.getElementById('bannerTitle');
        this.imageInput = document.getElementById('bannerImage');
        this.linkInput = document.getElementById('bannerLink');
        this.statusSelect = document.getElementById('bannerStatus');
        this.currentImageContainer = document.getElementById('currentImageContainer');
        this.currentImage = document.getElementById('currentImage');
    },

    bindEvents() {
        this.addBtn.addEventListener('click', () => this.openModal());
        this.closeBtn.addEventListener('click', () => this.closeModal());
        this.cancelBtn.addEventListener('click', () => this.closeModal());
        this.saveBtn.addEventListener('click', () => this.saveBanner());
        
        // Preview image when file selected
        this.imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.currentImage.src = event.target.result;
                    this.currentImageContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Đóng modal khi click ra ngoài
        window.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeModal();
        });
    },

    async loadBanners() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/list.php`);
            const data = await response.json();

            if (data.success) {
                this.renderTable(data.banners);
            } else {
                this.showMessage('Lỗi khi tải dữ liệu: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Lỗi:', error);
            this.showMessage('Lỗi kết nối máy chủ', 'error');
        }
    },

    renderTable(banners) {
        if (!banners || banners.length === 0) {
            this.tableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px;">Chưa có banner nào trong hệ thống.</td>
                </tr>`;
            return;
        }

        this.tableBody.innerHTML = banners.map(banner => `
            <tr>
                <td>#${banner.id}</td>
                <td>
                    <img src="/PetsAccessories/admin/backend/uploads/banners/${banner.image}" alt="${banner.title}" class="banner-img-preview">
                </td>
                <td><strong>${banner.title}</strong></td>
                <td>${banner.link ? `<a href="${banner.link}" target="_blank">Xem Link</a>` : 'Không có'}</td>
                <td>
                    <span class="status-badge ${banner.status == 1 ? 'status-completed' : 'status-cancelled'}">
                        ${banner.status == 1 ? 'Đang hiển thị' : 'Đang ẩn'}
                    </span>
                </td>
                <td style="text-align: center;">
                    <div class="actions-cell" style="justify-content: center;">
                        <button class="action-btn edit" onclick="bannerManager.editBanner(${banner.id})">✏️ Sửa</button>
                        <button class="action-btn delete" onclick="bannerManager.deleteBanner(${banner.id})">🗑️ Xóa</button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    openModal(banner = null) {
        this.form.reset();
        
        if (banner) {
            this.modalTitle.textContent = '📝 Sửa Banner';
            this.idInput.value = banner.id;
            this.titleInput.value = banner.title;
            this.linkInput.value = banner.link;
            this.statusSelect.value = banner.status;
            
            // Validate Image requirement
            this.imageInput.required = false; 
            
            // Hiện ảnh cũ
            this.currentImage.src = `/PetsAccessories/admin/backend/uploads/banners/${banner.image}`;
            this.currentImageContainer.style.display = 'block';
        } else {
            this.modalTitle.textContent = '➕ Thêm Banner';
            this.idInput.value = '';
            this.imageInput.required = true; // Bắt buộc phải có ảnh khi thêm mới
            this.currentImageContainer.style.display = 'none';
        }
        
        this.modal.classList.add('show');
    },

    closeModal() {
        this.modal.classList.remove('show');
        this.form.reset();
        this.imageInput.value = '';
        this.currentImageContainer.style.display = 'none';
    },

    async editBanner(id) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/get.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                this.openModal(data.banner);
            } else {
                this.showMessage(data.message, 'error');
            }
        } catch (error) {
            this.showMessage('Lỗi kết nối máy chủ', 'error');
        }
    },

    async saveBanner() {
        if (!this.titleInput.value.trim()) {
            this.showMessage('Tiêu đề banner không được để trống', 'error');
            return;
        }

        // Check image requirement
        const isUpdate = this.idInput.value !== '';
        if (!isUpdate && !this.imageInput.files.length) {
            this.showMessage('Vui lòng chọn hình ảnh', 'error');
            return;
        }

        // Disable button
        this.saveBtn.disabled = true;
        this.saveBtn.textContent = 'Đang lưu...';

        try {
            let imageFilename = '';

            // Upload image if file selected
            if (this.imageInput.files.length > 0) {
                const formData = new FormData();
                formData.append('image', this.imageInput.files[0]);

                const uploadRes = await fetch(`${this.apiBaseUrl}/upload-image.php`, {
                    method: 'POST',
                    body: formData
                });
                const uploadData = await uploadRes.json();

                if (!uploadData.success) {
                    throw new Error(uploadData.message);
                }

                imageFilename = uploadData.filename;
            } else {
                // For update without new image, keep old filename
                // Extract filename from image URL stored in data
                const urlParts = this.currentImage.src.split('/');
                imageFilename = urlParts[urlParts.length - 1];
                
                // If URL is relative path, make sure we get the filename
                if (imageFilename.includes('?')) {
                    imageFilename = imageFilename.split('?')[0];
                }
            }

            // Save banner data
            const endpoint = isUpdate ? '/update.php' : '/create.php';
            const saveData = {
                title: this.titleInput.value.trim(),
                image_url: imageFilename,
                link_url: this.linkInput.value.trim(),
                status: parseInt(this.statusSelect.value)
            };

            if (isUpdate) {
                saveData.id = parseInt(this.idInput.value);
            }

            const response = await fetch(`${this.apiBaseUrl}${endpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(saveData)
            });
            const data = await response.json();

            if (data.success) {
                this.showMessage(data.message, 'success');
                this.closeModal();
                this.loadBanners(); // Reload table
            } else {
                this.showMessage(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showMessage('Lỗi: ' + error.message, 'error');
        } finally {
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = '💾 Lưu Banner';
        }
    },

    async deleteBanner(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa banner này? Thao tác này không thể hoàn tác.')) return;

        try {
            const formData = new FormData();
            formData.append('id', id);

            const response = await fetch(`${this.apiBaseUrl}/delete.php`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                this.showMessage('Xóa banner thành công!', 'success');
                this.loadBanners();
            } else {
                this.showMessage(data.message, 'error');
            }
        } catch (error) {
            this.showMessage('Lỗi khi xóa banner', 'error');
        }
    },

    showMessage(message, type = 'info') {
        const div = document.createElement('div');
        div.className = `message ${type}`;
        div.innerHTML = `
            <span>${message}</span>
            <button style="background:none; border:none; cursor:pointer;" onclick="this.parentElement.remove()">×</button>
        `;
        this.messagesContainer.appendChild(div);
        
        // Tự động ẩn sau 4 giây
        setTimeout(() => {
            if (div.parentElement) div.remove();
        }, 4000);
    }
};

// Khởi tạo
document.addEventListener('DOMContentLoaded', () => {
    bannerManager.init();
});