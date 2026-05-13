<?php
require_once __DIR__ . '/../../backend/config/database.php';

$slug = $_GET['slug'] ?? '';
$page = null;

if (!empty($slug) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT page_title, page_content, updated_at FROM pages WHERE page_slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $page = null;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page ? htmlspecialchars($page['page_title']) : 'Không tìm thấy trang'; ?> - Pets Accessories</title>
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
    <?php require_once __DIR__ . '/../layout/header.php'; ?>

    <main class="detail-page-container">
        <?php if ($page): ?>
            <h1 class="detail-title"><?php echo htmlspecialchars($page['page_title']); ?></h1>
            <div class="detail-meta">
                <span>Cập nhật lần cuối: <?php echo date('d/m/Y H:i', strtotime($page['updated_at'])); ?></span>
            </div>

            <div class="detail-content">
                <?php
                // 1. Cắt bỏ các dấu Enter thừa ở trên cùng và dưới cùng của bài viết
                $content = trim($page['page_content']);

                // 2. Dọn dẹp: Nếu có 3-4 dấu Enter liên tiếp, tự động gộp lại thành tối đa 2 dấu (tương đương 1 dòng trống)
                $content = preg_replace("/(\r?\n){3,}/", "\n\n", $content);

                // 3. In ra màn hình bằng hàm nl2br() để tự động thêm thẻ xuống dòng <br> một cách mượt mà
                echo nl2br($content);
                ?>
            </div>
        <?php else: ?>
            <h1 class="detail-title">Trang không tồn tại</h1>
            <p>Vui lòng quay lại trang chủ.</p>
            <a href="/PetsAccessories/frontend/public/index.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:4px;">Trang chủ</a>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../layout/footer.php'; ?>
</body>

</html>