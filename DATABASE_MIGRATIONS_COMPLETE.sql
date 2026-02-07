-- ============================================================================
-- DATABASE MODIFICATIONS FOR NEW FEATURES
-- Run this file in phpMyAdmin to implement all changes
-- ============================================================================
-- Date: February 7, 2026
-- System: iMarket E-Commerce Logistics Management
-- Purpose: Add OTP, Archive, Admin, and Report Security features
-- ============================================================================

-- ============================================================================
-- STEP 1: ALTER USERS TABLE - Add new columns
-- ============================================================================
-- Add account_type column (1=Admin, 0=Regular User)
ALTER TABLE `users` ADD COLUMN `account_type` INT DEFAULT 0 COMMENT '1=Admin, 0=Regular User' AFTER `email`;

-- Add is_otp_enabled column (Enable/disable OTP for user)
ALTER TABLE `users` ADD COLUMN `is_otp_enabled` TINYINT(1) DEFAULT 1 COMMENT 'Enable OTP for this user' AFTER `account_type`;

-- ============================================================================
-- STEP 2: CREATE USER_OTPS TABLE - Store OTP codes
-- ============================================================================
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
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_used (is_used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 3: CREATE ARCHIVED_ITEMS TABLE - Store archived items
-- ============================================================================
CREATE TABLE IF NOT EXISTS `archived_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `archive_type` VARCHAR(100) NOT NULL COMMENT 'Type of item archived (contract, supplier, request, etc.)',
    `item_id` INT NOT NULL,
    `original_table` VARCHAR(100) NOT NULL COMMENT 'Name of the original table',
    `item_data` JSON NOT NULL COMMENT 'Full data of archived item as JSON',
    `archived_by` VARCHAR(100),
    `archived_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `reason` TEXT,
    `restore_allowed` TINYINT(1) DEFAULT 1,
    INDEX idx_archive_type (archive_type),
    INDEX idx_item_id (item_id),
    INDEX idx_archived_at (archived_at),
    INDEX idx_original_table (original_table)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STEP 4: ADD ARCHIVED_AT COLUMN TO PROCUREMENT_CONTRACTS (if needed)
-- ============================================================================
-- This adds support for archiving contracts
ALTER TABLE `procurement_contracts` 
ADD COLUMN `archived_at` DATETIME NULL AFTER `status`;

-- Update status ENUM to include 'Archived' if not present
ALTER TABLE `procurement_contracts` 
MODIFY COLUMN `status` ENUM('Active','Pending','Terminated','Expired','Cancelled','Completed','Archived') DEFAULT 'Pending';

-- ============================================================================
-- STEP 5: SET ADMIN USERS
-- ============================================================================
-- Set user ID 3 and 8 as admins (modify IDs as needed for your system)
UPDATE `users` SET `account_type` = 1 WHERE `id` IN (3, 8);

-- Verify admin users were set
-- SELECT id, name, email, account_type FROM users WHERE account_type = 1;

-- ============================================================================
-- STEP 6: OPTIONAL - SET OTP ENABLED FOR ALL USERS
-- ============================================================================
-- By default, all users have OTP enabled (is_otp_enabled = 1)
-- To disable for specific users:
-- UPDATE `users` SET `is_otp_enabled` = 0 WHERE `id` = 5;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================
-- Run these to verify the changes were applied correctly

-- Check users table new columns
-- DESCRIBE users;

-- Check user_otps table
-- SHOW TABLES LIKE 'user_otps';
-- DESCRIBE user_otps;

-- Check archived_items table
-- SHOW TABLES LIKE 'archived_items';
-- DESCRIBE archived_items;

-- Check procurement_contracts table
-- DESCRIBE procurement_contracts;

-- Check admin users
-- SELECT id, name, email, account_type FROM users WHERE account_type = 1;

-- Check OTP-enabled users
-- SELECT id, name, email, is_otp_enabled FROM users;

-- ============================================================================
-- ROLLBACK SCRIPT (if needed to undo changes)
-- ============================================================================
-- DO NOT RUN unless you need to undo these changes
-- Uncomment below to rollback:

/*
-- Drop archived_items table
DROP TABLE IF EXISTS `archived_items`;

-- Drop user_otps table
DROP TABLE IF EXISTS `user_otps`;

-- Remove columns from users table
ALTER TABLE `users` DROP COLUMN IF EXISTS `is_otp_enabled`;
ALTER TABLE `users` DROP COLUMN IF EXISTS `account_type`;

-- Remove archived_at from procurement_contracts
ALTER TABLE `procurement_contracts` DROP COLUMN IF EXISTS `archived_at`;
*/

-- ============================================================================
-- END OF MIGRATIONS
-- ============================================================================
-- All tables and columns have been created/modified successfully
-- The system is now ready for the new features:
-- 1. OTP Authentication
-- 2. Archive System (replaces Delete)
-- 3. User Types with Admin Controls
-- 4. Password Confirmation for Reports
-- ============================================================================
