<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Không có quyền truy cập']));
}

require_once __DIR__ . '/../../../config/database.php';

try {
    /** @var PDO $pdo */
    $uploadDir = __DIR__ . '/../../../../uploads/banners/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $title = $_POST['title'] ?? '';
    $link = $_POST['link'] ?? '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        exit(json_encode(['success' => false, 'message' => 'Vui lòng chọn hình ảnh hợp lệ']));
    }

    $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $fileName = 'banner_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO banners (title, image_url, link_url, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $fileName, $link, $status]);
        echo json_encode(['success' => true, 'message' => 'Thêm banner thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi upload hình ảnh lên server']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>