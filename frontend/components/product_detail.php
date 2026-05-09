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

<?php require_once __DIR__ . '/../layout/Header.php'; ?>

<main class="product-detail">
    <?php if (!empty($error)): ?>
        <div class="product-detail__error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <div class="product-detail__grid">
            <div class="product-detail__media">
                <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($product['product_name'] ?? 'Sản phẩm'); ?>">
            </div>
            <div class="product-detail__info">
                <h1><?php echo htmlspecialchars($product['product_name'] ?? 'Sản phẩm'); ?></h1>
                <p class="product-detail__price">
                    <?php echo number_format($finalPrice, 0, ',', '.'); ?> đ
                    <?php if ($discountPrice > 0 && $discountPrice < $price): ?>
                        <span class="product-detail__price-old"><?php echo number_format($price, 0, ',', '.'); ?> đ</span>
                    <?php endif; ?>
                </p>
                <p class="product-detail__stock">
                    Tồn kho: <strong><?php echo $stockQuantity; ?></strong>
                </p>
                <button type="button" class="btn-add-cart" data-id="<?php echo (int) ($product['product_id'] ?? 0); ?>" onclick="addToCart(this)">Thêm vào giỏ</button>
            </div>
        </div>

        <div class="product-detail__content">
            <div class="product-detail__section">
                <h2>Mô tả sản phẩm</h2>
                <p><?php echo !empty($description) ? nl2br(htmlspecialchars($description)) : 'Hiện chưa có mô tả.'; ?></p>
            </div>
            <div class="product-detail__section">
                <h2>Thông số kỹ thuật</h2>
                <?php if (!empty($specItems)): ?>
                    <ul>
                        <?php foreach ($specItems as $item): ?>
                            <li><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Hiện chưa có thông số kỹ thuật.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="product-detail__reviews">
            <h2>Đánh giá từ người mua</h2>
            <?php if (isset($ratingStats) && $ratingStats['total_reviews'] > 0): ?>
                <div class="average-rating">
                    <span class="rating-point"><?php echo number_format($ratingStats['avg_rating'], 1); ?>/5</span>
                    <span class="total-reviews">(<?php echo $ratingStats['total_reviews']; ?> đánh giá)</span>
                </div>
            <?php endif; ?>

            <?php if (isset($canReview) && $canReview): ?>
                <div class="review-form">
                    <h3>Viết đánh giá của bạn</h3>
                    <form id="submitReviewForm" onsubmit="submitReview(event)">
                        <input type="hidden" name="product_id" value="<?php echo (int)$productId; ?>">
                        <div class="form-group">
                            <label for="rating">Đánh giá:</label>
                            <select name="rating" id="rating" required>
                                <option value="5">5 Sao</option>
                                <option value="4">4 Sao</option>
                                <option value="3">3 Sao</option>
                                <option value="2">2 Sao</option>
                                <option value="1">1 Sao</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comment">Bình luận:</label>
                            <textarea name="comment" id="comment" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn">Gửi đánh giá</button>
                    </form>
                    <script>
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
                    </script>
                </div>
            <?php endif; ?>

            <?php if (!empty($reviews) && is_array($reviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item" style="border-bottom: 1px solid #eee; margin-bottom: 10px; padding-bottom: 10px;">
                            <div class="review-header">
                                <strong class="reviewer-name"><?php echo htmlspecialchars($review['fullname'] ?? 'Khách hàng'); ?></strong>
                                <div class="review-rating" style="color: #ff9800; display: inline-block; margin-left: 10px;">
                                    <?php 
                                    $rating = (int) ($review['rating'] ?? 5);
                                    for ($i = 0; $i < 5; $i++) {
                                        echo $i < $rating ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <span class="review-date">
                                    <?php 
                                    $date = strtotime($review['created_at'] ?? 'now');
                                    echo date('d/m/Y', $date);
                                    ?>
                                </span>
                            </div>
                            <p class="review-comment">
                                <?php echo htmlspecialchars($review['comment'] ?? ''); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-reviews">Chưa có đánh giá nào cho sản phẩm này.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($relatedProducts) && is_array($relatedProducts)): ?>
        <div class="related-products">
            <h2>Sản phẩm liên quan</h2>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $relProd): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="?id=<?php echo (int) $relProd['product_id']; ?>">
                                <img src="<?php echo htmlspecialchars((string) ($relProd['thumbnail_resolved'] ?? '')); ?>" alt="<?php echo htmlspecialchars($relProd['product_name']); ?>">
                            </a>
                        </div>
                        <div class="product-info">
                            <a href="?id=<?php echo (int) $relProd['product_id']; ?>">
                                <h3><?php echo htmlspecialchars($relProd['product_name']); ?></h3>
                            </a>
                            <p class="price"><?php echo number_format((float) ($relProd['final_price'] ?? 0), 0, ',', '.'); ?> đ</p>
                            <button type="button" class="btn-add-cart" data-id="<?php echo (int) $relProd['product_id']; ?>" onclick="addToCart(this)">Thêm vào giỏ</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>
</html>
