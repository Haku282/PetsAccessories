<?php
require_once __DIR__ . '/../../backend/src/product_detail.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../layout/header.php'; ?>

<main class="product-detail">
    <style>
        .product-detail-wrapper {
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Inter', Arial, sans-serif;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .pd-breadcrumb {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .pd-breadcrumb a { color: #0b59d6; text-decoration: none; }
        
        .pd-main {
            display: flex;
            gap: 40px;
            margin-bottom: 40px;
        }
        .pd-gallery {
            flex: 0 0 45%;
        }
        .pd-main-img {
            width: 100%;
            height: 450px;
            border-radius: 8px;
            border: 1px solid #ebebeb;
            object-fit: contain;
            padding: 10px;
        }
        .pd-thumbnails {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            overflow-x: auto;
        }
        .pd-thumb {
            width: 80px;
            height: 80px;
            border: 1px solid #ebebeb;
            border-radius: 4px;
            cursor: pointer;
            object-fit: cover;
            padding: 5px;
        }
        .pd-thumb:hover, .pd-thumb.active { border-color: #0b59d6; }

        .pd-info { flex: 1; }
        .pd-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin: 0 0 10px 0;
            line-height: 1.4;
        }
        .pd-rating {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }
        .pd-stars { color: #ffb800; font-size: 16px; }
        .pd-stock { color: #4caf50; font-weight: 500; display: flex; align-items: center; gap: 5px;}

        .pd-price-box {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .pd-price-final { font-size: 32px; font-weight: 700; color: #0b59d6; }
        .pd-price-old { font-size: 18px; color: #9e9e9e; text-decoration: line-through; }
        .pd-discount-badge {
            background: #ffebee; color: #f44336; padding: 4px 8px; border-radius: 4px; font-size: 13px; font-weight: bold;
        }

        .pd-meta {
            display: flex; gap: 30px; font-size: 14px; color: #555; margin-bottom: 20px;
        }
        
        .pd-short-desc {
            font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 20px;
        }

        .pd-actions {
            display: flex; align-items: center; gap: 20px; margin-bottom: 30px;
        }
        .pd-qty {
            display: flex; align-items: center; border: 1px solid #ddd; border-radius: 8px; height: 48px; overflow: hidden;
        }
        .pd-qty button {
            width: 40px; height: 100%; background: #fff; border: none; cursor: pointer; font-size: 20px; color: #666;
        }
        .pd-qty button:hover { background: #f0f0f0; }
        .pd-qty input {
            width: 50px; height: 100%; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; text-align: center; font-size: 16px; font-weight: bold; -moz-appearance: textfield; appearance: textfield;
        }
        .pd-qty input::-webkit-outer-spin-button, .pd-qty input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        .pd-add-to-cart {
            flex: 1; background: #0b59d6; color: white; border: none; border-radius: 8px; height: 48px; font-size: 16px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: background 0.2s;
        }
        .pd-add-to-cart:hover { background: #0947aa; }
        .pd-wishlist {
            width: 48px; height: 48px; border: 1px solid #ddd; border-radius: 8px; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #666; transition: 0.2s;
        }
        .pd-wishlist:hover { color: #f44336; border-color: #f44336; }

        .pd-benefits {
            background: #f8fcfd; border: 1px solid #e0f2f7; border-radius: 8px; padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
        }
        .pd-benefit-item { display: flex; align-items: flex-start; gap: 12px; font-size: 13px; }
        .pd-benefit-icon { font-size: 24px; color: #0b59d6; }
        .pd-benefit-title { font-weight: 600; color: #333; margin-bottom: 2px; font-size: 14px;}
        .pd-benefit-desc { color: #666; }

        .pd-nav-tabs {
            border-bottom: 1px solid #eee; display: flex; gap: 40px; margin-bottom: 30px;
        }
        .pd-nav-tab {
            padding: 15px 0; font-size: 16px; font-weight: 600; color: #666; cursor: pointer; border-bottom: 3px solid transparent; transition: 0.2s;
        }
        .pd-nav-tab.active { color: #0b59d6; border-bottom-color: #0b59d6; }
        
        .pd-tab-panel { display: none; font-size: 15px; line-height: 1.8; color: #444; }
        .pd-tab-panel.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .pd-review-box {
            background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-bottom: 20px;
        }
        .pd-review-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .pd-review-author { font-weight: bold; color: #333; }
        .pd-review-date { color: #999; font-size: 13px; }

        .pd-related-title {
            font-size: 22px; font-weight: bold; margin: 40px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #0b59d6; display: inline-block;
        }
    </style>
    
    <?php if (!empty($error)): ?>
        <div class="product-detail__error" style="color: red; text-align: center; padding: 50px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <div class="product-detail-wrapper">
            <div class="pd-breadcrumb">
                <a href="/PetsAccessories/frontend/public/index.php">Trang chủ</a> &rsaquo; 
                <span>Chi tiết sản phẩm</span>
            </div>

            <div class="pd-main">
                <div class="pd-gallery">
                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>" class="pd-main-img" id="mainImage">
                    <div class="pd-thumbnails">
                        <img src="<?php echo htmlspecialchars($thumbnail); ?>" class="pd-thumb active" onclick="document.getElementById('mainImage').src=this.src;">
                    </div>
                </div>

                <div class="pd-info">
                    <h1 class="pd-title"><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></h1>
                    
                    <div class="pd-rating">
                        <div class="pd-stars">
                            <?php 
                            $avg = isset($ratingStats['avg_rating']) ? round($ratingStats['avg_rating']) : 5;
                            for($i=1; $i<=5; $i++) echo $i <= $avg ? '★' : '☆';
                            ?>
                        </div>
                        <span style="color: #ff9800; font-weight: bold;"><?php echo isset($ratingStats['avg_rating']) ? number_format($ratingStats['avg_rating'], 1) : '5.0'; ?></span>
                        <span>(<?php echo isset($ratingStats['total_reviews']) ? $ratingStats['total_reviews'] : 0; ?> đánh giá)</span>
                        <span style="color: #ddd;">|</span>
                        <div class="pd-stock">
                            <span style="font-size: 16px;">✓</span> Còn lại <?php echo $stockQuantity; ?> sản phẩm
                        </div>
                    </div>

                    <div class="pd-price-box">
                        <span class="pd-price-final"><?php echo number_format($finalPrice, 0, ',', '.'); ?>đ</span>
                        <?php if ($discountPrice > 0 && $discountPrice < $price): ?>
                            <span class="pd-price-old"><?php echo number_format($price, 0, ',', '.'); ?>đ</span>
                            <span class="pd-discount-badge">-<?php echo round((($price - $discountPrice) / $price) * 100); ?>%</span>
                        <?php endif; ?>
                    </div>

                    <div class="pd-meta">
                        <div>Thương hiệu: <strong>Đang cập nhật</strong></div>
                        <div>Xuất xứ: <strong>Việt Nam</strong></div>
                    </div>

                    <div class="pd-short-desc">
                        Sản phẩm chất lượng cao, cung cấp dinh dưỡng hoặc phụ kiện tốt nhất cho thú cưng của bạn. 
                    </div>

                    <div class="pd-actions">
                        <div class="pd-qty">
                            <button type="button" onclick="document.getElementById('qtyInput').stepDown()">−</button>
                            <input type="number" id="qtyInput" value="1" min="1" max="<?php echo $stockQuantity; ?>">
                            <button type="button" onclick="document.getElementById('qtyInput').stepUp()">+</button>
                        </div>
                        <button type="button" class="pd-add-to-cart" data-id="<?php echo (int) ($product['product_id'] ?? 0); ?>" onclick="addDetailToCart(this)">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Thêm vào giỏ hàng
                        </button>
                        <button type="button" class="pd-wishlist" id="wishlistBtn" data-id="<?php echo isset($productId) && $productId ? (int)$productId : (isset($product['product_id']) ? (int)$product['product_id'] : 0); ?>" data-wishlist="<?php echo $isInWishlist ? '1' : '0'; ?>" onclick="toggleProductDetailWishlist(this)">♥</button>
                    </div>

                    <div class="pd-benefits">
                        <div class="pd-benefit-item">
                            <div class="pd-benefit-icon">🚚</div>
                            <div>
                                <div class="pd-benefit-title">Giao hỏa tốc 2H</div>
                                <div class="pd-benefit-desc">Nội thành Tp.HCM</div>
                            </div>
                        </div>
                        <div class="pd-benefit-item">
                            <div class="pd-benefit-icon">⭐</div>
                            <div>
                                <div class="pd-benefit-title">Tích điểm nhận quà</div>
                                <div class="pd-benefit-desc">Voucher hấp dẫn</div>
                            </div>
                        </div>
                        <div class="pd-benefit-item">
                            <div class="pd-benefit-icon">🔄</div>
                            <div>
                                <div class="pd-benefit-title">Đổi trả miễn phí</div>
                                <div class="pd-benefit-desc">Trong 7 ngày</div>
                            </div>
                        </div>
                        <div class="pd-benefit-item">
                            <div class="pd-benefit-icon">🛡️</div>
                            <div>
                                <div class="pd-benefit-title">100% Chính hãng</div>
                                <div class="pd-benefit-desc">Cam kết chất lượng</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="pd-nav-tabs">
                <div class="pd-nav-tab active" onclick="switchTab(0)">Mô tả sản phẩm</div>
                <div class="pd-nav-tab" onclick="switchTab(1)">Thông số kỹ thuật</div>
                <div class="pd-nav-tab" onclick="switchTab(2)">Đánh giá & Nhận xét</div>
            </div>

            <div class="pd-tab-panel active" id="tab-0">
                <h3>Chi tiết mô tả</h3>
                <p><?php echo !empty($description) ? nl2br(htmlspecialchars($description)) : 'Hiện chưa có mô tả.'; ?></p>
            </div>

            <div class="pd-tab-panel" id="tab-1">
                <h3>Thông số chi tiết</h3>
                <?php if (!empty($specItems)): ?>
                    <ul style="padding-left: 20px;">
                        <?php foreach ($specItems as $item): ?>
                            <li style="margin-bottom: 8px;"><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Hiện chưa có thông số kỹ thuật.</p>
                <?php endif; ?>
            </div>

            <div class="pd-tab-panel" id="tab-2">
                <h3>Đánh giá từ người mua</h3>
                <div style="display: flex; gap: 30px; margin-bottom: 30px; align-items: center; background: #fafafa; padding: 20px; border-radius: 8px;">
                    <div style="text-align: center;">
                        <div style="font-size: 36px; font-weight: bold; color: #ff9800;"><?php echo isset($ratingStats['avg_rating']) ? number_format($ratingStats['avg_rating'], 1) : '5.0'; ?></div>
                        <div style="color: #ff9800; font-size: 20px;">★★★★★</div>
                        <div style="color: #666; font-size: 14px; margin-top: 5px;"><?php echo isset($ratingStats['total_reviews']) ? $ratingStats['total_reviews'] : 0; ?> đánh giá</div>
                    </div>
                    <?php if (isset($canReview) && $canReview): ?>
                        <div style="flex: 1;">
                            <form id="submitReviewForm" onsubmit="submitReview(event)" style="display: flex; gap: 10px; flex-direction: column;">
                                <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <label>Đánh giá:</label>
                                    <select name="rating" required style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                                        <option value="5">5 Sao</option><option value="4">4 Sao</option><option value="3">3 Sao</option><option value="2">2 Sao</option><option value="1">1 Sao</option>
                                    </select>
                                </div>
                                <textarea name="comment" rows="2" placeholder="Nhập bình luận của bạn..." required style="padding: 10px; border-radius: 4px; border: 1px solid #ddd; resize: none;"></textarea>
                                <button type="submit" style="background: #0b59d6; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; align-self: flex-start;">Gửi đánh giá</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($reviews) && is_array($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="pd-review-box">
                            <div class="pd-review-header">
                                <div class="pd-review-author"><?php echo htmlspecialchars($review['fullname'] ?? 'Khách hàng'); ?>
                                    <span style="color: #ff9800; margin-left: 10px;">
                                    <?php for ($i=0; $i<5; $i++) echo $i < (int)($review['rating'] ?? 5) ? '★' : '☆'; ?>
                                    </span>
                                </div>
                                <div class="pd-review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></div>
                            </div>
                            <p style="margin: 0; color: #444;"><?php echo htmlspecialchars($review['comment'] ?? ''); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666; font-style: italic;">Chưa có đánh giá nào cho sản phẩm này.</p>
                <?php endif; ?>
            </div>

            <!-- Related Products -->
            <?php if (!empty($relatedProducts) && is_array($relatedProducts)): ?>
            <h2 class="pd-related-title">Sản phẩm liên quan</h2>
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
                <?php foreach ($relatedProducts as $relProd): ?>
                    <div class="product-card" style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; background: #fff; transition: box-shadow 0.2s;">
                        <a href="?id=<?php echo (int) $relProd['product_id']; ?>" style="display: block; padding: 10px; text-align: center;">
                            <img src="<?php echo htmlspecialchars((string) ($relProd['thumbnail_resolved'] ?? $relProd['thumbnail'])); ?>" style="width: 100%; height: 160px; object-fit: contain;">
                        </a>
                        <div style="padding: 15px;">
                            <a href="?id=<?php echo (int) $relProd['product_id']; ?>" style="text-decoration: none; color: #333; font-weight: 500; display: block; margin-bottom: 10px; font-size: 14px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                <?php echo htmlspecialchars($relProd['product_name']); ?>
                            </a>
                            <div style="color: #ff4d4f; font-weight: bold; font-size: 16px; margin-bottom: 10px;">
                                <?php echo number_format((float) ($relProd['discount_price'] > 0 ? $relProd['discount_price'] : $relProd['price']), 0, ',', '.'); ?>đ
                            </div>
                            <button type="button" data-id="<?php echo (int) $relProd['product_id']; ?>" onclick="addToCart(this)" style="width: 100%; padding: 8px; background: #f0f0f0; border: none; border-radius: 4px; cursor: pointer; color: #0b59d6; font-weight: bold; font-size: 13px;">Thêm vào giỏ</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>

    <?php endif; ?>

    <script>
    function switchTab(index) {
        document.querySelectorAll('.pd-nav-tab').forEach((t, i) => t.classList.toggle('active', i === index));
        document.querySelectorAll('.pd-tab-panel').forEach((p, i) => p.classList.toggle('active', i === index));
    }

    function addDetailToCart(btn) {
        // Mock add to cart logic (pass qty to actual function if supported)
        const id = btn.getAttribute('data-id');
        const qty = document.getElementById('qtyInput').value;
        const formData = new FormData();
        formData.append('product_id', id);
        formData.append('quantity', qty);
        
        fetch('/PetsAccessories/backend/src/add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('Đã thêm sản phẩm vào giỏ hàng!');
                } else {
                    alert('Đã thêm sản phẩm vào giỏ hàng!');
                }
                // Update header cart count
                const countBadge = document.querySelector('.header-cart .cart-count');
                if (countBadge) countBadge.textContent = data.cartCount || (parseInt(countBadge.textContent||0) + parseInt(qty));
            } else {
                if (typeof showToast === 'function') {
                    showToast('Có lỗi xảy ra: ' + data.message, true);
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof showToast === 'function') {
                showToast('Có lỗi xảy ra kết nối Server.', true);
            } else {
                alert('Đã thêm sản phẩm vào giỏ hàng.');
            }
            // location.reload();
        });
    }

    function submitReview(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        fetch('/PetsAccessories/backend/src/submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if(data.status === 'success') {
                location.reload();
            }
        })
        .catch(err => {
            alert('Có lỗi xảy ra.');
        });
    }

    function toggleProductDetailWishlist(btn) {
        // Get product ID from button's data attribute
        const productId = btn.getAttribute('data-id');
        console.log('toggleWishlist called with btn:', btn);
        console.log('Product ID attribute value:', productId);
        console.log('Product ID type:', typeof productId);
        
        if (!productId || productId === '' || productId === '0' || productId === 'null' || productId === '[object HTMLButtonElement]') {
            alert('Không thể xác định sản phẩm. Vui lòng tải lại trang.');
            console.error('Invalid product ID:', productId);
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'toggle');
        formData.append('product_id', String(productId).trim());
        
        console.log('Sending wishlist request with product_id:', String(productId).trim());
        
        fetch('/PetsAccessories/backend/src/wishlist_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    if (data.action === 'added') {
                        btn.style.color = '#f44336';
                        btn.style.borderColor = '#f44336';
                        btn.setAttribute('data-wishlist', '1');
                        if (typeof showToast === 'function') {
                            showToast('Đã thêm vào danh sách yêu thích!');
                        } else {
                            alert('Đã thêm vào danh sách yêu thích!');
                        }
                    } else {
                        btn.style.color = '#666';
                        btn.style.borderColor = '#ddd';
                        btn.setAttribute('data-wishlist', '0');
                        if (typeof showToast === 'function') {
                            showToast('Đã xóa khỏi danh sách yêu thích!');
                        } else {
                            alert('Đã xóa khỏi danh sách yêu thích!');
                        }
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Có lỗi xảy ra', true);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                }
            } catch(e) {
                console.error('JSON parse error:', e);
                alert('Lỗi: ' + text);
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            if (typeof showToast === 'function') {
                showToast('Có lỗi xảy ra kết nối Server.', true);
            } else {
                alert('Có lỗi xảy ra kết nối Server.');
            }
        });
    }

    // Initialize wishlist button state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const wishlistBtn = document.getElementById('wishlistBtn');
        console.log('Initializing wishlist button:', wishlistBtn);
        if (wishlistBtn) {
            const dataId = wishlistBtn.getAttribute('data-id');
            const isInWishlist = wishlistBtn.getAttribute('data-wishlist') === '1';
            console.log('Button data-id:', dataId, 'Is in wishlist:', isInWishlist);
            
            if (isInWishlist) {
                wishlistBtn.style.color = '#f44336';
                wishlistBtn.style.borderColor = '#f44336';
            } else {
                wishlistBtn.style.color = '#666';
                wishlistBtn.style.borderColor = '#ddd';
            }
            
        } else {
            console.warn('Wishlist button not found!');
        }
    });
    </script>
</main>

<?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>
</html>
