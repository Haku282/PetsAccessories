<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    
    // 1. Tổng khách hàng (role = 'customer')
    $stmt1 = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $total = $stmt1->fetchColumn();

    // 2. Khách hàng mới (trong 30 ngày qua)
    $stmt2 = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $newClients = $stmt2->fetchColumn();

    // 3. Khách thường xuyên (đã mua từ 3 đơn hàng trở lên)
    $stmt3 = $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT user_id FROM orders 
            GROUP BY user_id 
            HAVING COUNT(order_id) >= 3
        ) AS frequent
    ");
    $frequentClients = $stmt3->fetchColumn();

    echo json_encode([
        'success' => true, 
        'stats' => [
            'total' => $total,
            'new_clients' => $newClients,
            'frequent_clients' => $frequentClients
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>