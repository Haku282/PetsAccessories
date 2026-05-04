<?php
// Xử lý đơn hàng ở đây
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

// Các phương thức
$shippingLabels = [
    'standard' => 'Giao hàng tiêu chuẩn',
    'express' => 'Giao hàng nhanh',
    'pickup' => 'Lấy tại cửa hàng'
];
$paymentLabels = [
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'ewallet' => 'Ví điện tử'
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
            // Chỉ ghi đè email nếu user không nhập email mới ở form
            if (empty($userEmail)) {
                $userEmail = $user['email'];
            }
            $userPhone = !empty($user['phone']) ? $user['phone'] : $phone;
        }
    } catch (PDOException $e) {}
}

if (empty($userPhone)) $userPhone = $phone;

// Lấy thông tin sản phẩm để gửi email
$orderDetails = "";
$totalValue = 0;
if (isset($pdo) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT product_id, product_name, price FROM products WHERE product_id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = (int)$_SESSION['cart'][$p['product_id']];
        $price = (float)$p['price'];
        $subtotal = $qty * $price;
        $totalValue += $subtotal;
        $orderDetails .= "- " . $p['product_name'] . " x " . $qty . " = " . number_format($subtotal, 0, ',', '.') . " ₫\n";
    }
}

// 1. Gửi Email chi tiết đơn hàng
if (!empty($userEmail)) {
    $to = $userEmail;
    $subject = "Xac nhan don hang tu PetsAccessories";
    $message = "Chào " . $name . ",\n\n";
    $message .= "Cảm ơn bạn đã mua hàng tại PetsAccessories.\n\n";
    $message .= "CHI TIẾT ĐƠN HÀNG:\n";
    $message .= "----------------------------------------\n";
    $message .= $orderDetails;
    $message .= "----------------------------------------\n";
    $message .= "Tổng tiền hàng (Tạm tính): " . number_format($totalValue, 0, ',', '.') . " ₫\n\n";
    $message .= "THÔNG TIN GIAO HÀNG:\n";
    $message .= "- Vận chuyển: $shippingLabel\n";
    $message .= "- Thanh toán: $paymentLabel\n";
    $message .= "- Địa chỉ: $address\n\n";
    $message .= "Trân trọng,\nĐội ngũ PetsAccessories.";
    
    $headers = "From: noreply@petsaccessories.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($to, $subject, $message, $headers);
}

// 2. Gửi SMS xác nhận
if (!empty($userPhone)) {
    // Đoạn code dưới đây mô phỏng gọi API gửi SMS SMSGateway/Twilio
    // $smsMessage = "PetsAccessories: Đon hang cua ban da duoc dat thanh cong. Tong: " . number_format($totalValue, 0, ',', '.') . " ₫";
    // send_sms($userPhone, $smsMessage);
}

// Tạo thông báo hiển thị cho user
$contactMethods = [];
if (!empty($userEmail)) $contactMethods[] = "Email (<b>" . htmlspecialchars($userEmail) . "</b>)";
if (!empty($userPhone)) $contactMethods[] = "SMS (<b>" . htmlspecialchars($userPhone) . "</b>)";

if (count($contactMethods) > 0) {
    $notificationMessage = "Một biểu mẫu chi tiết đơn hàng đã được gửi tới " . implode(" và ", $contactMethods) . ".";
} else {
    $notificationMessage = "Đơn hàng của bạn đã được ghi nhận trên hệ thống.";
}

// TODO: Thêm logic lưu đơn hàng vào database (bảng orders, order_details, trừ tồn kho đã làm trong cart.php nên có thể cần flow đặt biệt hơn)

// Xóa giỏ hàng sau khi đặt thành công
$_SESSION['cart'] = [];

if (isset($_POST['redirect_to_index']) && $_POST['redirect_to_index'] == '1') {
    header('Location: /PetsAccessories/frontend/public/index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>
    <main style="max-width: 600px; margin: 60px auto; text-align: center; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.05);">
        <h2 style="color: #027a48; margin-bottom: 20px;">🎉 Đặt hàng thành công!</h2>
        <p style="margin-bottom: 10px; color: #555;">Cảm ơn <strong><?php echo htmlspecialchars($name); ?></strong> đã mua sắm tại PetsAccessories.</p>
        <p style="margin-bottom: 10px; color: #555;">Đơn hàng sẽ được giao đến địa chỉ: <strong><?php echo htmlspecialchars($address); ?></strong>.</p>
        
        <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px; text-align: left; display: inline-block;">
            <p style="margin: 5px 0;"><strong>Vận chuyển:</strong> <?php echo htmlspecialchars($shippingLabel); ?></p>
            <p style="margin: 5px 0;"><strong>Thanh toán:</strong> <?php echo htmlspecialchars($paymentLabel); ?></p>
        </div>

        <p style="margin-bottom: 30px; color: #155724; background: #d4edda; padding: 12px; border-radius: 8px;">
            <?php echo $notificationMessage; ?>
        </p>
        
        <a href="/PetsAccessories/frontend/public/index.php" class="cart-btn" style="display: inline-block;">Tiếp tục mua sắm</a>
    </main>
    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>
</html>
