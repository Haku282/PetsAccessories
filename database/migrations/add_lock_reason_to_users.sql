-- Thêm cột lý do khóa vào bảng users
ALTER TABLE `users` ADD COLUMN `lock_reason` TEXT DEFAULT NULL AFTER `status`;
ALTER TABLE `users` ADD COLUMN `locked_at` TIMESTAMP DEFAULT NULL AFTER `lock_reason`;
