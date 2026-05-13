<?php
/**
 * API: Xuất đơn hàng
 * GET /admin/backend/api/orders/export.php?format=pdf|excel&id=ORDER_ID
 */

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    ob_end_clean();
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// 2. Kiểm tra tham số
if (!isset($_GET['id']) || !isset($_GET['format'])) {
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Tham số không hợp lệ']);
    exit;
}

$format = strtolower(trim($_GET['format']));
require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    $orderId = (int)$_GET['id'];

    // 3. Lấy thông tin đơn hàng
    $stmt = $db->prepare("
        SELECT o.*, u.fullname, u.email, u.phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại');
    }

    // 4. Lấy chi tiết sản phẩm
    $itemsStmt = $db->prepare("
        SELECT oi.quantity, oi.price_at_purchase, p.product_name
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Xử lý xuất file
    if ($format === 'pdf') {
        ob_end_clean();
        generatePrintView($order, $items);
    } elseif ($format === 'excel') {
        ob_end_clean();
        generateExcel($order, $items);
    } else {
        throw new Exception('Định dạng không hỗ trợ');
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Hàm tạo giao diện in (Thay thế cho PDF thuần vì PHP cần thư viện ngoài để render PDF)
 * Người dùng có thể nhấn Ctrl + P để lưu thành PDF cực đẹp.
 */
function generatePrintView($order, $items) {
    $ngayTao = date('d/m/Y H:i', strtotime($order['created_at']));
    $ngayXuat = date('d/m/Y H:i');

    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Hóa đơn #<?php echo $order['order_id']; ?></title>
        <style>
            body { font-family: "Times New Roman", serif; margin: 40px; color: #333; line-height: 1.6; }
            .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
            .meta-info { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
            th { background: #f5f5f5; }
            .total-section { text-align: right; margin-top: 20px; font-weight: bold; font-size: 18px; }
            .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #777; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body onload="window.print()">
        <div class="no-print" style="background:#fff3cd; padding:10px; margin-bottom:20px; text-align:center;">
            Gợi ý: Nhấn <b>Ctrl + P</b> và chọn "Lưu dưới dạng PDF" để tải file.
        </div>

        <div class="header">
            <h1>HÓA ĐƠN ĐẶT HÀNG</h1>
            <p>Mã đơn hàng: #<?php echo $order['order_id']; ?></p>
        </div>

        <div class="meta-info">
            <div>
                <strong>Thông tin khách hàng:</strong><br>
                Họ tên: <?php echo htmlspecialchars($order['fullname']); ?><br>
                SĐT: <?php echo htmlspecialchars($order['phone']); ?><br>
                Địa chỉ: <?php echo htmlspecialchars($order['shipping_address']); ?>
            </div>
            <div style="text-align: right;">
                <strong>Thời gian:</strong><br>
                Ngày tạo đơn: <?php echo $ngayTao; ?><br>
                Ngày xuất hóa đơn: <?php echo $ngayXuat; ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                foreach ($items as $item): 
                    $lineTotal = $item['quantity'] * $item['price_at_purchase'];
                    $subtotal += $lineTotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo number_format($lineTotal, 0, ',', '.'); ?>đ</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            Tổng cộng: <?php echo number_format($order['total_price'], 0, ',', '.'); ?>đ
        </div>

        <div class="footer">
            <p>Cảm ơn quý khách đã mua hàng!</p>
            <p>Bản quyền © PetsAccessories - <?php echo date('Y'); ?></p>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Hàm xuất Excel bằng Table HTML (Đảm bảo font tiếng Việt và định dạng cột)
 */
function generateExcel($order, $items) {
    $filename = "DonHang_" . $order['order_id'] . "_" . date('dmY') . ".xls";
    
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $ngayTao = date('d/m/Y H:i', strtotime($order['created_at']));
    $ngayXuat = date('d/m/Y H:i');

    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '<table border="1">';
    // Tiêu đề
    echo '<tr><th colspan="4" style="background-color: #D3D3D3; font-size: 14pt;">ĐƠN HÀNG #' . $order['order_id'] . '</th></tr>';
    
    // Mốc thời gian
    echo '<tr><td colspan="2"><b>Ngày tạo đơn:</b></td><td colspan="2">' . $ngayTao . '</td></tr>';
    echo '<tr><td colspan="2"><b>Ngày xuất báo cáo:</b></td><td colspan="2">' . $ngayXuat . '</td></tr>';
    
    // Thông tin khách
    echo '<tr><td colspan="2"><b>Khách hàng:</b></td><td colspan="2">' . htmlspecialchars($order['fullname']) . '</td></tr>';
    echo '<tr><td colspan="2"><b>Số điện thoại:</b></td><td colspan="2">' . htmlspecialchars($order['phone']) . '</td></tr>';
    
    echo '<tr><td colspan="4"></td></tr>'; // Dòng trống

    // Tiêu đề bảng sản phẩm
    echo '<tr style="background-color: #f2f2f2;">
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
          </tr>';

    foreach ($items as $item) {
        $thanhTien = $item['quantity'] * $item['price_at_purchase'];
        echo '<tr>';
        echo '<td>' . htmlspecialchars($item['product_name']) . '</td>';
        echo '<td align="center">' . $item['quantity'] . '</td>';
        echo '<td align="right">' . number_format($item['price_at_purchase'], 0, '', '') . '</td>';
        echo '<td align="right">' . number_format($thanhTien, 0, '', '') . '</td>';
        echo '</tr>';
    }

    // Tổng cộng
    echo '<tr>
            <td colspan="3" align="right"><b>TỔNG CỘNG:</b></td>
            <td align="right" style="color: red;"><b>' . number_format($order['total_price'], 0, '', '') . '</b></td>
          </tr>';
    echo '</table>';
}