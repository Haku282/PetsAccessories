<?php
session_start();
require_once __DIR__ . '/../config/database.php';
/** @var PDO $pdo */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Vui lòng đăng nhập để đánh giá."]);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment'] ?? '');

    if (!$productId || !$rating || $rating < 1 || $rating > 5) {
        echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ."]);
        exit;
    }
    // Check if user has bought product
    $checkStmt = $pdo->prepare("
        SELECT o.order_id
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'completed'
        LIMIT 1
    ");
    $checkStmt->execute([$userId, $productId]);
    $order = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(["status" => "error", "message" => "Bạn cần mua và nhận sản phẩm này trước khi đánh giá."]);
        exit;
    }

    // Check if user has already reviewed
    $checkReviewStmt = $pdo->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
    $checkReviewStmt->execute([$userId, $productId]);
    if ($checkReviewStmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Bạn đã đánh giá sản phẩm này."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, comment, status, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
    if ($stmt->execute([$productId, $userId, $order['order_id'], $rating, $comment])) {
        echo json_encode(["status" => "success", "message" => "Đánh giá của bạn đã được gửi."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi gửi đánh giá."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Phương thức không hợp lệ."]);
}
