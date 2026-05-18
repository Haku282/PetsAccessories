<?php
// Prevent any output before JSON
ob_start();

// Check session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify admin access
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Clear any output and set JSON header
ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

// Correct path to database config
// Current: admin/backend/api/return_requests/update.php
// Need: backend/config/database.php
// Go up 4 levels: return_requests -> api -> backend -> admin -> root, then backend/config
$rootDir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
$dbConfigPath = $rootDir . '/backend/config/database.php';

if (!file_exists($dbConfigPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi: Không tìm thấy file config',
        'debug' => [
            'current_file' => __FILE__,
            'root_dir' => $rootDir,
            'looking_for' => $dbConfigPath,
            'exists' => file_exists($dbConfigPath)
        ]
    ]);
    exit;
}

require_once $dbConfigPath;

$db = $pdo ?? null;

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    try {
        $returnId = filter_input(INPUT_POST, 'return_id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        $adminNote = filter_input(INPUT_POST, 'admin_note', FILTER_SANITIZE_STRING);

        if (!$returnId || !$status) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $allowedStatuses = ['pending', 'approved', 'rejected', 'completed'];
        if (!in_array($status, $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
            exit;
        }

        // Update status
        if ($adminNote) {
            $stmt = $db->prepare('
                UPDATE return_requests 
                SET status = ?, admin_note = ? 
                WHERE return_id = ?
            ');
            $stmt->execute([$status, $adminNote, $returnId]);
        } else {
            $stmt = $db->prepare('
                UPDATE return_requests 
                SET status = ? 
                WHERE return_id = ?
            ');
            $stmt->execute([$status, $returnId]);
        }

        if ($stmt->rowCount() > 0) {
            $statusText = [
                'pending' => 'Đang Xử Lý',
                'approved' => 'Đã Phê Duyệt',
                'rejected' => 'Đã Từ Chối',
                'completed' => 'Đã Hoàn Thành'
            ];
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành ' . ($statusText[$status] ?? $status) . ' thành công'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không tìm thấy']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
