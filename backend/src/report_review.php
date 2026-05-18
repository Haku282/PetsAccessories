<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để báo cáo review.']);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
$reason = trim($_POST['reason'] ?? '');

if (!$reviewId || $reason === '') {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu báo cáo không hợp lệ.']);
    exit;
}

try {
    if (!($pdo instanceof PDO)) {
        throw new PDOException('Không thể kết nối cơ sở dữ liệu.');
    }

    $checkStmt = $pdo->prepare('
        SELECT review_id, user_id
        FROM reviews
        WHERE review_id = ?
        LIMIT 1
    ');
    $checkStmt->execute([(int) $reviewId]);
    $review = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        echo json_encode(['status' => 'error', 'message' => 'Review không tồn tại.']);
        exit;
    }

    if ((int) $review['user_id'] === $userId) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không thể báo cáo review của chính mình.']);
        exit;
    }

    $duplicateStmt = $pdo->prepare('
        SELECT report_id
        FROM review_reports
        WHERE review_id = ? AND user_id = ?
        LIMIT 1
    ');
    $duplicateStmt->execute([(int) $reviewId, $userId]);
    if ($duplicateStmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn đã báo cáo review này rồi.']);
        exit;
    }

    $insertStmt = $pdo->prepare('
        INSERT INTO review_reports (review_id, user_id, reason, status)
        VALUES (?, ?, ?, 0)
    ');
    $insertStmt->execute([(int) $reviewId, $userId, $reason]);

    echo json_encode(['status' => 'success', 'message' => 'Đã gửi báo cáo review.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Không thể gửi báo cáo: ' . $e->getMessage()]);
}