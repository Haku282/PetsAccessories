<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = $pdo ?? null;

if (!isset($_SESSION['user_id'])) {
    header('Location: /PetsAccessories/frontend/components/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/PetsAccessories/frontend/components/orders.php'));
    exit;
}

$userId = (int) $_SESSION['user_id'];
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    header('Location: /PetsAccessories/frontend/components/orders.php');
    exit;
}

$error = '';
$success = '';
$order = null;
$orderItems = [];
$returnRequests = [];

function mapOrderStatusLabel(string $status): string
{
    $map = [
        'pending' => '<span class="status-badge status-pending">Chờ xác nhận</span>',
        'shipping' => '<span class="status-badge status-shipping">Đang giao</span>',
        'completed' => '<span class="status-badge status-completed">Hoàn thành</span>',
        'cancelled' => '<span class="status-badge status-cancelled">Đã hủy</span>',
    ];

    $key = strtolower(trim($status));
    return $map[$key] ?? '<span class="status-badge">' . htmlspecialchars($status) . '</span>';
}

function mapPaymentStatusText(string $status): string
{
    $map = [
        'unpaid' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
        'refunded' => 'Đã hoàn tiền',
    ];

    $key = strtolower(trim($status));
    return $map[$key] ?? htmlspecialchars($status);
}

try {
    if (!($db instanceof PDO)) {
        $error = 'Không thể kết nối cơ sở dữ liệu.';
    } else {
        $stmt = $db->prepare('
            SELECT o.*, u.fullname, u.email, u.phone
            FROM orders o
            LEFT JOIN users u ON u.user_id = o.user_id
            WHERE o.order_id = ? AND o.user_id = ?
            LIMIT 1
        ');
        $stmt->execute([(int) $orderId, (int) $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$order) {
            $error = 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn hàng này.';
        }
    }
} catch (PDOException $e) {
    $error = 'Lỗi hệ thống: ' . $e->getMessage();
}

// Handle return/exchange request submission
if ($order && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_return_request') {
        $requestType = trim($_POST['request_type'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        $allowedTypes = ['return', 'exchange'];
        if (!in_array($requestType, $allowedTypes, true)) {
            $error = 'Loại yêu cầu không hợp lệ.';
        } elseif ($reason === '') {
            $error = 'Vui lòng nhập lý do đổi/trả.';
        } else {
            $orderStatus = strtolower((string) ($order['order_status'] ?? ''));
            if ($orderStatus !== 'completed') {
                $error = 'Chỉ hỗ trợ đổi/trả khi đơn hàng đã hoàn thành.';
            } else {
                try {
                    if (!($db instanceof PDO)) {
                        throw new PDOException('Không thể kết nối cơ sở dữ liệu.');
                    }

                    $stmt = $db->prepare('INSERT INTO return_requests (order_id, user_id, request_type, reason, status) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([
                        (int) $orderId,
                        (int) $userId,
                        $requestType,
                        $reason,
                        'pending',
                    ]);

                    $success = 'Đã gửi yêu cầu đổi/trả. Shop sẽ liên hệ xác nhận sớm.';
                } catch (PDOException $e) {
                    $error = 'Không thể tạo yêu cầu đổi/trả: ' . $e->getMessage();
                }
            }
        }
    }
}

// Load order items (schema-aware)
if ($order) {
    try {
        if (!($db instanceof PDO)) {
            throw new PDOException('Không thể kết nối cơ sở dữ liệu.');
        }

        $columns = [];
        try {
            $colsStmt = $db->query('SHOW COLUMNS FROM order_items');
            $cols = $colsStmt ? $colsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($cols as $col) {
                if (isset($col['Field'])) {
                    $columns[(string) $col['Field']] = true;
                }
            }
        } catch (PDOException $e) {
            $columns = [];
        }

        if (!empty($columns['price_at_purchase'])) {
            $stmt = $db->prepare('
                SELECT oi.product_id, oi.quantity, oi.price_at_purchase AS unit_price,
                       p.product_name, p.thumbnail
                FROM order_items oi
                LEFT JOIN products p ON p.product_id = oi.product_id
                WHERE oi.order_id = ?
                ORDER BY oi.order_item_id ASC
            ');
            $stmt->execute([(int) $orderId]);
            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($orderItems as &$item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $unit = (float) ($item['unit_price'] ?? 0);
                $item['line_total'] = $qty * $unit;
            }
            unset($item);
        } elseif (!empty($columns['unit_price']) && !empty($columns['line_total'])) {
            $stmt = $db->prepare('
                SELECT oi.product_id, oi.quantity, oi.unit_price, oi.line_total,
                       p.product_name, p.thumbnail
                FROM order_items oi
                LEFT JOIN products p ON p.product_id = oi.product_id
                WHERE oi.order_id = ?
                ORDER BY oi.order_item_id ASC
            ');
            $stmt->execute([(int) $orderId]);
            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif (!empty($columns['unit_price'])) {
            $stmt = $db->prepare('
                SELECT oi.product_id, oi.quantity, oi.unit_price,
                       p.product_name, p.thumbnail
                FROM order_items oi
                LEFT JOIN products p ON p.product_id = oi.product_id
                WHERE oi.order_id = ?
                ORDER BY oi.order_item_id ASC
            ');
            $stmt->execute([(int) $orderId]);
            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($orderItems as &$item) {
                $qty = (int) ($item['quantity'] ?? 0);
                $unit = (float) ($item['unit_price'] ?? 0);
                $item['line_total'] = $qty * $unit;
            }
            unset($item);
        } else {
            $stmt = $db->prepare('
                SELECT oi.product_id, oi.quantity,
                       p.product_name, p.thumbnail
                FROM order_items oi
                LEFT JOIN products p ON p.product_id = oi.product_id
                WHERE oi.order_id = ?
                ORDER BY oi.order_item_id ASC
            ');
            $stmt->execute([(int) $orderId]);
            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($orderItems as &$item) {
                $item['unit_price'] = 0;
                $item['line_total'] = 0;
            }
            unset($item);
        }
    } catch (PDOException $e) {
        $orderItems = [];
    }

    // Load return requests (if table exists)
    try {
        if (!($db instanceof PDO)) {
            throw new PDOException('Không thể kết nối cơ sở dữ liệu.');
        }

        $stmt = $db->prepare('
            SELECT return_id AS request_id, request_type, reason, status, created_at
            FROM return_requests
            WHERE order_id = ? AND user_id = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([(int) $orderId, (int) $userId]);
        $returnRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $returnRequests = [];
    }
}
