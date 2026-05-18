<?php
/**
 * API: Danh sách báo cáo đánh giá
 * GET /admin/backend/api/review_reports/list.php
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    $db = $pdo;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
    $action = isset($_GET['action']) ? trim((string)$_GET['action']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    if ($action === 'stats') {
        $stmt = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS rejected
            FROM review_reports
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => (int)($stats['total'] ?? 0),
                'pending' => (int)($stats['pending'] ?? 0),
                'resolved' => (int)($stats['resolved'] ?? 0),
                'rejected' => (int)($stats['rejected'] ?? 0)
            ]
        ]);
        exit;
    }

    $sql = "
        SELECT rr.report_id, rr.review_id, rr.user_id, rr.reason, rr.status, rr.created_at,
               r.rating, r.comment, r.status AS review_status,
               p.product_name,
               rep.fullname AS reporter_name,
               rev.fullname AS review_user_name
        FROM review_reports rr
        LEFT JOIN reviews r ON rr.review_id = r.review_id
        LEFT JOIN products p ON r.product_id = p.product_id
        LEFT JOIN users rep ON rr.user_id = rep.user_id
        LEFT JOIN users rev ON r.user_id = rev.user_id
        WHERE 1=1
    ";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (rr.reason LIKE ? OR p.product_name LIKE ? OR rep.fullname LIKE ? OR rev.fullname LIKE ?)
        ";
        $term = "%$search%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    if ($status !== '') {
        $sql .= " AND rr.status = ?";
        $params[] = (int)$status;
    }

    $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM ($sql) x");
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    $sql .= " ORDER BY rr.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = array_map(function ($row) {
        return [
            'report_id' => (int)$row['report_id'],
            'review_id' => (int)$row['review_id'],
            'user_id' => (int)$row['user_id'],
            'reason' => $row['reason'],
            'status' => (int)$row['status'],
            'rating' => $row['rating'] !== null ? (int)$row['rating'] : null,
            'comment' => $row['comment'],
            'review_status' => $row['review_status'] !== null ? (int)$row['review_status'] : null,
            'product_name' => $row['product_name'],
            'reporter_name' => $row['reporter_name'],
            'review_user_name' => $row['review_user_name'],
            'created_at' => $row['created_at']
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => (int)ceil($totalRecords / $limit),
            'total_records' => $totalRecords,
            'records_per_page' => $limit
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
