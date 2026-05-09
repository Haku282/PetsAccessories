<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = $pdo ?? null;

// Kiểm tra user đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: /PetsAccessories/frontend/components/login.php?redirect=' . urlencode('/PetsAccessories/frontend/components/orders.php'));
    exit;
}

$user_id = $_SESSION['user_id'];
$orders = [];
$error = '';

if (!($db instanceof PDO)) {
    $error = 'Không thể kết nối cơ sở dữ liệu.';
} else {
    try {
        $stmt = $db->prepare("
            SELECT order_id, total_price, shipping_fee, discount_amount, order_status, payment_method, payment_status, created_at
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Lỗi khi lấy dữ liệu đơn hàng: ' . $e->getMessage();
    }
}

function mapOrderStatus(string $status)
{
    $map = [
        'pending' => '<span class="status-badge status-pending">Chờ xác nhận</span>',
        'shipping' => '<span class="status-badge status-shipping">Đang giao</span>',
        'completed' => '<span class="status-badge status-completed">Hoàn thành</span>',
        'cancelled' => '<span class="status-badge status-cancelled">Đã hủy</span>',
    ];

    $statusKey = strtolower((string) $status);
    return $map[$statusKey] ?? '<span class="status-badge">' . htmlspecialchars((string) $status) . '</span>';
}

function mapPaymentStatus(string $status)
{
    $map = [
        'unpaid' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền',
    ];
    $statusKey = strtolower((string) $status);
    return $map[$statusKey] ?? htmlspecialchars((string) $status);
}
