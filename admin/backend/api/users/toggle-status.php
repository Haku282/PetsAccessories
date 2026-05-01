<?php
/**
 * API: Khóa/Mở khóa tài khoản
 * POST /admin/backend/api/users/toggle-status.php
 */

header('Content-Type: application/json');
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['user_id']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $userId = (int)$data['user_id'];
    $newStatus = (int)$data['status'];

    // Không cho phép thay đổi chính mình
    if ($userId === (int)$_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không thể thay đổi trạng thái của tài khoản của chính bạn']);
        exit;
    }

    /** @var PDO $pdo */
    $db = $pdo;

    // Validation status
    if (!in_array($newStatus, [0, 1])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
        exit;
    }

    $db = $pdo;

    // Kiểm tra tài khoản tồn tại
    $stmt = $db->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại']);
        exit;
    }

    // Cập nhật trạng thái
    $sql = "UPDATE users SET status = ? WHERE user_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$newStatus, $userId]);

    $statusText = $newStatus === 1 ? 'mở khóa' : 'khóa';
    echo json_encode([
        'success' => true,
        'message' => 'Đã ' . $statusText . ' tài khoản thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
