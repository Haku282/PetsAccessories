<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
error_log('=== PRODUCT_DETAIL DEBUG ===');
error_log('GET id: ' . ($_GET['id'] ?? 'NOT SET'));
error_log('Filtered productId: ' . ($productId ?? 'NULL'));

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
            'SELECT p.product_id, p.category_id, p.product_name, p.price, p.discount_price, p.thumbnail, p.description, p.specifications, p.stock_quantity, b.brand_name
             FROM products p
             LEFT JOIN brands b ON p.brand_id = b.brand_id
             WHERE p.status = 1 AND p.product_id = ?
             LIMIT 1'
        );
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log('Product fetched: ' . json_encode($product));
    } catch (PDOException $e) {
        $product = null;
    }

    if (!$product) {
        try {
            // Fallback if specifications column does not exist
            $stmt = $db->prepare(
                'SELECT p.product_id, p.category_id, p.product_name, p.price, p.discount_price, p.thumbnail, p.description, p.stock_quantity, b.brand_name
                 FROM products p
                 LEFT JOIN brands b ON p.brand_id = b.brand_id
                 WHERE p.status = 1 AND p.product_id = ?
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
                'SELECT p.product_id, p.category_id, p.product_name, p.price, p.discount_price, p.thumbnail, p.stock_quantity, b.brand_name
                 FROM products p
                 LEFT JOIN brands b ON p.brand_id = b.brand_id
                 WHERE p.status = 1 AND p.product_id = ?
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

        // Fetch product images
        $productImages = [];
        try {
            $imgStmt = $db->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY is_main DESC, image_id ASC");
            $imgStmt->execute([$productId]);
            $productImages = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            $productImages = [];
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

        // Check if product is in wishlist
        $isInWishlist = false;
        if (isset($_SESSION['user_id'])) {
            try {
                $wishlistStmt = $db->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1");
                $wishlistStmt->execute([$_SESSION['user_id'], $productId]);
                $isInWishlist = (bool) $wishlistStmt->fetch();
            } catch (PDOException $e) {
                $isInWishlist = false;
            }
        }

        // Fetch related products from parent category (5 random products)
        $categoryId = (int) ($product['category_id'] ?? 0);
        $parentCategoryId = null;

        // Nếu danh mục hiện tại có parent_id > 0, thì nó là subcategory, lấy products từ parent
        // Nếu danh mục hiện tại không có parent_id, thì nó là category cha, lấy products từ nó
        try {
            if ($categoryId > 0) {
                $catStmt = $db->prepare(
                    'SELECT parent_id FROM categories WHERE category_id = ? LIMIT 1'
                );
                $catStmt->execute([$categoryId]);
                $catRow = $catStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($catRow) {
                    // Nếu category có parent_id, dùng parent_id; nếu không thì dùng chính nó
                    $parentCategoryId = (int) ($catRow['parent_id'] ?? 0);
                    if ($parentCategoryId === 0) {
                        // Nó là category cha rồi, dùng chính nó
                        $parentCategoryId = $categoryId;
                    }
                }
            }
        } catch (PDOException $e) {
            $parentCategoryId = null;
        }

        // Lấy sản phẩm từ danh mục cha + subcategory
        if ($parentCategoryId !== null && $parentCategoryId > 0) {
            try {
                $relatedStmt = $db->prepare(
                    'SELECT p.product_id, p.product_name, p.price, p.discount_price, p.thumbnail
                     FROM products p
                     JOIN categories c ON p.category_id = c.category_id
                     WHERE (c.category_id = ? OR c.parent_id = ?)
                       AND p.status = 1
                       AND p.product_id != ?
                     ORDER BY RAND()'
                );
                $relatedStmt->execute([$parentCategoryId, $parentCategoryId, $productId]);
                $categoryProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Nếu có ít hơn 5 sản phẩm từ danh mục cha, lấy thêm từ các sản phẩm khác
                if (count($categoryProducts) < 5) {
                    $productsToAdd = 5 - count($categoryProducts);
                    try {
                        $otherStmt = $db->prepare(
                            'SELECT p.product_id, p.product_name, p.price, p.discount_price, p.thumbnail
                             FROM products p
                             WHERE p.status = 1
                               AND p.product_id != ?
                               AND p.product_id NOT IN (SELECT product_id FROM (
                                   SELECT p2.product_id
                                   FROM products p2
                                   JOIN categories c ON p2.category_id = c.category_id
                                   WHERE (c.category_id = ? OR c.parent_id = ?)
                                     AND p2.product_id != ?
                               ) as temp)
                             ORDER BY RAND()
                             LIMIT ' . (int)$productsToAdd
                        );
                        $otherStmt->execute([$productId, $parentCategoryId, $parentCategoryId, $productId]);
                        $otherProducts = $otherStmt->fetchAll(PDO::FETCH_ASSOC);
                        $relatedProducts = array_merge($categoryProducts, $otherProducts);
                    } catch (PDOException $e) {
                        $relatedProducts = $categoryProducts;
                    }
                } else {
                    $relatedProducts = $categoryProducts;
                }
            } catch (PDOException $e) {
                $relatedProducts = [];
            }
        }

        // Fallback: Nếu không tìm thấy sản phẩm liên quan, lấy 5 sản phẩm ngẫu nhiên từ tất cả
        if (empty($relatedProducts)) {
            try {
                $fallbackStmt = $db->prepare(
                    'SELECT p.product_id, p.product_name, p.price, p.discount_price, p.thumbnail
                     FROM products p
                     WHERE p.status = 1
                       AND p.product_id != ?
                     ORDER BY RAND()
                     LIMIT 5'
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