<?php
/**
 * API: Cập nhật bài viết
 * PUT /admin/backend/api/posts/update.php
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

    if (!$data || !isset($data['post_id']) || !is_numeric($data['post_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $postId = (int)$data['post_id'];

    $checkStmt = $db->prepare("SELECT post_id FROM posts WHERE post_id = ?");
    $checkStmt->execute([$postId]);
    if ($checkStmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài viết']);
        exit;
    }

    $errors = [];
    if (isset($data['title']) && empty($data['title'])) $errors['title'] = 'Tiêu đề là bắt buộc';
    if (isset($data['slug']) && empty($data['slug'])) $errors['slug'] = 'Slug là bắt buộc';
    elseif (isset($data['slug'])) {
        $checkStmt = $db->prepare("SELECT post_id FROM posts WHERE slug = ? AND post_id != ?");
        $checkStmt->execute([$data['slug'], $postId]);
        if ($checkStmt->fetchColumn()) $errors['slug'] = 'Slug đã tồn tại';
    }
    if (isset($data['content']) && empty($data['content'])) $errors['content'] = 'Nội dung là bắt buộc';

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Get old post data to check thumbnail change
    $oldPostStmt = $db->prepare("SELECT thumbnail FROM posts WHERE post_id = ?");
    $oldPostStmt->execute([$postId]);
    $oldPost = $oldPostStmt->fetch(PDO::FETCH_ASSOC);

    $updateFields = [];
    $params = [];

    $allowFields = ['title', 'slug', 'content', 'thumbnail', 'category', 'status'];
    foreach ($allowFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
            
            // Delete old thumbnail if changed
            if ($field === 'thumbnail' && !empty($oldPost['thumbnail']) && $data[$field] !== $oldPost['thumbnail']) {
                $uploadDir = __DIR__ . '/../../uploads/posts/';
                $oldImagePath = $uploadDir . $oldPost['thumbnail'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có trường nào để cập nhật']);
        exit;
    }

    $params[] = $postId;
    $sql = "UPDATE posts SET " . implode(", ", $updateFields) . " WHERE post_id = ?";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật bài viết thành công'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>