<?php
/**
 * API: Lấy danh sách tất cả tài khoản
 * GET /admin/backend/api/users/list.php
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
    /** @var PDO $pdo */
    $db = $pdo;
    
    // Lấy các tham số filter
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Validate page and limit
    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 100) $limit = 10;

    // Xây dựng query base
    $whereClause = " WHERE 1=1";
    $params = [];

    if (!empty($role) && in_array($role, ['admin', 'customer'])) {
        $whereClause .= " AND role = ?";
        $params[] = $role;
    }

    if ($status !== '' && in_array((int)$status, [0, 1])) {
        $whereClause .= " AND status = ?";
        $params[] = (int)$status;
    }

    if (!empty($search)) {
        $whereClause .= " AND (username LIKE ? OR email LIKE ? OR fullname LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Đếm tổng số record
    $countSql = "SELECT COUNT(*) as total FROM users" . $whereClause;
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy data
    $sql = "SELECT user_id, username, email, fullname as full_name, phone, role, status, created_at FROM users" . $whereClause;
    $sql .= " ORDER BY created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $users,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_records' => $total,
            'records_per_page' => $limit
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
