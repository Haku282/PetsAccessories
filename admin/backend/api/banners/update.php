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
    
    $id = (int)($_POST['id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $link = $_POST['link'] ?? '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    $checkStmt = $pdo->prepare("SELECT image_url FROM banners WHERE banner_id = ?");
    $checkStmt->execute([$id]);
    $oldBanner = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldBanner) {
        exit(json_encode(['success' => false, 'message' => 'Banner không tồn tại']));
    }

    $fileName = $oldBanner['image_url'];

    // Nếu có upload ảnh mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newFileName = 'banner_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
        $targetPath = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Xóa ảnh cũ
            if ($fileName && file_exists($uploadDir . $fileName)) {
                unlink($uploadDir . $fileName);
            }
            $fileName = $newFileName;
        }
    }

    $stmt = $pdo->prepare("UPDATE banners SET title = ?, image_url = ?, link_url = ?, status = ? WHERE banner_id = ?");
    $stmt->execute([$title, $fileName, $link, $status, $id]);

    echo json_encode(['success' => true, 'message' => 'Cập nhật banner thành công']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>
