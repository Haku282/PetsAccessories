<?php
/**
 * API: Cập nhật tài khoản
 * PUT /admin/backend/api/users/update.php
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

    // Validation
    if (empty($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID tài khoản không hợp lệ']);
        exit;
    }

    $userId = (int)$data['user_id'];
    /** @var PDO $pdo */
    $db = $pdo;

    // Kiểm tra tài khoản tồn tại
    $stmt = $db->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại']);
        exit;
    }

    // Kiểm tra email nếu thay đổi
    if (!empty($data['email'])) {
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng']);
            exit;
        }
    }

    // Cập nhật
    $updateFields = [];
    $updateParams = [];

    if (!empty($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
            exit;
        }
        $updateFields[] = "email = ?";
        $updateParams[] = $data['email'];
    }

    if (!empty($data['fullname'])) {
        $updateFields[] = "fullname = ?";
        $updateParams[] = $data['fullname'];
    }

    if (!empty($data['phone'])) {
        $updateFields[] = "phone = ?";
        $updateParams[] = $data['phone'];
    }

    if (!empty($data['address'])) {
        $updateFields[] = "address = ?";
        $updateParams[] = $data['address'];
    }

    if (isset($data['role']) && in_array($data['role'], ['admin', 'customer'])) {
        $updateFields[] = "role = ?";
        $updateParams[] = $data['role'];
    }

    if (isset($data['status']) && in_array($data['status'], [0, 1])) {
        $updateFields[] = "status = ?";
        $updateParams[] = $data['status'];
    }

    if (!empty($updateFields)) {
        $updateParams[] = $userId;
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($updateParams);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật tài khoản thành công'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
