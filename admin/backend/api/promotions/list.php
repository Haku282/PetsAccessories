<?php
/**
 * API: Lấy danh sách khuyến mãi
 * GET /admin/backend/api/promotions/list.php
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
require_once __DIR__ . '/../../utils/products_helper.php';

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
        SELECT p.*, COUNT(pr.product_id) as product_count
        FROM promotions p
        LEFT JOIN products pr ON p.promotion_id = pr.promotion_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (p.promotion_name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($status !== '') {
        $sql .= " AND p.status = ?";
        $params[] = (int)$status;
    }

    $sql .= " GROUP BY p.promotion_id";

    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as counted";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy dữ liệu phân trang
    $sql .= " ORDER BY p.promotion_id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu trả về
    $formattedPromotions = [];
    foreach ($promotions as $promotion) {
        $formattedPromotions[] = [
            'promotion_id' => (int)$promotion['promotion_id'],
            'promotion_name' => $promotion['promotion_name'],
            'description' => $promotion['description'],
            'discount_percent' => (int)$promotion['discount_percent'],
            'start_date' => $promotion['start_date'],
            'end_date' => $promotion['end_date'],
            'status' => (int)$promotion['status'],
            'product_count' => (int)$promotion['product_count'],
            'created_at' => $promotion['created_at']
        ];
    }

    $totalPages = ceil($totalRecords / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formattedPromotions,
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
