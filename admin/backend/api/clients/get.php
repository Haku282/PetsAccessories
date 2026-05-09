<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $id = (int)($_GET['id'] ?? 0);

    // Lấy thông tin khách
    $stmtUser = $pdo->prepare("SELECT user_id as id, fullname as name, email, phone, address FROM users WHERE user_id = ?");
    $stmtUser->execute([$id]);
    $client = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        exit(json_encode(['success' => false, 'message' => 'Khách hàng không tồn tại']));
    }

    // Lấy lịch sử mua (dùng đúng cột order_status)
    $stmtOrders = $pdo->prepare("SELECT order_id, total_price, order_status as status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmtOrders->execute([$id]);
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'client' => $client,
        'orders' => $orders
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>