<?php
/**
 * API: Thêm trang mới
 * POST /admin/backend/api/pages/create.php
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

try {
    /** @var PDO $pdo */
    $db = $pdo;
    
    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    // Validate dữ liệu
    $errors = [];
    if (empty($data['page_title'])) {
        $errors['page_title'] = 'Tiêu đề trang là bắt buộc';
    }
    if (empty($data['page_slug'])) {
        $errors['page_slug'] = 'Slug trang là bắt buộc';
    } else {
        // Kiểm tra slug đã tồn tại
        $checkStmt = $db->prepare("SELECT page_id FROM pages WHERE page_slug = ?");
        $checkStmt->execute([$data['page_slug']]);
        if ($checkStmt->fetchColumn()) {
            $errors['page_slug'] = 'Slug trang đã tồn tại';
        }
    }
    if (empty($data['page_content'])) {
        $errors['page_content'] = 'Nội dung trang là bắt buộc';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Chuẩn bị dữ liệu
    $page_title = $data['page_title'];
    $page_slug = $data['page_slug'];
    $page_content = $data['page_content'];

    // Thêm trang vào database
    $stmt = $db->prepare("
        INSERT INTO pages (page_title, page_slug, page_content, updated_at)
        VALUES (?, ?, ?, NOW())
    ");

    $success = $stmt->execute([
        $page_title,
        $page_slug,
        $page_content
    ]);

    if (!$success) {
        throw new Exception('Không thể thêm trang');
    }

    $pageId = $db->lastInsertId();

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " thêm trang ID: $pageId");

    echo json_encode([
        'success' => true,
        'message' => 'Thêm trang thành công',
        'page_id' => (int)$pageId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>