<?php
/**
 * API: Thêm bài viết mới
 * POST /admin/backend/api/posts/create.php
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $errors = [];
    if (empty($data['title'])) $errors['title'] = 'Tiêu đề là bắt buộc';
    if (empty($data['slug'])) $errors['slug'] = 'Slug là bắt buộc';
    else {
        $checkStmt = $db->prepare("SELECT post_id FROM posts WHERE slug = ?");
        $checkStmt->execute([$data['slug']]);
        if ($checkStmt->fetchColumn()) $errors['slug'] = 'Slug đã tồn tại';
    }
    if (empty($data['content'])) $errors['content'] = 'Nội dung là bắt buộc';

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    $title = $data['title'];
    $slug = $data['slug'];
    $content = $data['content'];
    $thumbnail = $data['thumbnail'] ?? null;
    $category = $data['category'] ?? 'blog';
    $status = isset($data['status']) ? (int)$data['status'] : 1;
    $author_id = $_SESSION['user_id'];

    $stmt = $db->prepare("
        INSERT INTO posts (title, slug, content, thumbnail, category, author_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([$title, $slug, $content, $thumbnail, $category, $author_id, $status]);
    $postId = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Thêm bài viết thành công',
        'post_id' => (int)$postId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>