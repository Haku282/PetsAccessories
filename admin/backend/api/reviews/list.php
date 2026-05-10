<?php
/**
 * API: Lấy danh sách đánh giá & bình luận
 * GET /admin/backend/api/reviews/list.php
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
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : '';
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : '';
    $rating = isset($_GET['rating']) ? (int)$_GET['rating'] : '';
    $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Xây dựng query
    $sql = "
        SELECT r.*, u.fullname as user_name, p.product_name
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.user_id
        LEFT JOIN products p ON r.product_id = p.product_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (r.comment LIKE ? OR u.fullname LIKE ? OR p.product_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($product_id)) {
        $sql .= " AND r.product_id = ?";
        $params[] = $product_id;
    }

    if (!empty($user_id)) {
        $sql .= " AND r.user_id = ?";
        $params[] = $user_id;
    }

    if (!empty($rating)) {
        $sql .= " AND r.rating = ?";
        $params[] = $rating;
    }

    if ($status !== '') {
        $sql .= " AND r.status = ?";
        $params[] = (int)$status;
    }

    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as counted";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy dữ liệu phân trang
    $sql .= " ORDER BY r.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu trả về
    $formattedReviews = [];
    foreach ($reviews as $review) {
        $formattedReviews[] = [
            'review_id' => (int)$review['review_id'],
            'product_id' => (int)$review['product_id'],
            'product_name' => $review['product_name'],
            'user_id' => (int)$review['user_id'],
            'user_name' => $review['user_name'],
            'order_id' => $review['order_id'] ? (int)$review['order_id'] : null,
            'rating' => (int)$review['rating'],
            'comment' => $review['comment'],
            'status' => (int)$review['status'],
            'created_at' => $review['created_at']
        ];
    }

    $totalPages = ceil($totalRecords / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formattedReviews,
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
