<?php
require_once __DIR__ . '/../../backend/config/database.php';

$isEmbedded = $isEmbedded ?? false;
$brandInfo = null;

// Pagination and filtering could go here
if (!isset($products)) {
    try {
        if (isset($pdo)) {
            $query = "SELECT * FROM products WHERE status = 'active'";
            $params = [];

            if (isset($_GET['brand_id']) && is_numeric($_GET['brand_id'])) {
                $query .= " AND brand_id = :brand_id";
                $params[':brand_id'] = (int)$_GET['brand_id'];
                
                // Fetch brand details if brand_id is provided
                $brandStmt = $pdo->prepare("SELECT brand_name, description, brand_logo FROM brands WHERE brand_id = :brand_id LIMIT 1");
                $brandStmt->execute([':brand_id' => $params[':brand_id']]);
                $brandInfo = $brandStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($brandInfo && !isset($sectionTitle)) {
                    $sectionTitle = "Sản phẩm thương hiệu " . $brandInfo['brand_name'];
                }
            }

            $query .= " ORDER BY created_at DESC LIMIT 20";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $products = [];
        }
    } catch (PDOException $e) {
        $products = [];
    }
}
$sectionTitle = $sectionTitle ?? 'Danh sách sản phẩm';
?>
<?php if (!$isEmbedded): ?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($sectionTitle); ?> - PetsAccessories</title>
        <link rel="stylesheet" href="../layout/style.css">
    </head>

    <body>
        <?php require_once __DIR__ . '/../layout/Header.php'; ?>
    <?php endif; ?>
    <main class="products-page" style="padding: 40px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
            <section class="product-section">
                <h2>
                    <?php echo htmlspecialchars($sectionTitle); ?>
                </h2>

                <?php if (isset($brandInfo) && !empty($brandInfo['description'])): ?>
                    <div class="brand-description" style="margin-bottom: 30px; padding: 20px; background-color: #f9fbfd; border-radius: 8px; border: 1px solid #e1e8ee;">

                        <p style="margin: 0; line-height: 1.6; color: #555;"><?php echo nl2br(htmlspecialchars($brandInfo['description'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="product-grid">
                    <?php if (!empty($products) && is_array($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                            // THUẬT TOÁN KIỂM TRA SẢN PHẨM MỚI (Tự động theo thời gian thực)
                            $isNew = false;
                            if (!empty($product['created_at'])) {
                                $createdTime = strtotime($product['created_at']); // Đổi chuỗi thời gian trong DB thành số giây
                                $currentTime = time(); // Lấy thời gian hiện tại của máy chủ
                                $diffDays = ($currentTime - $createdTime) / 86400; // 86400 là tổng số giây trong 1 ngày

                                if ($diffDays <= 7) {
                                    $isNew = true; // Nếu khoảng cách <= 7 ngày thì bật cờ $isNew
                                }
                            }
                            ?>

                            <div class="product-card" style="position: relative; border: 1px solid #e1e8ee; border-radius: 8px; overflow: hidden; background: #fff; display: flex; flex-direction: column; height: 100%; transition: transform 0.2s, box-shadow 0.2s;">

                                <?php if ($isNew): ?>
                                    <div style="position: absolute; top: 10px; right: 10px; background: #ff4747; color: white; font-size: 12px; font-weight: bold; padding: 4px 10px; border-radius: 20px; z-index: 2; box-shadow: 0 2px 5px rgba(255, 71, 71, 0.4); text-transform: uppercase; letter-spacing: 1px;">
                                        New
                                    </div>
                                <?php endif; ?>

                                <div class="product-image" style="height: 200px; display: flex; align-items: center; justify-content: center; background: #f9fbfd; padding: 10px;">
                                    <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)($product['product_id'] ?? 0); ?>" style="width: 100%; height: 100%;">
                                        <?php
                                        // Xử lý đường dẫn ảnh 
                                        $thumbnail = $product['thumbnail'] ?? $product['image'] ?? '';
                                        if (!empty($thumbnail)) {
                                            if (strpos($thumbnail, '/') !== false) {
                                                $image = $thumbnail;
                                            } else {
                                                $image = '/PetsAccessories/admin/backend/uploads/products/' . htmlspecialchars($thumbnail);
                                            }
                                        } else {
                                            $image = '/PetsAccessories/frontend/public/images/default-product.png';
                                        }
                                        ?>
                                        <img src="<?php echo $image; ?>"
                                            alt="<?php echo htmlspecialchars($product['product_name'] ?? $product['name'] ?? 'Tên sản phẩm'); ?>"
                                            style="width: 100%; height: 100%; object-fit: contain;"
                                            onerror="this.onerror=null; this.src='/PetsAccessories/frontend/public/images/default-product.png'">
                                    </a>
                                </div>
                                <div class="product-info" style="padding: 15px; display: flex; flex-direction: column; flex: 1;">
                                    <h3 style="font-size: 14px; margin: 0 0 10px; height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; line-clamp: 2; -webkit-box-orient: vertical;">
                                        <a href="/PetsAccessories/frontend/components/product_detail.php?id=<?php echo (int)($product['product_id'] ?? 0); ?>" style="text-decoration: none; color: #333; line-height: 1.4;">
                                            <?php echo htmlspecialchars($product['product_name'] ?? $product['name'] ?? 'Product Name'); ?>
                                        </a>
                                    </h3>
                                    <p class="price" style="margin-top: auto; margin-bottom: 15px; color: #E53935; font-weight: bold; font-size: 15px;">
                                        <?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?> đ
                                    </p>
                                    <div style="display:flex; gap:10px;">
                                        <button class="btn-add-cart" style="flex:1; padding: 10px; background: #ff5c5c; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; transition: background 0.3s;" data-id="<?php echo (int)($product['product_id'] ?? 0); ?>" onclick="addToCart(this)">Thêm vào giỏ</button>
                                        <button class="btn-wishlist" style="background:none; border:1px solid #ddd; color: #ff5c5c; border-radius:4px; width: 42px; display: flex; align-items: center; justify-content: center; cursor:pointer;" onclick="toggleWishlist(<?php echo (int)($product['product_id'] ?? 0); ?>)" title="Yêu thích">❤️</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-products">Chưa có sản phẩm nào.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    <?php if (!$isEmbedded): ?>
        <?php require_once __DIR__ . '/../layout/Footer.php'; ?>

    </body>

    </html>
<?php endif; ?>