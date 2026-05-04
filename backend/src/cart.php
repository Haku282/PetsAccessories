<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$db = $pdo;
$error = '';
$notice = '';

// Config tạm tính (có thể chỉnh theo nhu cầu)
$taxRate = 0.10; // 10% VAT (tạm tính)
$shippingFlatFee = 30000; // 30.000đ (tạm tính)
$freeShippingThreshold = 500000; // Miễn phí ship từ 500.000đ (tạm tính)

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function cart_recalculate_count(): int
{
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

function cart_normalize_quantity(mixed $value): int
{
    $qty = filter_var($value, FILTER_VALIDATE_INT);
    if ($qty === false || $qty < 0) {
        return 0;
    }
    if ($qty > 99) {
        return 99;
    }
    return (int) $qty;
}

function cart_adjust_item(PDO $db, int $productId, int $newQty, string &$error, string &$notice): void
{
    $currentQty = (int) ($_SESSION['cart'][$productId] ?? 0);
    if ($currentQty < 0) {
        $currentQty = 0;
    }

    if ($newQty === $currentQty) {
        return;
    }

    // If cart doesn't have the item and newQty is 0, nothing to do.
    if ($currentQty === 0 && $newQty === 0) {
        return;
    }

    $diff = $newQty - $currentQty; // + => need reserve more stock, - => release stock

    try {
        $db->beginTransaction();

        $stmt = $db->prepare('SELECT stock_quantity FROM products WHERE product_id = ? FOR UPDATE');
        $stmt->execute([$productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = 'Sản phẩm không tồn tại.';
            return;
        }

        $stock = (int) ($row['stock_quantity'] ?? 0);

        if ($diff > 0) {
            if ($stock < $diff) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = 'Không đủ tồn kho để tăng số lượng.';
                return;
            }

            $update = $db->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?');
            $update->execute([$diff, $productId]);
        } elseif ($diff < 0) {
            $release = abs($diff);
            $update = $db->prepare('UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?');
            $update->execute([$release, $productId]);
        }

        if ($newQty <= 0) {
            unset($_SESSION['cart'][$productId]);
            $notice = 'Đã xóa sản phẩm khỏi giỏ hàng.';
        } else {
            $_SESSION['cart'][$productId] = $newQty;
            $notice = 'Đã cập nhật giỏ hàng.';
        }

        if ($db->inTransaction()) {
            $db->commit();
        }
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = 'Lỗi hệ thống: ' . $e->getMessage();
    }
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if (!($db instanceof PDO)) {
        $error = 'Không thể kết nối cơ sở dữ liệu.';
    } elseif (!$productId) {
        $error = 'Sản phẩm không hợp lệ.';
    } else {
        if ($action === 'update') {
            $newQty = cart_normalize_quantity($_POST['quantity'] ?? 0);
            cart_adjust_item($db, (int) $productId, $newQty, $error, $notice);
        } elseif ($action === 'remove') {
            cart_adjust_item($db, (int) $productId, 0, $error, $notice);
        }
    }

    // PRG: redirect back to cart to avoid resubmits (skip when used as API)
    if (!defined('CART_API')) {
        $params = [];
        if (!empty($error)) {
            $params['error'] = $error;
        }
        if (!empty($notice)) {
            $params['notice'] = $notice;
        }

        $redirectUrl = '/PetsAccessories/frontend/components/cart.php';
        if (!empty($params)) {
            $redirectUrl .= '?' . http_build_query($params);
        }

        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Pull notice/error from query
if (!empty($_GET['error'])) {
    $error = (string) $_GET['error'];
}
if (!empty($_GET['notice'])) {
    $notice = (string) $_GET['notice'];
}

$cartItems = [];
$subtotal = 0.0;

if ($db instanceof PDO && !empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    try {
        $stmt = $db->prepare(
            'SELECT product_id, product_name, price, discount_price, thumbnail
             FROM products
             WHERE status = 1 AND product_id IN (' . $placeholders . ')'
        );
        $stmt->execute($productIds);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byId = [];
        foreach ($rows as $r) {
            $byId[(int) $r['product_id']] = $r;
        }

        foreach ($_SESSION['cart'] as $pid => $qty) {
            $pid = (int) $pid;
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }
            if (!isset($byId[$pid])) {
                continue;
            }

            $p = $byId[$pid];
            $price = (float) ($p['price'] ?? 0);
            $discount = (float) ($p['discount_price'] ?? 0);
            $unitPrice = ($discount > 0 && $discount < $price) ? $discount : $price;
            $lineTotal = $unitPrice * $qty;

            $thumb = !empty($p['thumbnail'])
                ? $p['thumbnail']
                : '/PetsAccessories/frontend/public/images/default-product.png';

            $cartItems[] = [
                'product_id' => $pid,
                'product_name' => $p['product_name'] ?? 'Sản phẩm',
                'thumbnail' => $thumb,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'line_total' => $lineTotal,
            ];

            $subtotal += $lineTotal;
        }
    } catch (PDOException $e) {
        $error = 'Không thể tải giỏ hàng lúc này.';
    }
}

$tax = $subtotal * $taxRate;
$shipping = $subtotal <= 0 ? 0 : (($subtotal >= $freeShippingThreshold) ? 0 : $shippingFlatFee);
$estimatedTotal = $subtotal + $tax + $shipping;

$cartCount = cart_recalculate_count();
