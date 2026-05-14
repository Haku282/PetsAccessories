<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
/** @var PDO $pdo */
// Debug logging
error_log('=== WISHLIST API DEBUG ===');
error_log('POST data: ' . json_encode($_POST));
error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'NOT SET'));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để sử dụng tính năng này.']);
    exit;
}

$action = $_POST['action'] ?? '';
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$userId = $_SESSION['user_id'];

error_log('Action: ' . $action);
error_log('Product ID (raw): ' . ($_POST['product_id'] ?? 'NOT SET'));
error_log('Product ID (int): ' . $productId);
error_log('User ID: ' . $userId);

if (!$productId || $productId <= 0) {
    error_log('VALIDATION FAILED: Product ID is invalid');
    echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không hợp lệ. (ID: ' . $productId . ')']);
    exit;
}

if ($action === 'toggle') {
    try {
        $stmt = $pdo->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        if ($stmt->fetch()) {
            // Remove
            $del = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
            $del->execute([$userId, $productId]);
            error_log('WISHLIST: Removed product ' . $productId . ' for user ' . $userId);
            echo json_encode(['status' => 'success', 'action' => 'removed', 'message' => 'Đã xóa khỏi danh sách yêu thích.']);
        } else {
            // Add
            $add = $pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
            $add->execute([$userId, $productId]);
            error_log('WISHLIST: Added product ' . $productId . ' for user ' . $userId);
            echo json_encode(['status' => 'success', 'action' => 'added', 'message' => 'Đã thêm vào danh sách yêu thích.']);
        }
    } catch (PDOException $e) {
        error_log('DB ERROR: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
    }
} else {
    error_log('INVALID ACTION: ' . $action);
    echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ.']);
}
