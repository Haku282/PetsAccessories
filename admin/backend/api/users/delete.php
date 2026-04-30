<?php
/**
 * API: Xóa tài khoản
 * DELETE /admin/backend/api/users/delete.php
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

    if (empty($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID tài khoản không hợp lệ']);
        exit;
    }

    $userId = (int)$data['user_id'];

    // Không cho phép xóa chính mình
    if ($userId === (int)$_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản của chính bạn']);
        exit;
    }

    /** @var PDO $pdo */
    $db = $pdo;

    // Kiểm tra tài khoản tồn tại
    $stmt = $db->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại']);
        exit;
    }

    // Xóa tài khoản
    $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Xóa tài khoản thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
