<?php
require_once __DIR__ . '/../../backend/config/database.php';

$slug = $_GET['slug'] ?? '';
$post = null;

if (!empty($slug) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT title, content, thumbnail, created_at, category FROM posts WHERE slug = :slug AND status = 1 LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $post = null;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? htmlspecialchars($post['title']) : 'Không tìm thấy trang'; ?> - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <style>
        .detail-page-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .detail-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .detail-meta {
            color: #777;
            font-size: 14px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .detail-thumbnail {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .detail-content {
            font-size: 20px;
            line-height: 1.6;
            color: #444;
        }

        .detail-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="detail-page-container">
        <?php if ($post): ?>
            <h1 class="detail-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="detail-meta">
                <span>Chuyên mục: <?php echo htmlspecialchars($post['category']); ?></span> |
                <span>Đăng ngày: <?php echo date('d/m/Y', strtotime($post['created_at'])); ?></span>
            </div>

            <?php if (!empty($post['thumbnail'])): ?>
                <img class="detail-thumbnail" src="/PetsAccessories/admin/backend/uploads/posts/<?php echo htmlspecialchars($post['thumbnail']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            <?php endif; ?>

            <div class="detail-content">
                <?php
                // 1. Cắt bỏ các dấu Enter thừa ở trên cùng và dưới cùng của bài viết
                $content = trim($post['content']);

                // 2. Dọn dẹp: Nếu có 3-4 dấu Enter liên tiếp, tự động gộp lại thành tối đa 2 dấu (tương đương 1 dòng trống)
                $content = preg_replace("/(\r?\n){3,}/", "\n\n", $content);

                // 3. In ra màn hình bằng hàm nl2br() để tự động thêm thẻ xuống dòng <br> một cách mượt mà
                echo nl2br($content);
                ?>
            </div>
        <?php else: ?>
            <h1 class="detail-title">Bài viết không tồn tại hoặc đã bị ẩn</h1>
            <p>Vui lòng quay lại trang chủ.</p>
            <a href="/PetsAccessories/frontend/public/index.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:4px;">Trang chủ</a>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>

</html>