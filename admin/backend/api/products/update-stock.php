<?php
/**
 * API: Cập nhật tồn kho sản phẩm
 * POST /admin/backend/api/products/update-stock.php
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST request']);
    exit;
}

require_once __DIR__ . '/../../../../backend/config/database.php';

try {
    /** @var PDO $pdo */
    $db = $pdo;
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['product_id']) || !isset($data['quantity']) || !isset($data['type'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    $product_id = (int)$data['product_id'];
    $quantity = (int)$data['quantity'];
    $type = $data['type']; // 'import' hoặc 'export'
    $note = $data['note'] ?? '';

    if ($quantity <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Số lượng phải lớn hơn 0']);
        exit;
    }

    if (!in_array($type, ['import', 'export'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Loại hành động không hợp lệ']);
        exit;
    }

    // Kiểm tra sản phẩm tồn tại
    $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    $current_stock = $product['stock_quantity'];

    // Tính toán tồn kho mới
    if ($type === 'import') {
        $new_stock = $current_stock + $quantity;
    } else {
        if ($quantity > $current_stock) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Số lượng xuất vượt quá tồn kho']);
            exit;
        }
        $new_stock = $current_stock - $quantity;
    }

    // Bắt đầu transaction
    $db->beginTransaction();

    try {
        // Cập nhật tồn kho
        $updateStmt = $db->prepare("
            UPDATE products 
            SET stock_quantity = ?, updated_at = NOW()
            WHERE product_id = ?
        ");
        $updateStmt->execute([$new_stock, $product_id]);

        // Tạo log nhập/xuất kho (nếu có bảng stock_logs)
        try {
            $logStmt = $db->prepare("
                INSERT INTO product_stock_logs (product_id, type, quantity, current_stock, new_stock, note, admin_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $logStmt->execute([
                $product_id,
                $type,
                $quantity,
                $current_stock,
                $new_stock,
                $note,
                $_SESSION['user_id']
            ]);
        } catch (Exception $logError) {
            // Bảng log có thể không tồn tại, bỏ qua lỗi
        }

        $db->commit();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $type === 'import' ? 'Nhập kho thành công' : 'Xuất kho thành công',
            'data' => [
                'product_id' => $product_id,
                'old_stock' => $current_stock,
                'new_stock' => $new_stock,
                'quantity' => $quantity,
                'type' => $type
            ]
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
