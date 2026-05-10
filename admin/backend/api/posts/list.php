<?php
/**
 * API: Lấy danh sách bài viết
 * GET /admin/backend/api/posts/list.php
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
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Xây dựng query
    $sql = "
        SELECT p.*, u.fullname as author_name
        FROM posts p
        LEFT JOIN users u ON p.author_id = u.user_id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($category)) {
        $sql .= " AND p.category = ?";
        $params[] = $category;
    }

    if ($status !== '') {
        $sql .= " AND p.status = ?";
        $params[] = (int)$status;
    }

    // Đếm tổng số bản ghi
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as counted";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy dữ liệu phân trang
    $sql .= " ORDER BY p.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
    
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dữ liệu trả về
    $formattedPosts = [];
    foreach ($posts as $post) {
        $formattedPosts[] = [
            'post_id' => (int)$post['post_id'],
            'title' => $post['title'],
            'slug' => $post['slug'],
            'content' => $post['content'],
            'thumbnail' => $post['thumbnail'],
            'category' => $post['category'],
            'author_id' => (int)$post['author_id'],
            'author_name' => $post['author_name'],
            'status' => (int)$post['status'],
            'created_at' => $post['created_at']
        ];
    }

    $totalPages = ceil($totalRecords / $limit);

    echo json_encode([
        'success' => true,
        'data' => $formattedPosts,
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
