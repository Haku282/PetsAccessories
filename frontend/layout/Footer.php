<?php
// frontend/components/footer.php
?>
<footer>
    <div class="footer-content">
        <div class="about-us">
            <h4>Về PetsAccessories</h4>
            <p>Cửa hàng phụ kiện thú cưng uy tín hàng đầu.</p>
        </div>
        <div class="contact-info">
            <h4>Liên hệ</h4>
            <p>Email: contact@petsaccessories.com</p>
            <p>Phone: 1900 xxxx</p>
        </div>
    </div>
    <div class="copyright">
        &copy; <?php echo date('Y'); ?> PetsAccessories. All rights reserved.
    </div>
</footer>

<script>
function showToast(message, isError = false) {
    const toast = document.createElement('div');
    toast.textContent = message;
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        backgroundColor: isError ? '#e74c3c' : '#2ecc71',
        color: '#fff',
        padding: '12px 20px',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '9999',
        fontSize: '15px',
        fontWeight: 'bold',
        opacity: '0',
        transition: 'opacity 0.3s, transform 0.3s',
        transform: 'translateY(-20px)'
    });
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    // Animate out and remove after 3s
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function addToCart(btn) {
    const productId = btn.getAttribute('data-id');
    if (!productId) return showToast('Lỗi: Không tìm thấy ID sản phẩm.', true);

    // Vô hiệu hóa nút trong khi chờ
    const originalText = btn.innerText;
    btn.innerText = 'Đang thêm...';
    btn.disabled = true;

    fetch('/PetsAccessories/backend/src/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng trên icon Header
            const badge = document.getElementById('cart-count-badge');
            if (badge) {
                badge.innerText = data.cartCount;
            }
            showToast(data.message);
        } else {
            showToast('Lỗi: ' + data.message, true);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra kết nối Server.', true);
    })
    .finally(() => {
        // Phục hồi nút
        btn.innerText = originalText;
        btn.disabled = false;
    });
}

function toggleWishlist(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', 'toggle');

    fetch('/PetsAccessories/backend/src/wishlist_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            showToast(data.message);
        } else {
            showToast(data.message, true);
            if(data.message.includes('đăng nhập')) {
                setTimeout(() => {
                    window.location.href = '/PetsAccessories/frontend/public/index.php?page=login';
                }, 1500);
            }
        }
    })
    .catch(err => {
        showToast('Có lỗi xảy ra.', true);
        console.error(err);
    });
}
</script>
