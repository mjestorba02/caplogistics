-- ============================================================================
-- REQUEST ASSET SUBMODULE - COPY & PASTE SQL COMMAND
-- This is the COMPLETE command - just copy everything and paste in PhpMyAdmin
-- ============================================================================

-- ===========================================================================
-- TABLE 1: ASSET_REQUESTS (Main Request Table)
-- ===========================================================================
CREATE TABLE IF NOT EXISTS `asset_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` varchar(50) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `requester_name` varchar(100) NOT NULL,
  `requester_department` varchar(100) NOT NULL,
  `request_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending Approval','Approved','Rejected','In Process','Completed','Archived') DEFAULT 'Pending Approval',
  `total_items` int(11) NOT NULL DEFAULT 0,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `notes` text,
  `approval_date` datetime,
  `approved_by` varchar(100),
  `rejection_reason` text,
  `archived_at` datetime,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_id` (`request_id`),
  KEY `idx_requester_id` (`requester_id`),
  KEY `idx_status` (`status`),
  KEY `idx_request_date` (`request_date`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===========================================================================
-- TABLE 2: ASSET_REQUEST_ITEMS (Individual Items in Requests)
-- ===========================================================================
CREATE TABLE IF NOT EXISTS `asset_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_request_id` int(11) NOT NULL,
  `item_sequence` int(11) NOT NULL,
  `asset_description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `department` varchar(100) NOT NULL,
  `urgency` enum('Low','Medium','High') DEFAULT 'Medium',
  `estimated_cost` decimal(12,2),
  `item_status` enum('Pending','Approved','Rejected','In Process','Delivered') DEFAULT 'Pending',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_asset_request_id` (`asset_request_id`),
  KEY `idx_item_status` (`item_status`),
  CONSTRAINT `asset_request_items_ibfk_1` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===========================================================================
-- TABLE 3: ASSET_REQUEST_TO_PROCUREMENT (Bridge Table)
-- ===========================================================================
CREATE TABLE IF NOT EXISTS `asset_request_to_procurement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_request_id` int(11) NOT NULL,
  `procurement_request_id` int(11),
  `asset_item_id` int(11) NOT NULL,
  `sent_to_procurement_date` datetime,
  `procurement_status` varchar(100) DEFAULT 'Pending',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_asset_request_id` (`asset_request_id`),
  KEY `idx_procurement_request_id` (`procurement_request_id`),
  KEY `idx_sent_date` (`sent_to_procurement_date`),
  CONSTRAINT `asset_request_to_procurement_ibfk_1` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_request_to_procurement_ibfk_2` FOREIGN KEY (`procurement_request_id`) REFERENCES `procurement_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `asset_request_to_procurement_ibfk_3` FOREIGN KEY (`asset_item_id`) REFERENCES `asset_request_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===========================================================================
-- TABLE 4: ASSET_REQUEST_AUDIT_LOG (Audit Trail)
-- ===========================================================================
CREATE TABLE IF NOT EXISTS `asset_request_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_request_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `action_by` varchar(100) NOT NULL,
  `old_value` text,
  `new_value` text,
  `notes` text,
  `action_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_asset_request_id` (`asset_request_id`),
  KEY `idx_action_date` (`action_date`),
  CONSTRAINT `asset_request_audit_log_ibfk_1` FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ===========================================================================
-- INSERT SAMPLE DATA
-- ===========================================================================

INSERT INTO `asset_requests` (`request_id`, `requester_id`, `requester_name`, `requester_department`, `status`, `total_items`, `priority`, `approved_by`, `approval_date`) VALUES
('AR-001', 1, 'John Smith', 'IT', 'Pending Approval', 1, 'High', NULL, NULL),
('AR-002', 2, 'Maria Garcia', 'HR', 'Approved', 1, 'Medium', 'Admin User', NOW()),
('AR-003', 3, 'Robert Chen', 'Finance', 'In Process', 1, 'Low', 'Admin User', NOW());

INSERT INTO `asset_request_items` (`asset_request_id`, `item_sequence`, `asset_description`, `quantity`, `department`, `urgency`, `estimated_cost`) VALUES
(1, 1, 'Laptop', 5, 'IT', 'High', 50000.00),
(2, 1, 'Office Chairs', 10, 'HR', 'Medium', 25000.00),
(3, 1, 'Software License', 1, 'Finance', 'Low', 15000.00);

-- ===========================================================================
-- VERIFY INSTALLATION (Run this to check)
-- ===========================================================================

-- Should show 3 rows in each table:
-- SELECT COUNT(*) FROM asset_requests;         -- Should show: 3
-- SELECT COUNT(*) FROM asset_request_items;    -- Should show: 3
-- SELECT COUNT(*) FROM asset_request_to_procurement;  -- Should show: 0
-- SELECT COUNT(*) FROM asset_request_audit_log;       -- Should show: 0

-- ===========================================================================
-- Test Query 1: View All Requests with Items
-- ===========================================================================

/*
SELECT 
  ar.request_id,
  ar.requester_name,
  ar.requester_department,
  ar.status,
  ar.priority,
  ari.asset_description,
  ari.quantity,
  ari.urgency,
  ari.estimated_cost
FROM asset_requests ar
LEFT JOIN asset_request_items ari ON ar.id = ari.asset_request_id
ORDER BY ar.request_date DESC;
*/

-- ===========================================================================
-- Test Query 2: Get Pending Approval Requests
-- ===========================================================================

/*
SELECT 
  request_id,
  requester_name,
  requester_department,
  priority,
  request_date
FROM asset_requests
WHERE status = 'Pending Approval'
ORDER BY priority DESC;
*/

-- Expected Result: AR-001

-- ===========================================================================
-- Test Query 3: Approve a Request (Example)
-- ===========================================================================

/*
UPDATE asset_requests 
SET 
  status = 'Approved',
  approval_date = NOW(),
  approved_by = 'Manager Name'
WHERE request_id = 'AR-001';
*/

-- ===========================================================================
-- SUMMARY
-- ===========================================================================
-- 4 Tables Created:
-- 1. asset_requests ..................... Main request table
-- 2. asset_request_items ............... Items in each request  
-- 3. asset_request_to_procurement ..... Links to procurement module
-- 4. asset_request_audit_log .......... Audit trail
--
-- Sample Data:
-- AR-001: Laptop (5) - IT - High - Pending Approval
-- AR-002: Chairs (10) - HR - Medium - Approved
-- AR-003: Software (1) - Finance - Low - In Process
--
-- Status: Ready to use
-- ===========================================================================
