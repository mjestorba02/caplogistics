-- ═════════════════════════════════════════════════════════════════════════════════
-- PLT UNIFIED MIGRATION & COMPATIBILITY FIX
-- Database: logcap1
-- Purpose: Create missing tables and add compatibility columns for PLT submodules
-- Safe to run multiple times (uses conditional checks)
-- ═════════════════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────────────────────────
-- 1. ENSURE PROJECT PERFORMANCE MONITORING & CLOSURE TABLE EXISTS
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS project_performance_monitoring_closure (
    id INT AUTO_INCREMENT PRIMARY KEY,
    performance_id VARCHAR(50) UNIQUE NOT NULL,
    project_id VARCHAR(50) NOT NULL,
    reporting_period_start DATE,
    reporting_period_end DATE,
    kpi_cost_actual DECIMAL(15,2),
    kpi_cost_planned DECIMAL(15,2),
    kpi_cost_variance DECIMAL(15,2),
    kpi_cost_variance_percent DECIMAL(5,2),
    kpi_delivery_time_actual INT,
    kpi_delivery_time_planned INT,
    kpi_delivery_time_variance INT,
    kpi_milestone_adherence_percent INT,
    kpi_quality_score INT,
    kpi_customer_satisfaction INT,
    performance_status ENUM('On Track','At Risk','Off Track','Critical') DEFAULT 'On Track',
    variance_explanation TEXT,
    corrective_actions_taken TEXT,
    improvement_suggestions TEXT,
    project_closure_status ENUM('Active','In Review','Closed','Archived') DEFAULT 'Active',
    closure_date DATETIME,
    final_report_generated TINYINT DEFAULT 0,
    final_report_document_id VARCHAR(100),
    lessons_learned TEXT,
    compliance_documentation_archived TINYINT DEFAULT 0,
    archive_location VARCHAR(255),
    project_approved_for_closure TINYINT DEFAULT 0,
    approved_by_id INT,
    approved_by_name VARCHAR(255),
    approval_date DATETIME,
    total_project_cost DECIMAL(15,2),
    actual_vs_planned_analysis TEXT,
    recommendations_for_future TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_performance_id (performance_id),
    INDEX idx_project_id (project_id),
    INDEX idx_performance_status (performance_status),
    INDEX idx_project_closure_status (project_closure_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- 2. ADD COMPATIBILITY COLUMNS TO delivery_site_coordination
-- ─────────────────────────────────────────────────────────────────────────────────

-- Ensure site_location exists (canonical column in schema)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'site_location');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN site_location VARCHAR(255) NOT NULL AFTER project_id', 'SELECT "site_location_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add delivery_status (compatibility alias for delivery_confirmation_status)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'delivery_status');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN delivery_status VARCHAR(50) DEFAULT NULL', 'SELECT "delivery_status_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add site_preparation (copy from site_preparation_checklist if present)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'site_preparation');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN site_preparation TEXT DEFAULT NULL', 'SELECT "site_preparation_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Populate site_preparation from site_preparation_checklist where applicable
UPDATE delivery_site_coordination SET site_preparation = site_preparation_checklist WHERE site_preparation IS NULL AND site_preparation_checklist IS NOT NULL LIMIT 100000;

-- Add receiving_team_assigned (boolean for compatibility)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'receiving_team_assigned');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN receiving_team_assigned TINYINT(1) DEFAULT 0', 'SELECT "receiving_team_assigned_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add notes (copy from delivery_notes if present)
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' AND COLUMN_NAME = 'notes');
SET @sql = IF(@cnt = 0, 'ALTER TABLE delivery_site_coordination ADD COLUMN notes TEXT DEFAULT NULL', 'SELECT "delivery_notes_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Populate notes from delivery_notes where applicable
UPDATE delivery_site_coordination SET notes = delivery_notes WHERE notes IS NULL AND delivery_notes IS NOT NULL LIMIT 100000;

-- ─────────────────────────────────────────────────────────────────────────────────
-- 3. VERIFY project_performance_monitoring_closure HAS REQUIRED COLUMNS
-- ─────────────────────────────────────────────────────────────────────────────────

-- Add notes column if missing
SET @cnt = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'project_performance_monitoring_closure' AND COLUMN_NAME = 'notes');
SET @sql = IF(@cnt = 0, 'ALTER TABLE project_performance_monitoring_closure ADD COLUMN notes TEXT DEFAULT NULL', 'SELECT "notes_exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ─────────────────────────────────────────────────────────────────────────────────
-- 4. FINAL VERIFICATION
-- ─────────────────────────────────────────────────────────────────────────────────

-- Show current state of both tables
SELECT 'delivery_site_coordination columns:' AS info;
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_site_coordination' ORDER BY ORDINAL_POSITION;

SELECT 'project_performance_monitoring_closure columns:' AS info;
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'project_performance_monitoring_closure' ORDER BY ORDINAL_POSITION;

-- ═════════════════════════════════════════════════════════════════════════════════
-- END OF MIGRATION
-- ═════════════════════════════════════════════════════════════════════════════════
