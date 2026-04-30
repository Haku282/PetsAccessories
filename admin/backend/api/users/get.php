<?php
/**
 * API: Lấy chi tiết một tài khoản
 * GET /admin/backend/api/users/get.php?id=1
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
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($userId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }

    /** @var PDO $pdo */
    $db = $pdo;
    $sql = "SELECT user_id, username, email, fullname, phone, address, avatar, role, status, created_at FROM users WHERE user_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => $user
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
