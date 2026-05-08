<?php
/**
 * API: Xuất đơn hàng
 * GET /admin/backend/api/orders/export.php?format=pdf|excel&id=ORDER_ID
 */

// Chỉ gọi session_start() nếu session chưa được khởi tạo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['format'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tham số không hợp lệ']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    $orderId = (int)$_GET['id'];
    $format = $_GET['format'];

    // Lấy thông tin đơn hàng
    $stmt = $db->prepare("
        SELECT 
            o.*,
            u.user_name,
            u.email,
            u.phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
        exit;
    }

    // Lấy chi tiết các sản phẩm
    $itemsStmt = $db->prepare("
        SELECT 
            oi.quantity,
            oi.price_at_purchase,
            p.product_name
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($format === 'pdf') {
        generatePDF($order, $items);
    } elseif ($format === 'excel') {
        generateExcel($order, $items);
    } else {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Định dạng không hợp lệ']);
    }

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

function generatePDF($order, $items) {
    // Nội dung PDF (có thể sử dụng library như TCPDF nếu cần)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="order_' . $order['order_id'] . '.pdf"');
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Hóa đơn - Đơn hàng #' . $order['order_id'] . '</title>
        <style>
            body { font-family: Arial; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .order-info { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f5f5f5; }
            .total { font-weight: bold; font-size: 14px; }
            .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>HÓA ĐƠN</h1>
            <p>Đơn hàng #' . htmlspecialchars($order['order_id']) . '</p>
        </div>

        <div class="order-info">
            <h3>Thông tin khách hàng</h3>
            <p><strong>Tên:</strong> ' . htmlspecialchars($order['user_name']) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>
            <p><strong>Điện thoại:</strong> ' . htmlspecialchars($order['phone'] ?? 'N/A') . '</p>
            <p><strong>Địa chỉ giao hàng:</strong> ' . htmlspecialchars($order['shipping_address']) . '</p>
            <p><strong>Ngày tạo:</strong> ' . date('d/m/Y H:i', strtotime($order['created_at'])) . '</p>
        </div>

        <h3>Chi tiết sản phẩm</h3>
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
    ';

    $subtotal = 0;
    foreach ($items as $item) {
        $lineTotal = (float)$item['quantity'] * (float)$item['price_at_purchase'];
        $subtotal += $lineTotal;
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['product_name']) . '</td>
                    <td>' . $item['quantity'] . '</td>
                    <td>' . number_format($item['price_at_purchase'], 0, ',', '.') . '₫</td>
                    <td>' . number_format($lineTotal, 0, ',', '.') . '₫</td>
                </tr>
        ';
    }

    $html .= '
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: right;">
            <p class="total">Tổng tiền hàng: ' . number_format($subtotal, 0, ',', '.') . '₫</p>
            <p class="total">Phí vận chuyển: ' . number_format($order['shipping_fee'], 0, ',', '.') . '₫</p>
            <p class="total">Giảm giá: ' . number_format($order['discount_amount'], 0, ',', '.') . '₫</p>
            <p class="total" style="font-size: 16px; border-top: 2px solid #333; padding-top: 10px;">
                Tổng cộng: ' . number_format($order['total_price'], 0, ',', '.') . '₫
            </p>
        </div>

        <div class="footer">
            <p>Cảm ơn bạn đã mua hàng!</p>
            <p>Ngày xuất: ' . date('d/m/Y H:i') . '</p>
        </div>
    </body>
    </html>
    ';

    // Sử dụng thư viện TCPDF nếu muốn export PDF chính thức
    // Hiện tại chỉ output HTML có thể in được
    echo $html;
}

function generateExcel($order, $items) {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="order_' . $order['order_id'] . '.xls"');
    
    echo "\xEF\xBB\xBF"; // BOM for UTF-8

    $excel = "ĐƠN HÀNG #" . $order['order_id'] . "\n\n";
    $excel .= "THÔNG TIN KHÁCH HÀNG\n";
    $excel .= "Tên:\t" . $order['user_name'] . "\n";
    $excel .= "Email:\t" . $order['email'] . "\n";
    $excel .= "Điện thoại:\t" . ($order['phone'] ?? 'N/A') . "\n";
    $excel .= "Địa chỉ giao:\t" . $order['shipping_address'] . "\n";
    $excel .= "Ngày tạo:\t" . date('d/m/Y H:i', strtotime($order['created_at'])) . "\n";
    $excel .= "\n";
    $excel .= "CHI TIẾT SẢN PHẨM\n";
    $excel .= "Sản phẩm\tSố lượng\tGiá\tThành tiền\n";

    $subtotal = 0;
    foreach ($items as $item) {
        $lineTotal = (float)$item['quantity'] * (float)$item['price_at_purchase'];
        $subtotal += $lineTotal;
        $excel .= $item['product_name'] . "\t" . $item['quantity'] . "\t" . 
                  number_format($item['price_at_purchase'], 0) . "\t" . 
                  number_format($lineTotal, 0) . "\n";
    }

    $excel .= "\n";
    $excel .= "TỔNG KẾT\n";
    $excel .= "Tổng tiền hàng:\t" . number_format($subtotal, 0) . "₫\n";
    $excel .= "Phí vận chuyển:\t" . number_format($order['shipping_fee'], 0) . "₫\n";
    $excel .= "Giảm giá:\t" . number_format($order['discount_amount'], 0) . "₫\n";
    $excel .= "Tổng cộng:\t" . number_format($order['total_price'], 0) . "₫\n";

    echo $excel;
}
?>
