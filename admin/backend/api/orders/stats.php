<?php
/**
 * API: Thống kê đơn hàng theo trạng thái
 * GET /admin/backend/api/orders/stats.php
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
    /** @var PDO $pdo */
    $db = $pdo;

    $stmt = $db->query("
        SELECT order_status, COUNT(*) AS total
        FROM orders
        GROUP BY order_status
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stats = [
        'pending' => 0,
        'confirmed' => 0,
        'shipping' => 0,
        'completed' => 0,
        'cancelled' => 0,
        'total' => 0
    ];

    foreach ($rows as $row) {
        $status = (string)$row['order_status'];
        $count = (int)$row['total'];
        if (array_key_exists($status, $stats)) {
            $stats[$status] = $count;
        }
        $stats['total'] += $count;
    }

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
