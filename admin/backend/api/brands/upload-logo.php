<?php
/**
 * API: Upload logo thương hiệu
 * POST /admin/backend/api/brands/upload-logo.php
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
    if (!isset($_FILES['logo'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có file logo']);
        exit;
    }

    $file = $_FILES['logo'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'File quá lớn, tối đa 2MB']);
        exit;
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Định dạng file không hỗ trợ']);
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
    $uploadDir = __DIR__ . '/../../uploads/brands/';
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
            'filename' => $existingFile,
            'image_url' => '/PetsAccessories/admin/backend/uploads/brands/' . $existingFile
        ]);
        exit;
    }

    // Generate unique filename
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    $fileName = 'brand_' . $timestamp . '_' . $random . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    $imageUrl = '/PetsAccessories/admin/backend/uploads/brands/' . $fileName;

    // Upload file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Không thể upload file');
    }

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Upload logo thành công',
        'filename' => $fileName,
        'image_url' => $imageUrl
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
