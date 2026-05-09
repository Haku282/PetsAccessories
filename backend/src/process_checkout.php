<?php
// Xử lý đơn hàng (logic-only, không render HTML)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../backend/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /PetsAccessories/frontend/public/index.php');
    exit;
}

$name = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['customer_phone'] ?? '');
$emailInput = trim($_POST['customer_email'] ?? '');
$address = trim($_POST['customer_address'] ?? '');
$shippingMethod = $_POST['shipping_method'] ?? 'standard';
$paymentMethod = $_POST['payment_method'] ?? 'cod';

if (empty($name) || empty($phone) || empty($address)) {
    header('Location: /PetsAccessories/frontend/components/checkout.php?error=' . urlencode('Vui lòng điền đầy đủ thông tin giao hàng'));
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: /PetsAccessories/frontend/components/cart.php?error=' . urlencode('Giỏ hàng trống'));
    exit;
}

$shippingLabels = [
    'standard' => 'Giao hàng tiêu chuẩn',
    'express' => 'Giao hàng nhanh',
    'pickup' => 'Lấy tại cửa hàng',
];
$paymentLabels = [
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'ewallet' => 'Ví điện tử',
];

$shippingLabel = $shippingLabels[$shippingMethod] ?? 'Giao hàng tiêu chuẩn';
$paymentLabel = $paymentLabels[$paymentMethod] ?? 'COD';

// Lấy thông tin user từ profile
$userEmail = $emailInput;
$userPhone = '';
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare('SELECT email, phone FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            if (empty($userEmail)) {
                $userEmail = $user['email'];
            }
            $userPhone = !empty($user['phone']) ? $user['phone'] : $phone;
        }
    } catch (PDOException $e) {
    }
}

if (empty($userPhone)) {
    $userPhone = $phone;
}

// Lấy thông tin sản phẩm để gửi email + để lưu order_items
$orderDetails = '';
$totalValue = 0;
$lineItems = [];
$createdOrderId = 0;

if (isset($pdo) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT product_id, product_name, price, discount_price, thumbnail FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = (int) $_SESSION['cart'][$p['product_id']];
        $price = (float) ($p['price'] ?? 0);
        $discount = (float) ($p['discount_price'] ?? 0);
        $unitPrice = ($discount > 0 && $discount < $price) ? $discount : $price;
        $subtotal = $qty * $unitPrice;
        $totalValue += $subtotal;
        $orderDetails .= '- ' . $p['product_name'] . ' x ' . $qty . ' = ' . number_format($subtotal, 0, ',', '.') . " ₫\n";

        $lineItems[] = [
            'product_id' => (int) $p['product_id'],
            'product_name' => (string) ($p['product_name'] ?? ''),
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'line_total' => $subtotal,
            'thumbnail' => !empty($p['thumbnail']) ? (string) $p['thumbnail'] : '/PetsAccessories/frontend/public/images/default-product.png',
        ];
    }
}

// 1. Gửi Email chi tiết đơn hàng
if (!empty($userEmail)) {
    $to = $userEmail;
    $subject = 'Xac nhan don hang tu PetsAccessories';
    $message = 'Chào ' . $name . ",\n\n";
    $message .= "Cảm ơn bạn đã mua hàng tại PetsAccessories.\n\n";
    $message .= "CHI TIẾT ĐƠN HÀNG:\n";
    $message .= "----------------------------------------\n";
    $message .= $orderDetails;
    $message .= "----------------------------------------\n";
    $message .= 'Tổng tiền hàng (Tạm tính): ' . number_format($totalValue, 0, ',', '.') . " ₫\n\n";
    $message .= "THÔNG TIN GIAO HÀNG:\n";
    $message .= "- Vận chuyển: $shippingLabel\n";
    $message .= "- Thanh toán: $paymentLabel\n";
    $message .= "- Địa chỉ: $address\n\n";
    $message .= "Trân trọng,\nĐội ngũ PetsAccessories.";

    $headers = "From: noreply@petsaccessories.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($to, $subject, $message, $headers);
}

// 2. Gửi SMS xác nhận (mô phỏng)
if (!empty($userPhone)) {
    // $smsMessage = "PetsAccessories: Đon hang cua ban da duoc dat thanh cong. Tong: " . number_format($totalValue, 0, ',', '.') . " ₫";
    // send_sms($userPhone, $smsMessage);
}

$contactMethods = [];
if (!empty($userEmail)) {
    $contactMethods[] = 'Email (<b>' . htmlspecialchars($userEmail) . '</b>)';
}
if (!empty($userPhone)) {
    $contactMethods[] = 'SMS (<b>' . htmlspecialchars($userPhone) . '</b>)';
}

if (count($contactMethods) > 0) {
    $notificationMessage = 'Một biểu mẫu chi tiết đơn hàng đã được gửi tới ' . implode(' và ', $contactMethods) . '.';
} else {
    $notificationMessage = 'Đơn hàng của bạn đã được ghi nhận trên hệ thống.';
}

// Lưu đơn hàng vào database
if (isset($pdo) && !empty($_SESSION['cart'])) {
    try {
        $pdo->beginTransaction();

        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            order_item_id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price_at_purchase DECIMAL(12,2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order_items_order_id (order_id),
            INDEX idx_order_items_product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (user_id, total_price, shipping_fee, discount_amount, order_status, payment_method, payment_status, shipping_method, shipping_address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $userIdInsert = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $shippingFee = 0;
        $discountAmount = 0;

        $stmtOrder->execute([
            $userIdInsert,
            $totalValue + $shippingFee - $discountAmount,
            $shippingFee,
            $discountAmount,
            'pending',
            $paymentMethod,
            'unpaid',
            $shippingMethod,
            $address,
        ]);

        $orderId = $pdo->lastInsertId();
        $createdOrderId = (int) $orderId;

        if (!empty($orderId) && !empty($lineItems)) {
            $columns = [];
            try {
                $colsStmt = $pdo->query('SHOW COLUMNS FROM order_items');
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
                $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)');
                foreach ($lineItems as $item) {
                    $stmtItem->execute([
                        (int) $orderId,
                        (int) $item['product_id'],
                        (int) $item['quantity'],
                        (float) $item['unit_price'],
                    ]);
                }
            } elseif (!empty($columns['unit_price']) && !empty($columns['line_total'])) {
                $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?)');
                foreach ($lineItems as $item) {
                    $stmtItem->execute([
                        (int) $orderId,
                        (int) $item['product_id'],
                        (int) $item['quantity'],
                        (float) $item['unit_price'],
                        (float) $item['line_total'],
                    ]);
                }
            } elseif (!empty($columns['unit_price'])) {
                $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
                foreach ($lineItems as $item) {
                    $stmtItem->execute([
                        (int) $orderId,
                        (int) $item['product_id'],
                        (int) $item['quantity'],
                        (float) $item['unit_price'],
                    ]);
                }
            } else {
                $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)');
                foreach ($lineItems as $item) {
                    $stmtItem->execute([
                        (int) $orderId,
                        (int) $item['product_id'],
                        (int) $item['quantity'],
                    ]);
                }
            }
        }

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Lỗi tạo đơn hàng: ' . $e->getMessage());
    }
}

// Xóa giỏ hàng sau khi đặt thành công
$_SESSION['cart'] = [];

if (isset($_POST['redirect_to_index']) && $_POST['redirect_to_index'] == '1') {
    header('Location: /PetsAccessories/frontend/public/index.php');
    exit;
}
