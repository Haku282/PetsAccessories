<?php
/**
 * Helper functions for Products Module
 * File: /admin/backend/utils/products_helper.php
 */

/**
 * Get product by ID
 * 
 * @param PDO $db Database connection
 * @param int $productId Product ID
 * @return array|null Product data or null
 */
function getProductById($db, $productId) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, c.category_name, b.brand_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            WHERE p.product_id = ?
        ");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Check if SKU exists
 * 
 * @param PDO $db Database connection
 * @param string $sku Product SKU
 * @param int $excludeId Product ID to exclude (for update)
 * @return bool True if exists
 */
function skuExists($db, $sku, $excludeId = null) {
    try {
        if ($excludeId) {
            $stmt = $db->prepare("SELECT product_id FROM products WHERE sku = ? AND product_id != ?");
            $stmt->execute([$sku, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT product_id FROM products WHERE sku = ?");
            $stmt->execute([$sku]);
        }
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get all categories with parent structure
 * 
 * @param PDO $db Database connection
 * @return array Categories list
 */
function getAllCategories($db) {
    try {
        $stmt = $db->query("
            SELECT category_id, category_name, parent_id, pet_type, status 
            FROM categories 
            WHERE status = 1
            ORDER BY parent_id, category_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get all brands
 * 
 * @param PDO $db Database connection
 * @return array Brands list
 */
function getAllBrands($db) {
    try {
        $stmt = $db->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Validate product data
 * 
 * @param array $data Product data
 * @param array $errors Errors array
 * @return bool True if valid
 */
function validateProductData($data, &$errors = []) {
    $errors = [];

    // Validate product name
    if (empty($data['product_name'])) {
        $errors[] = 'Tên sản phẩm không được để trống';
    } elseif (strlen($data['product_name']) > 255) {
        $errors[] = 'Tên sản phẩm không được vượt quá 255 ký tự';
    }

    // Validate category
    if (empty($data['category_id'])) {
        $errors[] = 'Danh mục sản phẩm không được để trống';
    } elseif (!is_numeric($data['category_id'])) {
        $errors[] = 'Danh mục không hợp lệ';
    }

    // Validate price
    if (empty($data['price'])) {
        $errors[] = 'Giá sản phẩm không được để trống';
    } elseif (!is_numeric($data['price']) || $data['price'] < 0) {
        $errors[] = 'Giá sản phẩm phải là số dương';
    }

    // Validate discount price
    if (!empty($data['discount_price'])) {
        if (!is_numeric($data['discount_price']) || $data['discount_price'] < 0) {
            $errors[] = 'Giá khuyến mãi phải là số dương';
        }
        if ($data['discount_price'] > $data['price']) {
            $errors[] = 'Giá khuyến mãi không được cao hơn giá gốc';
        }
    }

    // Validate stock quantity
    if (!isset($data['stock_quantity']) || $data['stock_quantity'] === '') {
        $errors[] = 'Tồn kho không được để trống';
    } elseif (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) {
        $errors[] = 'Tồn kho phải là số không âm';
    }

    // Validate SKU (if provided)
    if (!empty($data['sku']) && strlen($data['sku']) > 50) {
        $errors[] = 'SKU không được vượt quá 50 ký tự';
    }

    // Validate status
    $validStatuses = ['active', 'inactive', 'out_of_stock'];
    if (empty($data['status']) || !in_array($data['status'], $validStatuses)) {
        $errors[] = 'Trạng thái sản phẩm không hợp lệ';
    }

    return count($errors) === 0;
}

/**
 * Get product status label and color
 * 
 * @param string $status Product status
 * @return array Status label and color
 */
function getProductStatusInfo($status) {
    $statuses = [
        'active' => ['label' => 'Đang bán', 'color' => 'success', 'icon' => '✓'],
        'inactive' => ['label' => 'Ngừng kinh doanh', 'color' => 'warning', 'icon' => '⊗'],
        'out_of_stock' => ['label' => 'Hết hàng', 'color' => 'danger', 'icon' => '✗']
    ];
    return $statuses[$status] ?? ['label' => 'Không xác định', 'color' => 'secondary', 'icon' => '?'];
}

/**
 * Format product price with currency
 * 
 * @param float $price Price
 * @return string Formatted price
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' đ';
}

/**
 * Get product images
 * 
 * @param PDO $db Database connection
 * @param int $productId Product ID
 * @return array Images list
 */
function getProductImages($db, $productId) {
    try {
        $stmt = $db->prepare("
            SELECT image_id, image_url, is_main 
            FROM product_images 
            WHERE product_id = ? 
            ORDER BY is_main DESC, image_id ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Add product image
 * 
 * @param PDO $db Database connection
 * @param int $productId Product ID
 * @param string $imageUrl Image URL
 * @param bool $isMain Is main image
 * @return bool Success status
 */
function addProductImage($db, $productId, $imageUrl, $isMain = false) {
    try {
        $stmt = $db->prepare("
            INSERT INTO product_images (product_id, image_url, is_main)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$productId, $imageUrl, $isMain ? 1 : 0]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete product image
 * 
 * @param PDO $db Database connection
 * @param int $imageId Image ID
 * @return bool Success status
 */
function deleteProductImage($db, $imageId) {
    try {
        $stmt = $db->prepare("DELETE FROM product_images WHERE image_id = ?");
        return $stmt->execute([$imageId]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Generate unique filename for image
 * 
 * @param string $originalName Original filename
 * @return string Unique filename
 */
function generateImageFileName($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    return "product_" . $timestamp . "_" . $random . "." . $extension;
}

/**
 * Validate image file
 * 
 * @param array $file File array from $_FILES
 * @param array $errors Errors array
 * @return bool True if valid
 */
function validateImageFile($file, &$errors = []) {
    $errors = [];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (empty($file)) {
        $errors[] = 'File không tồn tại';
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Lỗi upload file: ' . $file['error'];
        return false;
    }

    if ($file['size'] > $maxFileSize) {
        $errors[] = 'File quá lớn, tối đa 5MB';
        return false;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        $errors[] = 'Định dạng file không hỗ trợ. Chỉ chấp nhận: JPG, PNG, GIF, WebP';
        return false;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        $errors[] = 'Định dạng file không hỗ trợ';
        return false;
    }

    return true;
}
?>
