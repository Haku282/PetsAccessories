<?php
require_once __DIR__ . '/../../backend/config/database.php';

$newsItems = [];
$db = $pdo;

if ($db instanceof PDO) {
    try {
        $stmt = $db->prepare(
            'SELECT post_id, title, slug, content, thumbnail, category, created_at
             FROM posts
             WHERE status = 1 AND category = :category
             ORDER BY created_at DESC
             LIMIT 3'
        );
        $stmt->execute(['category' => 'news']);
        $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $newsItems = [];
    }
}
?>

<section class="news-section">
    <div class="news-header">
        <h2>Tin tức & Chương trình ưu đãi</h2>
        <p>Các bài viết nổi bật, mẹo chăm sóc và ưu đãi mới nhất cho boss.</p>
    </div>
    <div class="news-grid">
        <?php if (empty($newsItems)): ?>
            <article class="news-card">
                <div class="news-media news-media--tips"></div>
                <div class="news-body">
                    <span class="news-tag">Thông báo</span>
                    <h3 class="news-title">Chưa có bài viết</h3>
                    <p class="news-excerpt">Hiện tại chưa có tin tức mới. Vui lòng quay lại sau.</p>
                    <a class="news-cta" href="#">Đọc thêm</a>
                </div>
            </article>
        <?php else: ?>
            <?php foreach ($newsItems as $index => $item): ?>
                <?php
                $thumbnail = !empty($item['thumbnail'])
                    ? $item['thumbnail']
                    : '/PetsAccessories/frontend/public/images/default-news.png';
                $excerpt = trim(strip_tags($item['content'] ?? ''));
                if (strlen($excerpt) > 120) {
                    $excerpt = substr($excerpt, 0, 120) . '...';
                }
                $mediaClass = $index % 3 === 0 ? 'news-media--tips' : ($index % 3 === 1 ? 'news-media--reward' : 'news-media--gear');
                $link = '/PetsAccessories/frontend/components/news_detail.php?slug=' . urlencode($item['slug']);
                ?>
                <article class="news-card">
                    <div class="news-media <?php echo $mediaClass; ?>" style="background-image: url('<?php echo htmlspecialchars($thumbnail); ?>'); background-size: cover; background-position: center;"></div>
                    <div class="news-body">
                        <span class="news-tag"><?php echo htmlspecialchars($item['category']); ?></span>
                        <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p class="news-excerpt"><?php echo htmlspecialchars($excerpt); ?></p>
                        <a class="news-cta" href="<?php echo htmlspecialchars($link); ?>">Đọc thêm</a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <p class="index-link" style="text-align: center; margin-top: 20px;">
        <a href="/PetsAccessories/frontend/public/index.php">Quay về trang chủ</a>
    </p>
</section>