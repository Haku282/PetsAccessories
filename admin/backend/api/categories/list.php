<?php
/**
 * API: Lấy danh sách danh mục
 * GET /admin/backend/api/categories/list.php
 */

header('Content-Type: application/json');

// Kiểm tra session
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
    
    // Lấy các tham số
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $parent_id = isset($_GET['parent_id']) ? $_GET['parent_id'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = ($page - 1) * $limit;

    // Xây dựng query
    $sql = "
        SELECT c.*, 
               parent.category_name as parent_name,
               COUNT(DISTINCT p.product_id) as product_count
        FROM categories c
        LEFT JOIN categories parent ON c.parent_id = parent.category_id
        LEFT JOIN products p ON c.category_id = p.category_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND c.category_name LIKE ?";
        $params[] = "%$search%";
    }

    if ($parent_id !== '') {
        if ($parent_id === 'null') {
            $sql .= " AND c.parent_id IS NULL";
        } else {
            $sql .= " AND c.parent_id = ?";
            $params[] = (int)$parent_id;
        }
    }

    if ($status !== '') {
        $sql .= " AND c.status = ?";
        $params[] = (int)$status;
    }

    $sql .= " GROUP BY c.category_id";

    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(DISTINCT c.category_id) as total FROM categories c
                 LEFT JOIN categories parent ON c.parent_id = parent.category_id
                 WHERE 1=1";
    
    $countParams = [];
    if (!empty($search)) {
        $countSql .= " AND c.category_name LIKE ?";
        $countParams[] = "%$search%";
    }
    if ($parent_id !== '') {
        if ($parent_id === 'null') {
            $countSql .= " AND c.parent_id IS NULL";
        } else {
            $countSql .= " AND c.parent_id = ?";
            $countParams[] = (int)$parent_id;
        }
    }
    if ($status !== '') {
        $countSql .= " AND c.status = ?";
        $countParams[] = (int)$status;
    }

    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy dữ liệu
    $sql .= " ORDER BY c.parent_id, c.category_name LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu
    $formattedData = [];
    foreach ($categories as $cat) {
        $formattedData[] = [
            'category_id' => (int)$cat['category_id'],
            'category_name' => $cat['category_name'],
            'parent_id' => $cat['parent_id'] ? (int)$cat['parent_id'] : null,
            'parent_name' => $cat['parent_name'],
            'pet_type' => $cat['pet_type'],
            'status' => (int)$cat['status'],
            'product_count' => (int)$cat['product_count'],
            'status_label' => $cat['status'] == 1 ? 'Kích hoạt' : 'Vô hiệu',
            'status_color' => $cat['status'] == 1 ? '#10b981' : '#ef4444'
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
