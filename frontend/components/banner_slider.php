<?php
// frontend/components/banner_slider.php
require_once __DIR__ . '/../../backend/config/database.php';

$banners = [];

if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM banners WHERE status = 1");
        $stmt->execute();
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Ghi log lỗi nếu cần thiết (không in ra màn hình để tránh lộ thông tin DB)
        error_log("Lỗi tải banner: " . $e->getMessage());
    }
}
?>
<section class="banner-slider">
    <div class="banners">
        <?php if (!empty($banners)): ?>
            <?php foreach ($banners as $banner): ?>
                <div class="banner-item">
                    <?php if (!empty($banner['link'])): ?>
                        <a href="<?php echo htmlspecialchars($banner['link']); ?>">
                        <?php endif; ?>

                        <img src="/PetsAccessories/upload/<?php echo htmlspecialchars($banner['image_url']); ?>" alt="<?php echo htmlspecialchars($banner['title'] ?? ''); ?>">

                        <?php if (!empty($banner['title'])): ?>
                            <div class="banner-text"><?php echo htmlspecialchars($banner['title']); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($banner['link'])): ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="banner-item">
                <img src="/PetsAccessories/frontend/public/images/banner1.jpg" alt="Siêu sale đồ ăn thú cưng">
                <div class="banner-text">Mùa hè sôi động, giảm 50% thức ăn hạt</div>
            </div>
        <?php endif; ?>
    </div>
</section>