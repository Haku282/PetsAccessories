<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    
    // Nhận từ khóa tìm kiếm (nếu có)
    $search = $_GET['search'] ?? '';
    
    // Chuẩn bị điều kiện tìm kiếm
    $searchCondition = "";
    $params = [];
    
    if (!empty($search)) {
        // Lọc theo Tên, Email, HOẶC Số điện thoại
        $searchCondition = " AND (u.fullname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?) ";
        $searchParam = "%$search%";
        $params = [$searchParam, $searchParam, $searchParam];
    }

    // Câu lệnh SQL (Có ghép thêm điều kiện tìm kiếm)
    $sql = "
        SELECT 
            u.user_id as id, 
            u.fullname as name, 
            u.email, 
            u.phone, 
            u.created_at,
            COUNT(o.order_id) as total_orders,
            SUM(o.total_price) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id AND o.order_status != 'cancelled'
        WHERE u.role = 'customer' $searchCondition
        GROUP BY u.user_id
        ORDER BY u.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Truyền tham số an toàn để chống SQL Injection
    
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'clients' => $clients]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>