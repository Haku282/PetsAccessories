<?php
// frontend/layout/Header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

$db = $pdo;

if (!($db instanceof PDO)) {
    $category_tree = [];
} else {
    try {
        $stmt = $db->query("SELECT category_id, category_name, parent_id FROM categories WHERE status = 1 ORDER BY COALESCE(parent_id, 0),  category_name");
        $all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $category_tree = [];
        foreach ($all_categories as $category) {
            if ($category['parent_id'] === null) {
                $category_tree[(int) $category['category_id']] = $category;
                $category_tree[(int) $category['category_id']]['children'] = [];
            }
        }

        foreach ($all_categories as $category) {
            $parent_id = $category['parent_id'];
            if ($parent_id !== null && isset($category_tree[(int) $parent_id])) {
                $category_tree[(int) $parent_id]['children'][] = $category;
            }
        }
    } catch (PDOException $e) {
        $category_tree = [];
        echo "<div style='background: red; color: white; text-align: center; padding: 10px; z-index: 9999; position: relative;'>Lỗi Header SQL: " . $e->getMessage() . "</div>";
    }
}

$mega_menu_columns = array_chunk(array_values($category_tree), 3);

$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = count($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Pet Accessories</title>
    <link rel="stylesheet" type="text/css" href="/PetsAccessories/frontend/layout/style.css">
</head>

<body>
    <div class="background-blur-overlay"></div>

    <header>
        <div class="header-top">

            <div style="max-width: 1200px; margin: 0 auto; width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; box-sizing: border-box;">

                <div class="logo">
                    <a href="/PetsAccessories/frontend/public/index.php" style="text-decoration: none;">
                        <img src="../../backend/upload/logo/logo.png" alt="PetsAccessories Logo" style="max-height: 70px; width: auto;">
                    </a>
                </div>

                <div class="search-bar" style="flex: 1; max-width: 500px; margin: 0 30px;">
                    <form action="/PetsAccessories/frontend/components/search.php" method="GET" style="display: flex; align-items: center; gap: 8px; width: 100%;">
                        <input type="text" name="q" placeholder="Tìm kiếm nhanh sản phẩm..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" style="flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                        <button type="submit" style="padding: 8px 15px; border: none; border-radius: 4px; background: #ff5c5c; color: white; cursor: pointer; font-weight: bold;">Tìm kiếm</button>
                        <button type="button" class="btn-filter" title="Lọc nâng cao" onclick="toggleSortFilter()" style="background: #f5f5f5; border: 1px solid #ddd; padding: 8px 12px; border-radius: 4px; cursor: pointer;">💵</button>

                        <select id="price-sort" name="sort" onchange="this.form.submit()" style="display: <?php echo !empty($_GET['sort']) ? 'block' : 'none'; ?>; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Mặc định --</option>
                            <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'price_asc') ? 'selected' : ''; ?>>Giá: Thấp đến cao</option>
                            <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'price_desc') ? 'selected' : ''; ?>>Giá: Cao đến thấp</option>
                        </select>
                    </form>
                </div>

                <div class="auth-buttons" style="display: flex; align-items: center; gap: 25px;">
                    <div class="cart-icon-container" style="position: relative;">
                        <a href="/PetsAccessories/frontend/components/cart.php" style="text-decoration: none; color: #fff; font-weight: bold; font-size: 16px; display: flex; align-items: center; gap: 5px;">
                            🛒 Giỏ hàng
                            <span id="cart-count-badge" style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; position: absolute; top: -12px; right: -20px;"><?php echo $cartCount; ?></span>
                        </a>
                    </div>

                    <?php if (isset($_SESSION['user_name'])): ?>
                        <div class="user-menu">
                            <button class="btn-profile" style="background: none; border: none; font-weight: bold; font-size: 15px; color: #fff; cursor: pointer;">Chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?> &#9662;</button>
                            <div class="dropdown-content">
                                <a href="/PetsAccessories/frontend/components/profile.php">Hồ sơ cá nhân</a>
                                <a href="/PetsAccessories/frontend/components/orders.php">Quản lý đơn hàng</a>
                                <a href="/PetsAccessories/frontend/public/index.php?page=wishlist">Danh sách yêu thích</a>
                                <a href="/PetsAccessories/frontend/components/change_password.php">Đổi mật khẩu</a>
                                <a href="/PetsAccessories/frontend/components/logout.php" class="logout-link">Đăng xuất</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="login">
                            <form action="/PetsAccessories/frontend/components/login.php" method="GET">
                                <button type="submit" class="btn-login" style="background: #fff; border: 1px solid #0b59d6; color: #0b59d6; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">Đăng nhập</button>
                            </form>
                        </div>
                        <div class="register">
                            <form action="/PetsAccessories/frontend/components/register.php" method="GET">
                                <button type="submit" class="btn-register" style="background: #ff5c5c; border: none; color: white; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">Đăng ký</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <nav class="primary-nav">
            <ul class="primary-menu" style="max-width: 1200px; margin: 0 auto; display: flex; padding: 0 15px;">
                <li class="has-mega">
                    <a href="/PetsAccessories/frontend/public/index.php">Boss</a>
                    <div class="mega-dropdown">
                        <div class="mega-inner">
                            <?php foreach ($mega_menu_columns as $column): ?>
                                <div class="mega-col">
                                    <?php foreach ($column as $parent_category): ?>
                                        <div class="mega-group">
                                            <h4><?php echo htmlspecialchars($parent_category['category_name']); ?></h4>
                                            <?php if (!empty($parent_category['children'])): ?>
                                                <ul>
                                                    <?php foreach ($parent_category['children'] as $child): ?>
                                                        <li><a href="/PetsAccessories/frontend/components/category.php?id=<?php echo (int) $child['category_id']; ?>"><?php echo htmlspecialchars($child['category_name']); ?></a></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
                <li><a href="/PetsAccessories/frontend/components/popular_products.php">Hàng Mới Về</a></li>
                <li><a href="/PetsAccessories/frontend/components/brands.php">Thương Hiệu</a></li>
                <li><a href="/PetsAccessories/frontend/components/news_section.php">Tin Tức và Khuyến Mãi</a></li>
            </ul>
        </nav>
    </header>

    <script>
        function toggleSortFilter() {
            var sortSelect = document.getElementById('price-sort');
            if (sortSelect.style.display === 'none' || sortSelect.style.display === '') {
                sortSelect.style.display = 'block';
            } else {
                sortSelect.style.display = 'none';
            }
        }
    </script>