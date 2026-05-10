<?php
/**
 * API: Lấy danh sách khu vực giao hàng
 * GET /admin/backend/api/shipping_zones/list.php
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
        SELECT sz.*
        FROM shipping_zones sz
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (sz.zone_name LIKE ? OR sz.estimated_delivery LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($status !== '') {
        $sql .= " AND sz.status = ?";
        $params[] = (int)$status;
    }

    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as counted";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy dữ liệu phân trang
    $sql .= " ORDER BY sz.zone_id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $shippingZones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu trả về
    $formattedZones = [];
    foreach ($shippingZones as $zone) {
        $formattedZones[] = [
            'zone_id' => (int)$zone['zone_id'],
            'zone_name' => $zone['zone_name'],
            'shipping_fee' => (float)$zone['shipping_fee'],
            'estimated_delivery' => $zone['estimated_delivery'],
            'status' => (int)$zone['status']
        ];
    }

    $totalPages = ceil($totalRecords / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formattedZones,
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
