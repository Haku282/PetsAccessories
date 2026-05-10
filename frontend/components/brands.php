<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm Mới - Pets Accessories</title>
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layout/Header.php'; ?>
    <?php
    require_once __DIR__ . '/../../backend/config/database.php';

    $brands = [];
    $errorMsg = '';

    if (isset($pdo)) {
        try {
            // Lấy dữ liệu từ bảng brands
            $sql = "SELECT brand_id, brand_name, brand_logo FROM brands ORDER BY brand_id ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errorMsg = "Lỗi tải danh sách thương hiệu: " . $e->getMessage();
        }
    } else {
        $errorMsg = "Chưa kết nối được cơ sở dữ liệu.";
    }
    ?>
    
    <style>
        /* CSS cho phần Thương hiệu */
        .brands-section {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .brands-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .brands-header h2 {
            color: #4CAF50;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            display: inline-block;
            margin: 0;
        }

        .brand-grid {
            display: grid;
            /* Chia cột tự động: Mỗi thẻ thương hiệu rộng ít nhất 180px */
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            /* Để không bị sát lề trên mobile */
        }

        .brand-card {
            background-color: white;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px 15px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            /* Xóa gạch chân của thẻ a */
            color: #333;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .brand-card:hover {
            transform: translateY(-5px);
            /* Hiệu ứng nảy lên khi hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border-color: #4CAF50;
        }

        .brand-card img {
            max-width: 100%;
            height: 80px;
            /* Cố định chiều cao logo để các thẻ đều nhau */
            object-fit: contain;
            /* Đảm bảo logo không bị méo */
            margin-bottom: 15px;
        }

        .brand-card h4 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
    </style>

    <div class="brands-section">
        <div class="brands-header">
            <h2>Thương Hiệu Nổi Bật</h2>
        </div>

        <?php if (!empty($errorMsg)): ?>
            <div style="color: red; text-align: center; padding: 15px; border: 1px solid red; border-radius: 5px; background-color: #ffe6e6; max-width: 600px; margin: 0 auto;">
                <?php echo $errorMsg; ?>
            </div>
        <?php elseif (empty($brands)): ?>
            <p style="text-align: center; color: #777;">Hiện chưa có thương hiệu nào.</p>
        <?php else: ?>
            <div class="brand-grid">

                <?php foreach ($brands as $brand): ?>
                    <?php
                    $imageFolder = '../../backend/uploads/imgBrand';
                    $logoPath = !empty($brand['brand_logo']) ? $imageFolder . htmlspecialchars($brand['brand_logo']) : $imageFolder . 'default-brand.png';
                    ?>

                    <a href="/PetsAccessories/frontend/public/index.php?page=products&brand_id=<?php echo $brand['brand_id']; ?>" class="brand-card">
                        <img src="<?php echo $logoPath; ?>" alt="Logo <?php echo htmlspecialchars($brand['brand_name']); ?>" onerror="this.src='<?php echo $imageFolder; ?>default-brand.png'">
                        <h4><?php echo htmlspecialchars($brand['brand_name']); ?></h4>
                    </a>

                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </div>
    <?php require_once __DIR__ . '/../layout/Footer.php'; ?>
</body>

</html>