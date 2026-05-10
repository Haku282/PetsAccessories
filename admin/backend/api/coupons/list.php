<?php
/**
 * API: Lấy danh sách mã giảm giá
 * GET /admin/backend/api/coupons/list.php
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
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Xây dựng query
    $sql = "
        SELECT c.*
        FROM coupons c
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (c.code LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
    }

    if ($status !== '') {
        $sql .= " AND c.status = ?";
        $params[] = (int)$status;
    }

    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as counted";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy dữ liệu phân trang
    $sql .= " ORDER BY c.coupon_id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu trả về
    $formattedCoupons = [];
    foreach ($coupons as $coupon) {
        $formattedCoupons[] = [
            'coupon_id' => (int)$coupon['coupon_id'],
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => (float)$coupon['discount_value'],
            'min_order_value' => (float)$coupon['min_order_value'],
            'max_discount' => $coupon['max_discount'] !== null ? (float)$coupon['max_discount'] : null,
            'usage_limit' => $coupon['usage_limit'] !== null ? (int)$coupon['usage_limit'] : null,
            'used_count' => (int)$coupon['used_count'],
            'expiry_date' => $coupon['expiry_date'],
            'status' => (int)$coupon['status'],
            'usage_count' => (int)$coupon['used_count'],
            'created_at' => $coupon['created_at']
        ];
    }

    $totalPages = ceil($totalRecords / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formattedCoupons,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
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
