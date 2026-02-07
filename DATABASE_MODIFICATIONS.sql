-- ============================================================================
-- DATABASE MODIFICATIONS FOR NEW FEATURES
-- Run this in phpMyAdmin to add all required tables and columns
-- ============================================================================

-- 1. ALTER users table to add account_type and OTP fields
ALTER TABLE `users` ADD COLUMN `account_type` INT DEFAULT 0 COMMENT '1=Admin, 0=Regular User' AFTER `email`;
ALTER TABLE `users` ADD COLUMN `is_otp_enabled` TINYINT(1) DEFAULT 1 COMMENT 'Enable OTP for this user' AFTER `account_type`;

-- 2. Create OTP table for storing generated OTPs
CREATE TABLE IF NOT EXISTS `user_otps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `otp_code` VARCHAR(6) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL,
    `is_used` TINYINT(1) DEFAULT 0,
    `used_at` TIMESTAMP NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_otp_code (otp_code),
    INDEX idx_expires_at (expires_at)
);

-- 3. Create table for archived items (generic archive table)
CREATE TABLE IF NOT EXISTS `archived_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `archive_type` VARCHAR(100) NOT NULL COMMENT 'Type of item archived (contract, request, etc.)',
    `item_id` INT NOT NULL,
    `original_table` VARCHAR(100) NOT NULL COMMENT 'Name of the original table',
    `item_data` JSON NOT NULL COMMENT 'Full data of archived item as JSON',
    `archived_by` VARCHAR(100),
    `archived_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `reason` TEXT,
    `restore_allowed` TINYINT(1) DEFAULT 1,
    INDEX idx_archive_type (archive_type),
    INDEX idx_item_id (item_id),
    INDEX idx_archived_at (archived_at)
);

-- 4. Update existing admin users to have account_type = 1
-- Assuming IDs 3 and 8 are admins based on the data
UPDATE `users` SET `account_type` = 1 WHERE `id` IN (3, 8);

-- 5. Add archived_at column to procurement_contracts table if not exists
ALTER TABLE `procurement_contracts` 
ADD COLUMN `archived_at` DATETIME NULL AFTER `status`;

-- 6. Add archive status to procurement_contracts
ALTER TABLE `procurement_contracts` 
MODIFY COLUMN `status` ENUM('Active','Pending','Terminated','Expired','Cancelled','Completed','Archived') DEFAULT 'Pending';

-- Sample: Set first user as admin for testing
-- UPDATE `users` SET `account_type` = 1 WHERE `id` = 3;
