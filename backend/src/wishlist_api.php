<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để sử dụng tính năng này.']);
    exit;
}

$action = $_POST['action'] ?? '';
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$userId = $_SESSION['user_id'];

if (!$productId) {
    echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không hợp lệ.']);
    exit;
}

if ($action === 'toggle') {
    $stmt = $pdo->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    if ($stmt->fetch()) {
        // Remove
        $del = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        $del->execute([$userId, $productId]);
        echo json_encode(['status' => 'success', 'action' => 'removed', 'message' => 'Đã xóa khỏi danh sách yêu thích.']);
    } else {
        // Add
        $add = $pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $add->execute([$userId, $productId]);
        echo json_encode(['status' => 'success', 'action' => 'added', 'message' => 'Đã thêm vào danh sách yêu thích.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ.']);
}
