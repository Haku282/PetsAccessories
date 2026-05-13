<?php
/**
 * API: Khóa/Mở khóa tài khoản
 * POST /admin/backend/api/users/toggle-status.php
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
    $lockReason = isset($data['lock_reason']) ? trim($data['lock_reason']) : null;

    // Không cho phép thay đổi chính mình
    if ($userId === (int)$_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không thể thay đổi trạng thái của tài khoản của chính bạn']);
        exit;
    }

    /** @var PDO $pdo */
    $db = $pdo;

    // Auto-create lock_reason column if not exists
    try {
        $checkColumn = $db->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='lock_reason' AND TABLE_SCHEMA=DATABASE()");
        $checkColumn->execute();
        if ($checkColumn->rowCount() === 0) {
            $db->exec("ALTER TABLE users ADD COLUMN lock_reason TEXT DEFAULT NULL");
        }
    } catch (Exception $e) {
        // Silently continue if column creation fails
    }

    // Validation status
    if (!in_array($newStatus, [0, 1])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
        exit;
    }

    // Nếu khóa account (status = 0) phải có lý do
    if ($newStatus === 0 && empty($lockReason)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập lý do khóa tài khoản']);
        exit;
    }

    // Kiểm tra tài khoản tồn tại
    $stmt = $db->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại']);
        exit;
    }

    // Cập nhật trạng thái và lý do khóa
    if ($newStatus === 0) {
        // Khóa tài khoản: cập nhật status = 0, lock_reason
        $sql = "UPDATE users SET status = ?, lock_reason = ? WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$newStatus, $lockReason, $userId]);
    } else {
        // Mở khóa tài khoản: cập nhật status = 1, xóa lock_reason
        $sql = "UPDATE users SET status = ?, lock_reason = NULL WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$newStatus, $userId]);
    }

    $statusText = $newStatus === 1 ? 'mở khóa' : 'khóa';
    echo json_encode([
        'success' => true,
        'message' => 'Đã ' . $statusText . ' tài khoản thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("[Toggle Status Error] " . $e->getMessage() . " - SQL State: " . $e->errorInfo[0]);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("[Toggle Status Error] " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
