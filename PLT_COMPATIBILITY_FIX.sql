-- PLT compatibility migration
-- Adds compatibility columns expected by the front-end and copies existing values where appropriate
-- Run against your `logcap1` database

-- NOTE: This script uses prepared-statement conditionals so it can be run safely from mysql client.

-- 1) Ensure the performance table exists (run migration file first if present)
-- If you haven't run `PLT_PERFORMANCE_SETUP.sql` yet, run it now.

-- 2) delivery_site_coordination: add compatibility columns if missing and copy values
-- delivery_status (alias of delivery_confirmation_status)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'delivery_status');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN delivery_status VARCHAR(50) DEFAULT NULL', 'SELECT "delivery_status_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- site_preparation (copy from site_preparation_checklist)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'site_preparation');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN site_preparation TEXT DEFAULT NULL', 'SELECT "site_preparation_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
-- copy checklist into new column if present
UPDATE delivery_site_coordination SET site_preparation = site_preparation_checklist WHERE site_preparation IS NULL AND site_preparation_checklist IS NOT NULL;

-- receiving_team_assigned (boolean)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'receiving_team_assigned');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN receiving_team_assigned TINYINT(1) DEFAULT 0', 'SELECT "receiving_team_assigned_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- notes (copy from delivery_notes)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'notes');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN notes TEXT DEFAULT NULL', 'SELECT "delivery_notes_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
UPDATE delivery_site_coordination SET notes = delivery_notes WHERE notes IS NULL AND delivery_notes IS NOT NULL;

-- site_location: ensure exists (schema already uses it, but include safety)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'site_location');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN site_location VARCHAR(255) NOT NULL AFTER project_id', 'SELECT "site_location_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) project_performance_monitoring_closure: ensure `notes` column exists
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'project_performance_monitoring_closure' AND COLUMN_NAME = 'notes');
SET @sql = IF(@cnt = 0, 'ALTER TABLE project_performance_monitoring_closure ADD COLUMN notes TEXT DEFAULT NULL', 'SELECT "performance_notes_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- End of migration
