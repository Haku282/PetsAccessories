<?php
/**
 * API: Cập nhật trang
 * PUT /admin/backend/api/pages/update.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận PUT request']);
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

    // Kiểm tra ID
    if (!isset($data['page_id']) || !is_numeric($data['page_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID trang không hợp lệ']);
        exit;
    }

    $pageId = (int)$data['page_id'];

    // Kiểm tra trang tồn tại
    $checkStmt = $db->prepare("SELECT page_id FROM pages WHERE page_id = ?");
    $checkStmt->execute([$pageId]);
    if ($checkStmt->fetchColumn() === false) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy trang']);
        exit;
    }

    // Validate dữ liệu
    $errors = [];
    if (isset($data['page_title']) && empty($data['page_title'])) {
        $errors['page_title'] = 'Tiêu đề trang là bắt buộc';
    }
    if (isset($data['page_slug']) && empty($data['page_slug'])) {
        $errors['page_slug'] = 'Slug trang là bắt buộc';
    } elseif (isset($data['page_slug'])) {
        // Kiểm tra slug đã tồn tại (trừ chính nó)
        $checkStmt = $db->prepare("SELECT page_id FROM pages WHERE page_slug = ? AND page_id != ?");
        $checkStmt->execute([$data['page_slug'], $pageId]);
        if ($checkStmt->fetchColumn()) {
            $errors['page_slug'] = 'Slug trang đã tồn tại';
        }
    }
    if (isset($data['page_content']) && empty($data['page_content'])) {
        $errors['page_content'] = 'Nội dung trang là bắt buộc';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors]);
        exit;
    }

    // Chuẩn bị dữ liệu
    $page_title = isset($data['page_title']) ? $data['page_title'] : null;
    $page_slug = isset($data['page_slug']) ? $data['page_slug'] : null;
    $page_content = isset($data['page_content']) ? $data['page_content'] : null;

    // Xây dựng query cập nhật động
    $updateFields = [];
    $params = [];

    if ($page_title !== null) {
        $updateFields[] = "page_title = ?";
        $params[] = $page_title;
    }
    if ($page_slug !== null) {
        $updateFields[] = "page_slug = ?";
        $params[] = $page_slug;
    }
    if ($page_content !== null) {
        $updateFields[] = "page_content = ?";
        $params[] = $page_content;
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có trường nào để cập nhật']);
        exit;
    }

    $params[] = $pageId;
    $sql = "UPDATE pages SET " . implode(", ", $updateFields) . " WHERE page_id = ?";

    $stmt = $db->prepare($sql);
    $success = $stmt->execute($params);

    if (!$success) {
        throw new Exception('Không thể cập nhật trang');
    }

    // Log activity
    error_log("[" . date('Y-m-d H:i:s') . "] Admin " . $_SESSION['user_id'] . " cập nhật trang ID: $pageId");

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật trang thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>