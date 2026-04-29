<?php
// Delegate to backend logic
require_once __DIR__ . '/../../backend/src/category.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($categoryName); ?> - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="product-section">
        <h2><?php echo htmlspecialchars($categoryName); ?></h2>

        <?php if (!empty($error)): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (empty($products)): ?>
            <p>Chưa có sản phẩm trong danh mục này.</p>
        <?php else: ?>
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