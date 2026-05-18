<?php
require_once __DIR__ . '/../../backend/src/order_detail.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo (int) $orderId; ?> - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <style>
        .order-detail-page {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .order-detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .order-detail-header h2 {
            margin: 0;
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: #f5f5f5;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-weight: 600;
        }

        .btn-primary {
            background: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin: 16px 0;
        }

        .meta-box {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 14px;
            background: #fafafa;
        }

        .meta-box h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #333;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 6px 0;
            color: #555;
        }

        .meta-row strong {
            color: #222;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .orders-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #555;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-shipping {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 8px;
            margin: 12px 0;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .return-box {
            margin-top: 22px;
            border-top: 2px solid #eee;
            padding-top: 16px;
        }

        .return-box h3 {
            margin: 0 0 10px 0;
        }

        .form-row {
            margin: 10px 0;
        }

        .form-row label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }

        .form-row select,
        .form-row textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-row textarea {
            min-height: 90px;
            resize: vertical;
        }

        @media print {

            header,
            footer,
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .order-detail-page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .meta-box {
                background: #fff !important;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../layout/header.php'; ?>

    <main class="order-detail-page">
        <div class="order-detail-header">
            <h2>Chi tiết đơn hàng #<?php echo (int) $orderId; ?></h2>
            <div class="no-print" style="display:flex; gap:10px; align-items:center;">
                <a class="btn" href="/PetsAccessories/frontend/components/orders.php">Quay lại</a>
                <button class="btn btn-primary" type="button" onclick="window.print()">In hóa đơn</button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
            <div class="meta-grid">
                <div class="meta-box">
                    <h3>Thông tin đơn hàng</h3>
                    <div class="meta-row"><span>Ngày đặt</span><strong><?php echo !empty($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : ''; ?></strong></div>
                    <div class="meta-row"><span>Trạng thái</span><strong><?php echo mapOrderStatusLabel((string) ($order['order_status'] ?? '')); ?></strong></div>
                    <div class="meta-row"><span>Thanh toán</span><strong><?php echo mapPaymentStatusText((string) ($order['payment_status'] ?? '')); ?></strong></div>
                    <div class="meta-row"><span>Phương thức</span><strong><?php echo htmlspecialchars((string) ($order['payment_method'] ?? '')); ?></strong></div>
                </div>

                <div class="meta-box">
                    <h3>Giao hàng</h3>
                    <div class="meta-row"><span>Phương thức</span><strong><?php echo htmlspecialchars((string) ($order['shipping_method'] ?? '')); ?></strong></div>
                    <div class="meta-row"><span>Địa chỉ</span><strong><?php echo htmlspecialchars((string) ($order['shipping_address'] ?? '')); ?></strong></div>
                    <div class="meta-row"><span>Người nhận</span><strong><?php echo htmlspecialchars((string) ($order['fullname'] ?? ($_SESSION['user_name'] ?? ''))); ?></strong></div>
                    <div class="meta-row"><span>Liên hệ</span><strong><?php echo htmlspecialchars((string) ($order['phone'] ?? '')); ?></strong></div>
                </div>
            </div>

            <h3>Danh sách sản phẩm</h3>
            <?php if (empty($orderItems)): ?>
                <div class="alert" style="background:#fff3cd;color:#856404;">Chưa có dữ liệu chi tiết sản phẩm cho đơn này (thường là đơn được tạo trước khi hệ thống lưu chi tiết theo từng sản phẩm).</div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <?php
                                        // Xử lý chuẩn đường dẫn ảnh
                                        $rawThumb = $item['thumbnail'] ?? '';
                                        if (!empty($rawThumb)) {
                                            if (strpos($rawThumb, '/') !== false) {
                                                $thumbnail = $rawThumb;
                                            } else {
                                                $thumbnail = '/PetsAccessories/admin/backend/uploads/products/' . $rawThumb;
                                            }
                                        } else {
                                            $thumbnail = '/PetsAccessories/frontend/public/images/default-product.png';
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($thumbnail); ?>"
                                            alt="<?php echo htmlspecialchars((string) ($item['product_name'] ?? '')); ?>"
                                            style="width:40px;height:40px;object-fit:cover;border-radius:6px;vertical-align:middle;margin-right:8px;"
                                            onerror="this.onerror=null; this.src='/PetsAccessories/frontend/public/images/default-product.png'">

                                        <?php echo htmlspecialchars((string) ($item['product_name'] ?? ('#' . (int) $item['product_id']))); ?>
                                    </td>
                                    <td><?php echo (int) ($item['quantity'] ?? 0); ?></td>
                                    <td><?php echo number_format((float) ($item['unit_price'] ?? 0), 0, ',', '.'); ?> đ</td>
                                    <td><strong><?php echo number_format((float) ($item['line_total'] ?? 0), 0, ',', '.'); ?> đ</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="meta-grid">
                <div class="meta-box">
                    <h3>Tổng kết</h3>
                    <div class="meta-row"><span>Phí ship</span><strong><?php echo number_format((float) ($order['shipping_fee'] ?? 0), 0, ',', '.'); ?> đ</strong></div>
                    <div class="meta-row"><span>Giảm giá</span><strong style="<?php echo ($order['discount_amount'] ?? 0) > 0 ? 'color: #e74c3c;' : ''; ?>"><?php echo ($order['discount_amount'] ?? 0) > 0 ? '-' : ''; ?><?php echo number_format((float) ($order['discount_amount'] ?? 0), 0, ',', '.'); ?> đ</strong></div>
                    <div class="meta-row"><span>Tổng thanh toán</span><strong><?php echo number_format((float) ($order['total_price'] ?? 0), 0, ',', '.'); ?> đ</strong></div>
                </div>
                <div class="meta-box">
                    <h3>Ghi chú</h3>
                    <div style="color:#555; min-height: 48px;">
                        <?php echo nl2br(htmlspecialchars((string) ($order['notes'] ?? ''))); ?>
                    </div>
                </div>
            </div>

            <div class="return-box">
                <h3>Yêu cầu đổi/trả</h3>
                <div class="no-print">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_return_request">
                        <div class="form-row">
                            <label for="request_type">Loại yêu cầu</label>
                            <select id="request_type" name="request_type" required>
                                <option value="return">Trả hàng</option>
                                <option value="exchange">Đổi hàng</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label for="reason">Lý do</label>
                            <textarea id="reason" name="reason" placeholder="Mô tả tình trạng sản phẩm, lý do đổi/trả..." required></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Gửi yêu cầu</button>
                    </form>
                    <p style="margin-top:10px;color:#777;">Lưu ý: hiện chỉ cho phép gửi yêu cầu khi đơn hàng ở trạng thái Hoàn thành.</p>
                </div>

                <?php if (!empty($returnRequests)): ?>
                    <h4 style="margin:16px 0 8px 0;">Lịch sử yêu cầu</h4>
                    <div style="overflow-x:auto;">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Mã yêu cầu</th>
                                    <th>Loại</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Lý do</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returnRequests as $req): ?>
                                    <tr>
                                        <td>#<?php echo (int) $req['return_id']; ?></td>
                                        <td><?php echo htmlspecialchars((string) $req['request_type']); ?></td>
                                        <td><?php echo htmlspecialchars((string) $req['status']); ?></td>
                                        <td><?php echo !empty($req['created_at']) ? date('d/m/Y H:i', strtotime($req['created_at'])) : ''; ?></td>
                                        <td><?php echo nl2br(htmlspecialchars((string) ($req['reason'] ?? ''))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../layout/footer.php'; ?>
</body>

</html>