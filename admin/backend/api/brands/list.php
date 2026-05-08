<?php
/**
 * API: Lấy danh sách thương hiệu
 * GET /admin/backend/api/brands/list.php
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
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = ($page - 1) * $limit;

    // Xây dựng query
    $sql = "
        SELECT b.*, COUNT(DISTINCT p.product_id) as product_count
        FROM brands b
        LEFT JOIN products p ON b.brand_id = p.brand_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (b.brand_name LIKE ? OR b.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " GROUP BY b.brand_id";

    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(*) as total FROM brands b WHERE 1=1";
    $countParams = [];
    
    if (!empty($search)) {
        $countSql .= " AND (b.brand_name LIKE ? OR b.description LIKE ?)";
        $searchTerm = "%$search%";
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
    }

    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy dữ liệu
    $sql .= " ORDER BY b.brand_name LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu
    $formattedData = [];
    foreach ($brands as $brand) {
        $formattedData[] = [
            'brand_id' => (int)$brand['brand_id'],
            'brand_name' => $brand['brand_name'],
            'brand_logo' => $brand['brand_logo'],
            'description' => $brand['description'],
            'product_count' => (int)$brand['product_count']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $formattedData,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$totalRecords,
            'pages' => ceil($totalRecords / $limit)
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
