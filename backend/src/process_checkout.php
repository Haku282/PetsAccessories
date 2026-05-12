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

$inputName = trim($_POST['fullname'] ?? '');
$inputPhone = trim($_POST['phone'] ?? '');
$emailInput = trim($_POST['email'] ?? '');

$province = trim($_POST['province'] ?? '');
$district = trim($_POST['district'] ?? '');
$ward = trim($_POST['ward'] ?? '');
$addressSpecific = trim($_POST['address'] ?? '');

$inputAddress = $addressSpecific;
if ($ward) $inputAddress .= ', ' . $ward;
if ($district) $inputAddress .= ', ' . $district;
if ($province) $inputAddress .= ', ' . $province;

$shippingMethod = $_POST['shipping_method'] ?? 'standard';
$paymentMethod = $_POST['payment_method'] ?? 'cod';

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

// Lấy thông tin user từ profile (ưu tiên dữ liệu người dùng đã đăng nhập)
$userEmail = $emailInput;
$userPhone = $inputPhone;
$userName = $inputName;
$userAddress = $inputAddress;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare('SELECT fullname, email, phone, address FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            if (empty($userName) && !empty($user['fullname'])) {
                $userName = $user['fullname'];
            }
            if (empty($userEmail) && !empty($user['email'])) {
                $userEmail = $user['email'];
            }
            if (empty($userPhone) && !empty($user['phone'])) {
                $userPhone = $user['phone'];
            }
            if (empty($userAddress) && !empty($user['address'])) {
                $userAddress = $user['address'];
            }

            if ($inputName !== '' || $inputPhone !== '' || $addressSpecific !== '' || $province !== '' || $district !== '' || $ward !== '') {
                $updateName = $inputName !== '' ? $inputName : ($user['fullname'] ?? '');
                $updatePhone = $inputPhone !== '' ? $inputPhone : ($user['phone'] ?? '');
                $updateAddress = $inputAddress !== '' ? $inputAddress : ($user['address'] ?? '');

                $updateStmt = $pdo->prepare('UPDATE users SET fullname = ?, phone = ?, address = ? WHERE user_id = ?');
                $updateStmt->execute([$updateName, $updatePhone, $updateAddress, $_SESSION['user_id']]);
                if ($updateName !== '') {
                    $_SESSION['user_name'] = $updateName;
                }
            }
        }
    } catch (PDOException $e) {
    }
}

$name = $userName;
$phone = $userPhone;
$address = $userAddress;

if (empty($name) || empty($phone) || empty($address)) {
    header('Location: /PetsAccessories/frontend/components/checkout.php?error=' . urlencode('Vui lòng điền đầy đủ thông tin giao hàng'));
    exit;
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

// ==========================================
// 1. Gửi Email chi tiết đơn hàng (Dùng PHPMailer & HTML)
// ==========================================
if (!empty($userEmail)) {
    // Gọi file autoload của Composer (điều chỉnh đường dẫn tới thư mục vendor cho đúng với cấu trúc của bạn)
    require_once __DIR__ . '/../../vendor/autoload.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Cấu hình Server SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gaming12882000@gmail.com'; // ĐIỀN EMAIL CỦA BẠN
        $mail->Password   = 'obkz gjkr zqtt rasr'; // ĐIỀN MẬT KHẨU ỨNG DỤNG
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Người gửi và người nhận
        $mail->setFrom('gaming12882000@gmail.com', 'Pets Accessories');
        $mail->addAddress($userEmail, $name);

        // Tạo danh sách sản phẩm dạng bảng HTML từ mảng $lineItems
        $htmlOrderRows = '';
        foreach ($lineItems as $item) {
            $priceFormatted = number_format($item['unit_price'], 0, ',', '.');
            $totalFormatted = number_format($item['line_total'], 0, ',', '.');
            $htmlOrderRows .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eeeeee;'>{$item['product_name']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eeeeee; text-align: center;'>{$item['quantity']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eeeeee; text-align: right;'>{$priceFormatted} ₫</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eeeeee; text-align: right; font-weight: bold;'>{$totalFormatted} ₫</td>
                </tr>
            ";
        }

        $totalValueFormatted = number_format($totalValue, 0, ',', '.');

        // Nội dung Email HTML
        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đơn hàng từ PetsAccessories';
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #4CAF50; color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>Cảm ơn bạn đã đặt hàng!</h2>
            </div>
            
            <div style='padding: 20px;'>
                <p>Chào <b>$name</b>,</p>
                <p>Đơn hàng của bạn đã được ghi nhận thành công tại hệ thống của <b>PetsAccessories</b>. Dưới đây là chi tiết đơn hàng của bạn:</p>
                
                <h3 style='border-bottom: 2px solid #4CAF50; padding-bottom: 5px; color: #4CAF50;'>Thông Tin Giao Hàng</h3>
                <ul style='list-style: none; padding: 0;'>
                    <li><b>Người nhận:</b> $name</li>
                    <li><b>Số điện thoại:</b> $phone</li>
                    <li><b>Địa chỉ:</b> $address</li>
                    <li><b>Vận chuyển:</b> $shippingLabel</li>
                    <li><b>Thanh toán:</b> $paymentLabel</li>
                </ul>

                <h3 style='border-bottom: 2px solid #4CAF50; padding-bottom: 5px; color: #4CAF50;'>Chi Tiết Sản Phẩm</h3>
                <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                    <thead>
                        <tr style='background-color: #f9f9f9;'>
                            <th style='padding: 10px; text-align: left; border-bottom: 2px solid #ddd;'>Sản phẩm</th>
                            <th style='padding: 10px; text-align: center; border-bottom: 2px solid #ddd;'>SL</th>
                            <th style='padding: 10px; text-align: right; border-bottom: 2px solid #ddd;'>Đơn giá</th>
                            <th style='padding: 10px; text-align: right; border-bottom: 2px solid #ddd;'>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        $htmlOrderRows
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='3' style='padding: 15px 10px; text-align: right; font-size: 16px;'><b>Tổng cộng:</b></td>
                            <td style='padding: 15px 10px; text-align: right; font-size: 18px; color: #E53935; font-weight: bold;'>$totalValueFormatted ₫</td>
                        </tr>
                    </tfoot>
                </table>
                
                <p style='margin-top: 20px; text-align: center; font-size: 14px; color: #777;'>
                    Nếu có bất kỳ thắc mắc nào, xin vui lòng liên hệ với chúng tôi qua email này.<br>
                    Trân trọng,<br>
                    <b>Đội ngũ PetsAccessories</b>
                </p>
            </div>
        </div>";

        // Bản text thuần cho các ứng dụng mail không hỗ trợ HTML
        $mail->AltBody = "Chào $name,\n\nCảm ơn bạn đã mua hàng tại PetsAccessories.\n\nCHI TIẾT ĐƠN HÀNG:\n$orderDetails\nTổng tiền: $totalValueFormatted ₫\n\nTHÔNG TIN GIAO HÀNG:\n- Vận chuyển: $shippingLabel\n- Thanh toán: $paymentLabel\n- Địa chỉ: $address\n\nTrân trọng,\nĐội ngũ PetsAccessories.";

        $mail->send();
    } catch (\Exception $e) {
        error_log("Lỗi gửi email xác nhận đơn hàng: {$mail->ErrorInfo}");
    }
}
// ==========================================
// KẾT THÚC PHẦN GỬI EMAIL
// ==========================================

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
        // Calculate shipping fee from shipping_zones based on province
        $shippingFee = 30000;
        $freeShippingThreshold = 500000;
        $matchedZone = 'tạm tính';
        
        $stmtSz = $pdo->query("SELECT zone_name, shipping_fee FROM shipping_zones WHERE status = 1");
        $allZones = $stmtSz->fetchAll(PDO::FETCH_ASSOC);
        
        $found = false;
        foreach ($allZones as $z) {
            if (stripos($province, $z['zone_name']) !== false || stripos($z['zone_name'], $province) !== false) {
                $shippingFee = (float)$z['shipping_fee'];
                $matchedZone = $z['zone_name'];
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            foreach ($allZones as $z) {
                if (mb_strtolower($z['zone_name']) === 'toàn quốc') {
                    $shippingFee = (float)$z['shipping_fee'];
                    $matchedZone = $z['zone_name'];
                    break;
                }
            }
        }

        if ($totalValue >= $freeShippingThreshold) {
            $shippingFee = 0;
            $matchedZone = 'Miễn phí';
        }

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