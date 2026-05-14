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
<section class="banner-slider-container" style="position: relative; max-width: 100%; overflow: hidden; margin: 20px auto; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
    <div class="banners-wrapper" id="banners-wrapper" style="display: flex; transition: transform 0.5s ease-in-out;">
        <?php if (!empty($banners)): ?>
            <?php foreach ($banners as $banner): ?>
                <div class="banner-slide" style="flex: 0 0 100%; width: 100%; box-sizing: border-box; position: relative;">
                    <?php if (!empty($banner['link_url'])): ?>
                        <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" style="display: block;">
                        <?php endif; ?>

                        <img src="/PetsAccessories/admin/backend/uploads/banners/<?php echo htmlspecialchars($banner['image_url']); ?>"
                            alt="<?php echo htmlspecialchars($banner['title'] ?? ''); ?>"
                            loading="lazy"
                            style="width: 100%; height: 500px; object-fit: contain; background: #fff; display: block; border-radius: 12px;"
                            onerror="this.onerror=null; this.src='/PetsAccessories/admin/backend/uploads/banners/default-banner.png'">
                        <?php if (!empty($banner['title'])): ?>
                            <div class="banner-text" style="position: absolute; bottom: 20px; left: 20px; background: rgba(0,0,0,0.6); color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold;"><?php echo htmlspecialchars($banner['title']); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($banner['link_url'])): ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="banner-slide" style="flex: 0 0 100%; width: 100%; box-sizing: border-box; position: relative;">
                <img src="/PetsAccessories/admin/backend/uploads/banners/default-banner.png" alt="Siêu sale đồ ăn thú cưng" style="width: 100%; height: 500px; object-fit: cover; display: block; border-radius: 12px;">
                <div class="banner-text" style="position: absolute; bottom: 20px; left: 20px; background: rgba(0,0,0,0.6); color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold;">Mùa hè sôi động, giảm 50% thức ăn hạt</div>
            </div>
        <?php endif; ?>
    </div>

    <button onclick="prevSlide()" style="position: absolute; top: 50%; left: 15px; transform: translateY(-50%); background: rgba(255,255,255,0.8); border: none; font-size: 20px; cursor: pointer; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">❮</button>
    <button onclick="nextSlide()" style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); background: rgba(255,255,255,0.8); border: none; font-size: 20px; cursor: pointer; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">❯</button>
</section>

<script>
    let currentSlide = 0;
    const wrapper = document.getElementById('banners-wrapper');
    const slides = document.querySelectorAll('.banner-slide');
    const totalSlides = slides.length;

    function updateSlider() {
        if (wrapper) {
            wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
        }
    }

    function nextSlide() {
        if (totalSlides > 0) {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }
    }

    function prevSlide() {
        if (totalSlides > 0) {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlider();
        }
    }

    // Tự động trượt mỗi 4 giây
    if (totalSlides > 1) {
        setInterval(nextSlide, 4000);
    }
</script>