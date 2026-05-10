<?php
require_once __DIR__ . '/../../../backend/middleware/check_admin.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo Cáo Bình Luận - Admin</title>
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/categories.css">
</head>
<body>
    <div class="container">
        <div class="header"><div><h1><span>⚠️</span> Báo Cáo Bình Luận</h1></div></div>
        <div class="menu">
            <ul>
                <li><a href="/PetsAccessories/admin/frontend/index_admin.php"><span>📊</span> Dashboard</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/reviews/index.php"><span>⭐</span> Đánh Giá</a></li>
                <li><a href="/PetsAccessories/admin/frontend/pages/reviews/reports.php" class="active"><span>⚠️</span> Báo Cáo</a></li>
            </ul>
        </div>
        <div class="brands-container">
            <p><i>API backend review_reports đã được thêm để xử lý báo cáo sai phạm.</i></p>
        </div>
    </div>
</body>
</html>
