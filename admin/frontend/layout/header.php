<?php
// admin/frontend/layout/header.php
$pageTitle = $pageTitle ?? 'Quản Trị Hệ Thống';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Quản Trị Viên</title>
    <!-- Use Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/PetsAccessories/admin/frontend/assets/css/dashboard.css">
    <?php if (isset($extraCss)) echo $extraCss; ?>
    <style>
        /* Global Modal Fix for all admin pages */
        .modal {
            display: none; /* Changed via JS or Observer to flex */
            position: fixed !important;
            z-index: 9999 !important;
            left: 0 !important;
            top: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(15, 23, 42, 0.6) !important;
            backdrop-filter: blur(4px);
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important; 
            margin: 0 !important;
            padding: 0 !important;
        }
        .modal.active, .modal.show {
            display: flex !important;
        }
        .modal-content {
            background: #fff !important;
            border-radius: 12px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1) !important;
            max-width: 720px;
            width: 90%;
            max-height: 85vh !important;
            display: flex !important;
            flex-direction: column !important;
            position: relative !important;
            transform: none !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            bottom: auto !important;
            margin: auto !important;
            animation: fadeInModal 0.25s ease-out forwards;
        }
        @keyframes fadeInModal {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            padding: 20px 24px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-shrink: 0;
        }
        .modal-header h3 { margin: 0 !important; font-size: 1.25rem !important; font-weight: 600 !important; color: #1e293b !important; }
        .modal-body {
            padding: 24px !important;
            overflow-y: auto !important;
            flex: 1 1 auto !important;
        }
        .modal-footer {
            padding: 16px 24px !important;
            border-top: 1px solid #e2e8f0 !important;
            display: flex !important;
            justify-content: flex-end !important;
            gap: 12px !important;
            background: #f8fafc !important;
            border-radius: 0 0 12px 12px !important;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php require __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <div class="header-title">
                    <h1><?= htmlspecialchars($pageTitle) ?></h1>
                </div>
                <div class="user-profile">
                    <span>Xin chào: <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong></span>
                    <a href="/PetsAccessories/frontend/components/logout.php" class="btn-logout">
                        <span>🚪</span> Đăng Xuất
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
