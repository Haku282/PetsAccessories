<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/search.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <style>
        .search-results-section {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .search-results-section h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="search-results-section">
        <h2><?php echo empty($searchQuery) ? 'Tìm kiếm' : htmlspecialchars($pageTitle); ?></h2>

        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (empty($products)): ?>
            <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa của bạn.</p>
        <?php else: ?>
            <p>Tìm thấy <strong><?php echo count($products); ?></strong> sản phẩm phù hợp.</p>
            <br>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php include __DIR__ . '/ProductCard.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>

</html>