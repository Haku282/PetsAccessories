<?php
/**
 * API: Cập nhật trạng thái báo cáo đánh giá
 * PUT /admin/backend/api/review_reports/update-status.php
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận PUT request']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    $db = $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['report_id']) || !is_numeric($data['report_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $reportId = (int)$data['report_id'];
    $status = isset($data['status']) ? (int)$data['status'] : 1;
    $action = isset($data['action']) ? trim($data['action']) : '';
    $note = isset($data['admin_note']) ? trim($data['admin_note']) : null;

    if (!in_array($status, [0, 1, 2], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
        exit;
    }

    $check = $db->prepare('SELECT review_id FROM review_reports WHERE report_id = ?');
    $check->execute([$reportId]);
    $reviewId = $check->fetchColumn();
    if (!$reviewId) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy báo cáo']);
        exit;
    }

    $db->beginTransaction();

    $stmt = $db->prepare('UPDATE review_reports SET status = ? WHERE report_id = ?');
    $stmt->execute([$status, $reportId]);

    if ($action === 'hide_review') {
        $db->prepare('UPDATE reviews SET status = 0 WHERE review_id = ?')->execute([$reviewId]);
    } elseif ($action === 'show_review') {
        $db->prepare('UPDATE reviews SET status = 1 WHERE review_id = ?')->execute([$reviewId]);
    } elseif ($action === 'delete_review') {
        $db->prepare('DELETE FROM reviews WHERE review_id = ?')->execute([$reviewId]);
        $db->prepare('DELETE FROM review_reports WHERE review_id = ?')->execute([$reviewId]);
    }

    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Cập nhật báo cáo thành công']);
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
