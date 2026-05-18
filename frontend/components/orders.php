<?php
require_once __DIR__ . '/../../backend/src/orders.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mua hàng - PetsAccessories</title>
    <link rel="stylesheet" href="../layout/style.css">
    <style>
        .orders-page {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .orders-page h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .orders-table th, .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .orders-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #555;
        }
        .orders-table tbody tr:hover {
            background-color: #fdfdfd;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-shipping { background-color: #cce5ff; color: #004085; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .empty-orders {
            text-align: center;
            padding: 40px 20px;
            color: #777;
        }
        .btn-view-details {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-view-details:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>

    <main class="orders-page">
        <h2>Lịch sử mua hàng</h2>

        <?php if (!empty($error)): ?>
            <div style="color: red; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <p>Bạn chưa có đơn hàng nào.</p>
                <a href="/PetsAccessories/frontend/public/index.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($order['order_id']); ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td><?php echo number_format((float) $order['total_price'], 0, ',', '.'); ?> đ</td>
                                <td>
                                    <?php echo mapPaymentStatus($order['payment_status']); ?>
                                </td>
                                <td>
                                    <?php echo mapOrderStatus($order['order_status']); ?>
                                </td>
                                <td>
                                    <a class="btn-view-details" href="/PetsAccessories/frontend/components/order_detail.php?id=<?php echo (int) $order['order_id']; ?>">Xem chi tiết</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>
</html>