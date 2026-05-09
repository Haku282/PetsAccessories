<?php
/**
 * API: Upload ảnh sản phẩm
 * POST /admin/backend/api/products/upload-image.php
 */

header('Content-Type: application/json');

// Chỉ gọi session_start() nếu session chưa được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST request']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';
require_once __DIR__ . '/../../utils/products_helper.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $isMain = isset($_POST['is_main']) ? (int)$_POST['is_main'] : 0;

    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID không hợp lệ']);
        exit;
    }

    if (!isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có file ảnh']);
        exit;
    }

    // Validate file
    $errors = [];
    if (!validateImageFile($_FILES['image'], $errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'File không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Tạo thư mục upload nếu chưa có
    $uploadDir = __DIR__ . '/../../../../frontend/public/uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Tạo tên file duy nhất
    $fileName = generateImageFileName($_FILES['image']['name']);
    $filePath = $uploadDir . $fileName;
    $imageUrl = '/PetsAccessories/frontend/public/uploads/products/' . $fileName;

    // Upload file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Không thể upload file']);
        exit;
    }

    // Nếu là ảnh chính, hủy các ảnh chính khác
    if ($isMain) {
        $stmt = $db->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?");
        $stmt->execute([$productId]);
    }

    // Lưu vào database
    $success = addProductImage($db, $productId, $imageUrl, $isMain);

    if (!$success) {
        // Xóa file nếu lưu database thất bại
        unlink($filePath);
        throw new Exception('Không thể lưu thông tin ảnh vào database');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " upload ảnh cho sản phẩm ID: $productId");

    echo json_encode([
        'success' => true,
        'message' => 'Upload ảnh thành công',
        'image_url' => $imageUrl,
        'image_id' => $db->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
