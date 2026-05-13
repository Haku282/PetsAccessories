<?php
/**
 * API: Upload ảnh bài viết
 * POST /admin/backend/api/posts/upload-image.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST request']);
    exit;
}

try {
    if (!isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có file ảnh']);
        exit;
    }

    $file = $_FILES['image'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'File quá lớn, tối đa 5MB']);
        exit;
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Định dạng file không hỗ trợ. Chỉ chấp nhận: JPG, PNG, GIF, WebP']);
        exit;
    }

    // Validate extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Định dạng file không hỗ trợ']);
        exit;
    }

    // Create upload directory if not exists
    $uploadDir = __DIR__ . '/../../uploads/posts/';
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

    // If identical file exists, reuse it
    if ($existingFile) {
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'File này đã tồn tại, sử dụng lại',
            'image_url' => '/PetsAccessories/admin/backend/uploads/posts/' . $existingFile,
            'filename' => $existingFile
        ]);
        exit;
    }

    // Generate unique filename
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    $fileName = 'post_' . $timestamp . '_' . $random . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    $imageUrl = '/PetsAccessories/admin/backend/uploads/posts/' . $fileName;

    // Upload file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Không thể upload file');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " upload ảnh bài viết: $fileName");

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Upload ảnh thành công',
        'image_url' => $imageUrl,
        'filename' => $fileName
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
