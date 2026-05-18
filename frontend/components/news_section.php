<?php
require_once __DIR__ . '/../../backend/src/news_section.php';

$scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
$isEmbedded = false;
if (!empty($scriptFilename)) {
    $isEmbedded = realpath($scriptFilename) !== realpath(__FILE__);
}

if (!$isEmbedded):
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức và khuyến mãi - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>

    <?php require_once __DIR__ . '/../layout/Header.php'; ?>
<?php endif; ?>

    <section class="news-section">
        <div class="news-header">
            <h2>Chương trình khuyến mãi</h2>
        </div>
        <div class="news-grid <?php echo !$isEmbedded ? 'news-grid--standalone' : ''; ?>">
            <?php if (empty($promotions)): ?>
                <article class="news-card">
                    <div class="news-media news-media--tips"></div>
                    <div class="news-body">
                        <span class="news-tag">Thông báo</span>
                        <h3 class="news-title">Chưa có bài viết</h3>
                        <p class="news-excerpt">Hiện tại chưa có khuyến mãi mới. Vui lòng quay lại sau.</p>
                        <a class="news-cta" href="#">Đọc thêm</a>
                    </div>
                </article>
            <?php else: ?>
                <?php foreach ($promotions as $index => $item): ?>
                    <?php
                    $thumbnail = !empty($item['thumbnail'])
                        ? '/PetsAccessories/admin/backend/uploads/posts/' . htmlspecialchars($item['thumbnail'])
                        : '/PetsAccessories/frontend/public/images/default-news.png';
                    $excerpt = trim(strip_tags($item['content'] ?? ''));
                    if (strlen($excerpt) > 120) {
                        $excerpt = substr($excerpt, 0, 120) . '...';
                    }
                    $mediaClass = $index % 3 === 0 ? 'news-media--tips' : ($index % 3 === 1 ? 'news-media--reward' : 'news-media--gear');
                    $link = '/PetsAccessories/frontend/public/index.php?page=news_detail&slug=' . urlencode($item['slug']);
                    ?>
                    <article class="news-card">
                        <div class="news-media <?php echo $mediaClass; ?>" style="background-image: url('<?php echo $thumbnail; ?>'); background-size: cover; background-position: center;"></div>
                        <div class="news-body">
                            <span class="news-tag">Khuyến mãi & Blog</span>
                            <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p class="news-excerpt"><?php echo htmlspecialchars($excerpt); ?></p>
                            <a class="news-cta" href="<?php echo htmlspecialchars($link); ?>">Đọc thêm</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="news-header" style="margin-top: 40px;">
            <h2>Tin tức & Thông báo</h2>
        </div>
        <div class="news-grid <?php echo !$isEmbedded ? 'news-grid--standalone' : ''; ?>">
            <?php if (empty($news)): ?>
                <article class="news-card">
                    <div class="news-media news-media--tips"></div>
                    <div class="news-body">
                        <span class="news-tag">Thông báo</span>
                        <h3 class="news-title">Chưa có trang</h3>
                        <p class="news-excerpt">Hiện tại chưa có trang thông báo mới. Vui lòng quay lại sau.</p>
                        <a class="news-cta" href="#">Đọc thêm</a>
                    </div>
                </article>
            <?php else: ?>
                <?php foreach ($news as $index => $item): ?>
                    <?php
                    $thumbnail = '/PetsAccessories/frontend/public/images/default-news.png';
                    $excerpt = trim(strip_tags($item['page_content'] ?? ''));
                    if (strlen($excerpt) > 120) {
                        $excerpt = substr($excerpt, 0, 120) . '...';
                    }
                    $mediaClass = $index % 3 === 0 ? 'news-media--tips' : ($index % 3 === 1 ? 'news-media--reward' : 'news-media--gear');
                    $link = '/PetsAccessories/frontend/public/index.php?page=page_detail&slug=' . urlencode($item['page_slug']);
                    ?>
                    <article class="news-card">
                        <div class="news-media <?php echo $mediaClass; ?>" style="background-image: url('<?php echo $thumbnail; ?>'); background-size: cover; background-position: center;"></div>
                        <div class="news-body">
                            <span class="news-tag">Tin tức</span>
                            <h3 class="news-title"><?php echo htmlspecialchars($item['page_title']); ?></h3>
                            <p class="news-excerpt"><?php echo htmlspecialchars($excerpt); ?></p>
                            <a class="news-cta" href="<?php echo htmlspecialchars($link); ?>">Đọc thêm</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

<?php if (!$isEmbedded): ?>
    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>

</html>
<?php endif; ?>