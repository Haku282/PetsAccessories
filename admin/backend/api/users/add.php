<?php
/**
 * API: Thêm tài khoản mới
 * POST /admin/backend/api/users/add.php
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
    /** @var PDO $pdo */
    $db = $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);

    // Validation
    if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
        exit;
    }

    if (strlen($data['username']) < 3) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username phải có ít nhất 3 ký tự']);
        exit;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
        exit;
    }

    if (strlen($data['password']) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
        exit;
    }

    $db = $pdo;

    // Kiểm tra username đã tồn tại
    $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username đã được sử dụng']);
        exit;
    }

    // Kiểm tra email đã tồn tại
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng']);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

    // Insert
    $sql = "INSERT INTO users (username, email, password, fullname, phone, address, role, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $data['username'],
        $data['email'],
        $hashedPassword,
        $data['fullname'] ?? '',
        $data['phone'] ?? '',
        $data['address'] ?? '',
        $data['role'] ?? 'customer',
        $data['status'] ?? 1
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Thêm tài khoản thành công',
        'user_id' => $db->lastInsertId()
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
