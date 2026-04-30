<?php
/**
 * Helper functions for Users Module
 * File: /admin/backend/utils/users_helper.php
 */

/**
 * Get user by ID
 * 
 * @param PDO $db Database connection
 * @param int $userId User ID
 * @return array|null User data or null
 */
function getUserById($db, $userId) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Check if username exists
 * 
 * @param PDO $db Database connection
 * @param string $username Username to check
 * @param int $excludeId User ID to exclude (for update)
 * @return bool True if exists
 */
function usernameExists($db, $username, $excludeId = null) {
    try {
        if ($excludeId) {
            $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
        }
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Check if email exists
 * 
 * @param PDO $db Database connection
 * @param string $email Email to check
 * @param int $excludeId User ID to exclude (for update)
 * @return bool True if exists
 */
function emailExists($db, $email, $excludeId = null) {
    try {
        if ($excludeId) {
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
        }
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Hash password securely
 * 
 * @param string $password Plain password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password
 * 
 * @param string $password Plain password
 * @param string $hash Password hash
 * @return bool True if matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate email format
 * 
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get total users count
 * 
 * @param PDO $db Database connection
 * @param string $role Filter by role (optional)
 * @param int $status Filter by status (optional)
 * @return int Total count
 */
function getTotalUsersCount($db, $role = '', $status = '') {
    try {
        $sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        $params = [];

        if (!empty($role)) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = (int)$status;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get users list with pagination and filters
 * 
 * @param PDO $db Database connection
 * @param int $page Page number
 * @param int $limit Items per page
 * @param string $role Filter by role
 * @param int $status Filter by status (-1 for no filter)
 * @param string $search Search term
 * @return array Array of users
 */
function getUsersList($db, $page = 1, $limit = 10, $role = '', $status = -1, $search = '') {
    try {
        $sql = "SELECT user_id, username, email, fullname, phone, role, status, created_at 
                FROM users WHERE 1=1";
        $params = [];

        if (!empty($role)) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if ($status >= 0) {
            $sql .= " AND status = ?";
            $params[] = (int)$status;
        }

        if (!empty($search)) {
            $sql .= " AND (username LIKE ? OR email LIKE ? OR fullname LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $offset = ($page - 1) * $limit;
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Create new user
 * 
 * @param PDO $db Database connection
 * @param array $data User data
 * @return array Result with success flag and message
 */
function createUser($db, $data) {
    $errors = [];

    // Validation
    if (empty($data['username'])) $errors[] = 'Username không được để trống';
    if (empty($data['email'])) $errors[] = 'Email không được để trống';
    if (empty($data['password'])) $errors[] = 'Mật khẩu không được để trống';

    if (strlen($data['username'] ?? '') < 3) $errors[] = 'Username phải có ít nhất 3 ký tự';
    if (strlen($data['password'] ?? '') < 6) $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
    if (!validateEmail($data['email'] ?? '')) $errors[] = 'Email không hợp lệ';

    if (!empty($errors)) {
        return ['success' => false, 'message' => implode(', ', $errors)];
    }

    // Check duplicates
    if (usernameExists($db, $data['username'])) {
        return ['success' => false, 'message' => 'Username đã được sử dụng'];
    }
    if (emailExists($db, $data['email'])) {
        return ['success' => false, 'message' => 'Email đã được sử dụng'];
    }

    try {
        $sql = "INSERT INTO users (username, email, password, fullname, phone, address, role, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['username'],
            $data['email'],
            hashPassword($data['password']),
            $data['fullname'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['role'] ?? 'customer',
            $data['status'] ?? 1
        ]);

        return [
            'success' => true,
            'message' => 'Tạo tài khoản thành công',
            'user_id' => $db->lastInsertId()
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi tạo tài khoản: ' . $e->getMessage()];
    }
}

/**
 * Update user
 * 
 * @param PDO $db Database connection
 * @param int $userId User ID
 * @param array $data User data to update
 * @return array Result with success flag and message
 */
function updateUser($db, $userId, $data) {
    try {
        $user = getUserById($db, $userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Tài khoản không tồn tại'];
        }

        $updates = [];
        $params = [];

        if (isset($data['email']) && !empty($data['email'])) {
            if (!validateEmail($data['email'])) {
                return ['success' => false, 'message' => 'Email không hợp lệ'];
            }
            if ($data['email'] !== $user['email'] && emailExists($db, $data['email'], $userId)) {
                return ['success' => false, 'message' => 'Email đã được sử dụng'];
            }
            $updates[] = "email = ?";
            $params[] = $data['email'];
        }

        if (isset($data['fullname'])) {
            $updates[] = "fullname = ?";
            $params[] = $data['fullname'];
        }

        if (isset($data['phone'])) {
            $updates[] = "phone = ?";
            $params[] = $data['phone'];
        }

        if (isset($data['address'])) {
            $updates[] = "address = ?";
            $params[] = $data['address'];
        }

        if (isset($data['role']) && in_array($data['role'], ['admin', 'customer'])) {
            $updates[] = "role = ?";
            $params[] = $data['role'];
        }

        if (isset($data['status']) && in_array($data['status'], [0, 1])) {
            $updates[] = "status = ?";
            $params[] = $data['status'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
            }
            $updates[] = "password = ?";
            $params[] = hashPassword($data['password']);
        }

        if (empty($updates)) {
            return ['success' => false, 'message' => 'Không có dữ liệu để cập nhật'];
        }

        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Cập nhật tài khoản thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()];
    }
}

/**
 * Delete user
 * 
 * @param PDO $db Database connection
 * @param int $userId User ID
 * @param int $currentUserId Current admin user ID
 * @return array Result with success flag and message
 */
function deleteUser($db, $userId, $currentUserId) {
    try {
        if ($userId === $currentUserId) {
            return ['success' => false, 'message' => 'Không thể xóa tài khoản của chính bạn'];
        }

        $user = getUserById($db, $userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Tài khoản không tồn tại'];
        }

        $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);

        return ['success' => true, 'message' => 'Xóa tài khoản thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()];
    }
}

/**
 * Toggle user status (lock/unlock)
 * 
 * @param PDO $db Database connection
 * @param int $userId User ID
 * @param int $newStatus New status (0 or 1)
 * @param int $currentUserId Current admin user ID
 * @return array Result with success flag and message
 */
function toggleUserStatus($db, $userId, $newStatus, $currentUserId) {
    try {
        if ($userId === $currentUserId) {
            return ['success' => false, 'message' => 'Không thể thay đổi trạng thái của tài khoản của chính bạn'];
        }

        if (!in_array($newStatus, [0, 1])) {
            return ['success' => false, 'message' => 'Trạng thái không hợp lệ'];
        }

        $user = getUserById($db, $userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Tài khoản không tồn tại'];
        }

        $stmt = $db->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->execute([$newStatus, $userId]);

        $statusText = $newStatus === 1 ? 'mở khóa' : 'khóa';
        return ['success' => true, 'message' => 'Đã ' . $statusText . ' tài khoản thành công'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}
?>
