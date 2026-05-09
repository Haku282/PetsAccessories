<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = null;
$reviews = [];
$relatedProducts = [];
$error = '';
$db = $pdo;

if (!$productId) {
    $error = 'Sản phẩm không hợp lệ.';
} elseif (!($db instanceof PDO)) {
    $error = 'Kết nối cơ sở dữ liệu chưa sẵn sàng.';
} else {
    try {
        // Try full fields first
        $stmt = $db->prepare(
            'SELECT product_id, category_id, product_name, price, discount_price, thumbnail, description, specifications, stock_quantity
             FROM products
             WHERE status = 1 AND product_id = ?
             LIMIT 1'
        );
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $product = null;
    }

    if (!$product) {
        try {
            // Fallback if specifications column does not exist
            $stmt = $db->prepare(
                'SELECT product_id, category_id, product_name, price, discount_price, thumbnail, description, stock_quantity
                 FROM products
                 WHERE status = 1 AND product_id = ?
                 LIMIT 1'
            );
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $product['specifications'] = '';
            }
        } catch (PDOException $e) {
            $product = null;
        }
    }

    if (!$product) {
        try {
            // Minimal fallback
            $stmt = $db->prepare(
                'SELECT product_id, category_id, product_name, price, discount_price, thumbnail, stock_quantity
                 FROM products
                 WHERE status = 1 AND product_id = ?
                 LIMIT 1'
            );
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $product['description'] = '';
                $product['specifications'] = '';
            }
        } catch (PDOException $e) {
            $product = null;
        }
    }

    if (!$product) {
        $error = 'Không tìm thấy sản phẩm.';
    } else {
        // Fetch reviews if product exists
        try {
            $reviewStmt = $db->prepare(
                'SELECT r.review_id, r.user_id, r.rating, r.comment, r.created_at, u.fullname
                 FROM reviews r
                 LEFT JOIN users u ON r.user_id = u.user_id
                 WHERE r.product_id = ? AND r.status = 1
                 ORDER BY r.created_at DESC
                 LIMIT 10'
            );
            $reviewStmt->execute([$productId]);
            $reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch average rating
            $avgStmt = $db->prepare('SELECT AVG(rating) as avg_rating, COUNT(review_id) as total_reviews FROM reviews WHERE product_id = ? AND status = 1');
            $avgStmt->execute([$productId]);
            $ratingStats = $avgStmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $reviews = [];
            $ratingStats = ['avg_rating' => 0, 'total_reviews' => 0];
        }

        // Check if user can review
        $canReview = false;
        if (isset($_SESSION['user_id'])) {
            try {
                $checkOrderIdStmt = $db->prepare("
                    SELECT o.order_id
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.order_id
                    WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'completed'
                    LIMIT 1
                ");
                $checkOrderIdStmt->execute([$_SESSION['user_id'], $productId]);
                if ($checkOrderIdStmt->fetch()) {
                    $checkRevStmt = $db->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
                    $checkRevStmt->execute([$_SESSION['user_id'], $productId]);
                    if (!$checkRevStmt->fetch()) {
                        $canReview = true;
                    }
                }
            } catch (PDOException $e) {
                $canReview = false;
            }
        }

        // Fetch related products (prefer same category; fallback to latest products)
        $categoryId = (int) ($product['category_id'] ?? 0);

        if ($categoryId > 0) {
            try {
                $relatedStmt = $db->prepare(
                    'SELECT p.product_id, p.product_name, p.price, p.discount_price, p.thumbnail
                     FROM products p
                     WHERE p.category_id = ?
                       AND p.status = 1
                       AND p.product_id != ?
                     ORDER BY p.product_id DESC
                     LIMIT 6'
                );
                $relatedStmt->execute([$categoryId, $productId]);
                $relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $relatedProducts = [];
            }
        }

        if (empty($relatedProducts)) {
            try {
                $fallbackStmt = $db->prepare(
                    'SELECT p.product_id, p.product_name, p.price, p.discount_price, p.thumbnail
                     FROM products p
                     WHERE p.status = 1
                       AND p.product_id != ?
                     ORDER BY p.product_id DESC
                     LIMIT 6'
                );
                $fallbackStmt->execute([$productId]);
                $relatedProducts = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $relatedProducts = [];
            }
        }
    }
}

$price = 0;
$discountPrice = 0;
$finalPrice = 0;
$thumbnail = '';
$description = '';
$specItems = [];
$stockQuantity = 0;

if (!empty($product)) {
    $price = (float) ($product['price'] ?? 0);
    $discountPrice = (float) ($product['discount_price'] ?? 0);
    $finalPrice = ($discountPrice > 0 && $discountPrice < $price) ? $discountPrice : $price;

    $thumbnail = !empty($product['thumbnail'])
        ? (string) $product['thumbnail']
        : '/PetsAccessories/frontend/public/images/default-product.png';

    $description = trim((string) ($product['description'] ?? ''));
    $specifications = trim((string) ($product['specifications'] ?? ''));
    $stockQuantity = (int) ($product['stock_quantity'] ?? 0);

    if ($specifications !== '') {
        if (strpos($specifications, "\n") !== false) {
            $specItems = array_filter(array_map('trim', explode("\n", $specifications)));
        } elseif (strpos($specifications, '\\n') !== false) {
            $specItems = array_filter(array_map('trim', explode('\\n', $specifications)));
        } elseif (strpos($specifications, ';') !== false) {
            $specItems = array_filter(array_map('trim', explode(';', $specifications)));
        } else {
            $specItems = [$specifications];
        }
    }
}

if (!empty($relatedProducts) && is_array($relatedProducts)) {
    foreach ($relatedProducts as &$relProd) {
        $relPrice = (float) ($relProd['price'] ?? 0);
        $relDiscount = (float) ($relProd['discount_price'] ?? 0);
        $relProd['final_price'] = ($relDiscount > 0 && $relDiscount < $relPrice) ? $relDiscount : $relPrice;
        $relProd['thumbnail_resolved'] = !empty($relProd['thumbnail'])
            ? (string) $relProd['thumbnail']
            : '/PetsAccessories/frontend/public/images/default-product.png';
    }
    unset($relProd);
}