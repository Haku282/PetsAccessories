<?php
/**
 * API: Upload thumbnail sản phẩm
 * POST /admin/backend/api/products/upload-thumbnail.php
 * 
 * Parameters:
 *  - image: (required) File ảnh thumbnail
 */

header('Content-Type: application/json');

// Clean output buffer
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    ob_end_clean();
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

try {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Vui lòng chọn hình ảnh hợp lệ');
    }

    $file = $_FILES['image'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        throw new Exception('File quá lớn, tối đa 5MB');
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes)) {
        throw new Exception('Chỉ hỗ trợ file JPG, PNG, GIF, WebP');
    }

    // Validate extension
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception('Chỉ hỗ trợ file JPG, PNG, GIF, WebP');
    }

    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/../../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Calculate file hash to avoid duplicates
    $fileHash = md5_file($file['tmp_name']);
    
    // Check if file with same hash already exists
    $existingFile = null;
    $files = scandir($uploadDir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            $filePath = $uploadDir . $f;
            if (is_file($filePath) && md5_file($filePath) === $fileHash) {
                $existingFile = $f;
                break;
            }
        }
    }

    // If identical file exists, reuse filename
    if ($existingFile) {
        $fileName = $existingFile;
    } else {
        // Generate unique filename and upload
        $fileName = 'product_' . time() . '_' . rand(10000, 99999) . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Lỗi khi upload file lên server');
        }
    }

    // Return success response
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'filename' => $fileName,
        'image_url' => '/PetsAccessories/admin/backend/uploads/products/' . $fileName,
        'message' => 'Upload ảnh thành công'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
