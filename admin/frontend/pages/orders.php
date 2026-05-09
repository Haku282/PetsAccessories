<?php
/**
 * REDIRECT FILE - Orders Management
 * Old URL: /admin/frontend/pages/orders.php
 * New URL: /admin/frontend/pages/orders/index.php
 * 
 * This file redirects to the new location for backward compatibility
 * When someone visits the old URL, they will be automatically redirected to the new location
 * with a 301 (Permanent) redirect status code
 */

// Redirect to new location with 301 (Permanent Redirect)
header('Location: /PetsAccessories/admin/frontend/pages/orders/index.php', true, 301);

// Exit to ensure no further code is executed
exit;
?>
