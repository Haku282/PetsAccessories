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
// Current: admin/backend/api/return_requests/get.php
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

$action = $_GET['action'] ?? '';

// Get stats
if ($action === 'stats') {
    try {
        $stmt = $db->prepare('
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed
            FROM return_requests
        ');
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => (int) ($stats['total'] ?? 0),
                'pending' => (int) ($stats['pending'] ?? 0),
                'approved' => (int) ($stats['approved'] ?? 0),
                'rejected' => (int) ($stats['rejected'] ?? 0),
                'completed' => (int) ($stats['completed'] ?? 0)
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
    }
    exit;
}

// Get single request
$requestId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($requestId) {
    try {
        $stmt = $db->prepare('
            SELECT rr.*, u.fullname, u.email, u.phone
            FROM return_requests rr
            LEFT JOIN users u ON rr.user_id = u.user_id
            WHERE rr.return_id = ?
            LIMIT 1
        ');
        $stmt->execute([$requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($request) {
            echo json_encode(['success' => true, 'data' => $request]);
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

// Get all requests with filters
try {
    $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
    $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);

    $query = '
        SELECT rr.*, u.fullname, u.email, u.phone
        FROM return_requests rr
        LEFT JOIN users u ON rr.user_id = u.user_id
        WHERE 1=1
    ';
    $params = [];

    if ($status) {
        $query .= ' AND rr.status = ?';
        $params[] = $status;
    }

    if ($type) {
        $query .= ' AND rr.request_type = ?';
        $params[] = $type;
    }

    if ($search) {
        $query .= ' AND (u.fullname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    $query .= ' ORDER BY rr.created_at DESC';

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $requests,
        'count' => count($requests)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}
