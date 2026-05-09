<?php
/**
 * Helper functions for Orders Module
 * File: /admin/backend/utils/orders_helper.php
 */

/**
 * Get order by ID
 * 
 * @param PDO $db Database connection
 * @param int $orderId Order ID
 * @return array|null Order data or null
 */
function getOrderById($db, $orderId) {
    try {
        $stmt = $db->prepare("
            SELECT o.*, u.user_id, u.username, u.email, u.phone, u.address
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            WHERE o.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get order items
 * 
 * @param PDO $db Database connection
 * @param int $orderId Order ID
 * @return array Order items
 */
function getOrderItems($db, $orderId) {
    try {
        $stmt = $db->prepare("
            SELECT oi.*, p.product_name, p.price, p.thumbnail as image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
            ORDER BY oi.order_item_id ASC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get order status history
 * 
 * @param PDO $db Database connection
 * @param int $orderId Order ID
 * @return array Status history
 */
function getOrderStatusHistory($db, $orderId) {
    try {
        $stmt = $db->prepare("
            SELECT * FROM order_status_history
            WHERE order_id = ?
            ORDER BY changed_at DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get order logs
 * 
 * @param PDO $db Database connection
 * @param int $orderId Order ID
 * @return array Order logs
 */
function getOrderLogs($db, $orderId) {
    try {
        $stmt = $db->prepare("
            SELECT ol.*, a.username as admin_name
            FROM order_logs ol
            LEFT JOIN users a ON ol.admin_id = a.user_id
            WHERE ol.order_id = ?
            ORDER BY ol.changed_at DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get order status info (label + color)
 * 
 * @param string $status Order status
 * @return array Status info with label and color
 */
function getOrderStatusInfo($status) {
    $statusMap = [
        'pending' => ['label' => 'Chờ xác nhận', 'color' => '#FFC107', 'class' => 'status-pending'],
        'confirmed' => ['label' => 'Đã xác nhận', 'color' => '#2196F3', 'class' => 'status-confirmed'],
        'shipping' => ['label' => 'Đang giao', 'color' => '#4CAF50', 'class' => 'status-shipping'],
        'completed' => ['label' => 'Hoàn thành', 'color' => '#1B5E20', 'class' => 'status-completed'],
        'cancelled' => ['label' => 'Hủy', 'color' => '#F44336', 'class' => 'status-cancelled'],
    ];
    
    return $statusMap[$status] ?? ['label' => $status, 'color' => '#999', 'class' => ''];
}

/**
 * Get payment status info
 * 
 * @param string $status Payment status
 * @return array Payment status info
 */
function getPaymentStatusInfo($status) {
    $statusMap = [
        'unpaid' => ['label' => 'Chưa thanh toán', 'color' => '#FFC107'],
        'paid' => ['label' => 'Đã thanh toán', 'color' => '#4CAF50'],
        'refunded' => ['label' => 'Hoàn tiền', 'color' => '#9C27B0'],
    ];
    
    return $statusMap[$status] ?? ['label' => $status, 'color' => '#999'];
}

/**
 * Validate order status transition
 * 
 * @param string $currentStatus Current status
 * @param string $newStatus New status
 * @return bool True if valid transition
 */
function isValidStatusTransition($currentStatus, $newStatus) {
    // Define valid transitions
    $validTransitions = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipping', 'cancelled'],
        'shipping' => ['completed', 'cancelled'],
        'completed' => [],  // Terminal state
        'cancelled' => [],  // Terminal state
    ];
    
    return isset($validTransitions[$currentStatus]) && 
           in_array($newStatus, $validTransitions[$currentStatus]);
}

/**
 * Calculate order total
 * 
 * @param array $items Order items
 * @return float Total amount
 */
function calculateOrderTotal($items) {
    $total = 0;
    foreach ($items as $item) {
        $total += ($item['quantity'] * $item['price']);
    }
    return $total;
}

/**
 * Format currency (VND)
 * 
 * @param float $amount Amount
 * @return string Formatted currency
 */
function formatCurrency($amount) {
    return number_format($amount, 0, '.', ',') . ' đ';
}

/**
 * Create order status history record
 * 
 * @param PDO $db Database connection
 * @param int $orderId Order ID
 * @param string $oldStatus Old status
 * @param string $newStatus New status
 * @return bool Success
 */
function addStatusHistory($db, $orderId, $newStatus) { // Bỏ old_status
    try {
        $stmt = $db->prepare("
            INSERT INTO order_status_history (order_id, status, changed_at)
            VALUES (?, ?, NOW())
        ");
        return $stmt->execute([$orderId, $newStatus]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Log order action
 * 
 * @param PDO $db Database connection
 * @param int $orderId Order ID
 * @param int $adminId Admin user ID
 * @param string $action Action performed
 * @param string $reason Reason for action
 * @return bool Success
 */
function logOrderAction($db, $orderId, $adminId, $oldStatus, $newStatus, $reason = '') { // Đổi params cho đúng CSDL
    try {
        $stmt = $db->prepare("
            INSERT INTO order_logs (order_id, admin_id, old_status, new_status, reason, changed_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$orderId, $adminId, $oldStatus, $newStatus, $reason]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get all orders with pagination
 * 
 * @param PDO $db Database connection
 * @param int $page Page number
 * @param string $status Filter by status
 * @param string $search Search query
 * @param int $perPage Items per page
 * @return array Orders data
 */
function getAllOrders($db, $page = 1, $status = null, $search = '', $perPage = 10) {
    try {
        $offset = ($page - 1) * $perPage;
        $query = "
            SELECT o.*, u.username, u.email, u.phone,
                   COUNT(oi.order_item_id) as item_count,
                   SUM(oi.quantity * oi.price_at_purchase) as total
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $query .= " AND o.order_status = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $query .= " AND (u.username LIKE ? OR u.email LIKE ? OR o.order_id LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $query .= " GROUP BY o.order_id ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Count total orders
 * 
 * @param PDO $db Database connection
 * @param string $status Filter by status
 * @param string $search Search query
 * @return int Total count
 */
function countTotalOrders($db, $status = null, $search = '') {
    try {
        $query = "SELECT COUNT(*) as total FROM orders WHERE 1=1";
        $params = [];
        
        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $query .= " AND (user_id IN (SELECT user_id FROM users WHERE username LIKE ? OR email LIKE ?) OR order_id LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get order statistics
 * 
 * @param PDO $db Database connection
 * @return array Statistics
 */
function getOrderStatistics($db) {
    try {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'confirmed' => 0,
            'shipping' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'total_revenue' => 0,
        ];
        
        // Get count by status
        foreach (['pending', 'confirmed', 'shipping', 'completed', 'cancelled'] as $status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE status = ?");
            $stmt->execute([$status]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats[$status] = $result['count'] ?? 0;
        }
        
        // Get total revenue
        $stmt = $db->query("
            SELECT SUM(oi.quantity * oi.price_at_purchase) as total
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.order_status = 'completed'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = $result['total'] ?? 0;
        
        // Get total orders
        $stats['total'] = array_sum(array_values(array_diff_key($stats, ['total_revenue' => 0])));
        
        return $stats;
    } catch (PDOException $e) {
        return [];
    }
}
?>
