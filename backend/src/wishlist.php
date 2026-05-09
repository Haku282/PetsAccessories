<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /PetsAccessories/frontend/public/index.php?page=login");
    exit;
}

$userId = $_SESSION['user_id'];
$wishlists = [];

try {
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.product_name, p.price, p.discount_price, p.thumbnail
        FROM wishlists w
        JOIN products p ON w.product_id = p.product_id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$userId]);
    $wishlists = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $wishlists = [];
}
