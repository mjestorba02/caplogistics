-- ============================================================================
-- REQUEST ASSET SUBMODULE - STANDALONE SQL COMMANDS
-- Copy these commands directly into PhpMyAdmin or MySQL command line
-- ============================================================================

-- ============================================================================
-- SETUP: Run Complete Setup (Recommended)
-- ============================================================================

-- Copy all content from: C:\xampp\htdocs\newcaplog1\sql\REQUEST_ASSET_MODULE.sql
-- And execute in PhpMyAdmin SQL tab


-- ============================================================================
-- OR: Run Individual Commands Below
-- ============================================================================

-- TABLE 1: Create asset_requests
CREATE TABLE IF NOT EXISTS `asset_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `request_id` varchar(50) NOT NULL UNIQUE,
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
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_requester_id` (`requester_id`),
  KEY `idx_status` (`status`),
  KEY `idx_request_date` (`request_date`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLE 2: Create asset_request_items
CREATE TABLE IF NOT EXISTS `asset_request_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `asset_request_id` int(11) NOT NULL,
  `item_sequence` int(11) NOT NULL,
  `asset_description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `department` varchar(100) NOT NULL,
  `urgency` enum('Low','Medium','High') DEFAULT 'Medium',
  `estimated_cost` decimal(12,2),
  `item_status` enum('Pending','Approved','Rejected','In Process','Delivered') DEFAULT 'Pending',
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests`(`id`) ON DELETE CASCADE,
  KEY `idx_asset_request_id` (`asset_request_id`),
  KEY `idx_item_status` (`item_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLE 3: Create asset_request_to_procurement (Bridge)
CREATE TABLE IF NOT EXISTS `asset_request_to_procurement` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `asset_request_id` int(11) NOT NULL,
  `procurement_request_id` int(11),
  `asset_item_id` int(11) NOT NULL,
  `sent_to_procurement_date` datetime,
  `procurement_status` varchar(100) DEFAULT 'Pending',
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`procurement_request_id`) REFERENCES `procurement_requests`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`asset_item_id`) REFERENCES `asset_request_items`(`id`) ON DELETE CASCADE,
  KEY `idx_asset_request_id` (`asset_request_id`),
  KEY `idx_procurement_request_id` (`procurement_request_id`),
  KEY `idx_sent_date` (`sent_to_procurement_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- TABLE 4: Create asset_request_audit_log
CREATE TABLE IF NOT EXISTS `asset_request_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `asset_request_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `action_by` varchar(100) NOT NULL,
  `old_value` text,
  `new_value` text,
  `notes` text,
  `action_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_request_id`) REFERENCES `asset_requests`(`id`) ON DELETE CASCADE,
  KEY `idx_asset_request_id` (`asset_request_id`),
  KEY `idx_action_date` (`action_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ============================================================================
-- INSERT SAMPLE DATA (as per your table example)
-- ============================================================================

-- Sample Requests
INSERT INTO `asset_requests` 
(`request_id`, `requester_id`, `requester_name`, `requester_department`, `status`, `total_items`, `priority`, `approved_by`, `approval_date`)
VALUES
('AR-001', 1, 'John Smith', 'IT', 'Pending Approval', 1, 'High', NULL, NULL),
('AR-002', 2, 'Maria Garcia', 'HR', 'Approved', 1, 'Medium', 'Admin User', NOW()),
('AR-003', 3, 'Robert Chen', 'Finance', 'In Process', 1, 'Low', 'Admin User', NOW());

-- Sample Request Items
INSERT INTO `asset_request_items` 
(`asset_request_id`, `item_sequence`, `asset_description`, `quantity`, `department`, `urgency`, `estimated_cost`)
VALUES
(1, 1, 'Laptop', 5, 'IT', 'High', 50000.00),
(2, 1, 'Office Chairs', 10, 'HR', 'Medium', 25000.00),
(3, 1, 'Software License', 1, 'Finance', 'Low', 15000.00);


-- ============================================================================
-- VERIFY INSTALLATION (Run these to check)
-- ============================================================================

-- Check if tables exist and show row counts
SELECT 'asset_requests' as table_name, COUNT(*) as row_count FROM asset_requests
UNION ALL
SELECT 'asset_request_items', COUNT(*) FROM asset_request_items
UNION ALL
SELECT 'asset_request_to_procurement', COUNT(*) FROM asset_request_to_procurement
UNION ALL
SELECT 'asset_request_audit_log', COUNT(*) FROM asset_request_audit_log;

-- Should return:
-- table_name                    | row_count
-- asset_requests                | 3
-- asset_request_items           | 3
-- asset_request_to_procurement  | 0
-- asset_request_audit_log       | 0


-- ============================================================================
-- QUICK OPERATIONS
-- ============================================================================

-- OPERATION 1: View All Requests with Items
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

-- Result should show your 3 sample requests with their items


-- OPERATION 2: Get Only Pending Approval Requests
SELECT 
  request_id,
  requester_name,
  requester_department,
  total_items,
  priority,
  request_date
FROM asset_requests
WHERE status = 'Pending Approval'
ORDER BY priority DESC;

-- Result: AR-001 only


-- OPERATION 3: Get Only Approved Requests
SELECT 
  request_id,
  requester_name,
  requester_department,
  total_items,
  priority,
  approved_by,
  approval_date
FROM asset_requests
WHERE status = 'Approved'
ORDER BY request_date DESC;

-- Result: AR-002


-- OPERATION 4: Approve a Pending Request (Example: Approve AR-001)
UPDATE asset_requests 
SET 
  status = 'Approved',
  approval_date = NOW(),
  approved_by = 'Manager Name'
WHERE request_id = 'AR-001';

-- Also update the item status
UPDATE asset_request_items
SET item_status = 'Approved'
WHERE asset_request_id = (SELECT id FROM asset_requests WHERE request_id = 'AR-001');


-- OPERATION 5: Reject a Request (Example: Reject if needed)
UPDATE asset_requests
SET 
  status = 'Rejected',
  rejection_reason = 'Reason for rejection here'
WHERE request_id = 'AR-001';


-- OPERATION 6: Mark Request as In Process (Ready to send to Procurement)
UPDATE asset_requests
SET status = 'In Process'
WHERE request_id = 'AR-002';


-- OPERATION 7: Add New Request
INSERT INTO asset_requests 
(request_id, requester_id, requester_name, requester_department, priority)
VALUES
('AR-004', 4, 'Jane Doe', 'Operations', 'High');

-- Then add items to it
INSERT INTO asset_request_items 
(asset_request_id, item_sequence, asset_description, quantity, department, urgency, estimated_cost)
VALUES
((SELECT id FROM asset_requests WHERE request_id = 'AR-004'), 1, 'Server Equipment', 2, 'Operations', 'High', 100000.00);


-- OPERATION 8: Send Approved Request to Procurement
-- This creates an entry in the bridge table
INSERT INTO asset_request_to_procurement 
(asset_request_id, asset_item_id, sent_to_procurement_date)
SELECT 
  ar.id,
  ari.id,
  NOW()
FROM asset_requests ar
JOIN asset_request_items ari ON ar.id = ari.asset_request_id
WHERE ar.request_id = 'AR-002';


-- OPERATION 9: Track Procurement Status
SELECT 
  ar.request_id,
  ari.asset_description,
  ari.quantity,
  artp.sent_to_procurement_date,
  artp.procurement_status
FROM asset_requests ar
JOIN asset_request_items ari ON ar.id = ari.asset_request_id
JOIN asset_request_to_procurement artp ON ari.id = artp.asset_item_id
WHERE ar.status IN ('In Process', 'Completed');


-- OPERATION 10: Get Audit Trail for a Request
SELECT 
  action,
  action_by,
  action_date,
  new_value,
  notes
FROM asset_request_audit_log
WHERE asset_request_id = 1
ORDER BY action_date DESC;


-- OPERATION 11: Archive Old Requests (Example: Archive completed ones)
UPDATE asset_requests
SET 
  status = 'Archived',
  archived_at = NOW()
WHERE status = 'Completed' AND request_date < DATE_SUB(NOW(), INTERVAL 90 DAY);


-- OPERATION 12: Get Summary Statistics
SELECT 
  'Total Requests' as metric, COUNT(*) as value FROM asset_requests
UNION ALL
SELECT 'Pending Approval', COUNT(*) FROM asset_requests WHERE status = 'Pending Approval'
UNION ALL
SELECT 'Approved', COUNT(*) FROM asset_requests WHERE status = 'Approved'
UNION ALL
SELECT 'In Process', COUNT(*) FROM asset_requests WHERE status = 'In Process'
UNION ALL
SELECT 'Rejected', COUNT(*) FROM asset_requests WHERE status = 'Rejected'
UNION ALL
SELECT 'Total Items', COUNT(*) FROM asset_request_items
UNION ALL
SELECT 'High Priority Items', COUNT(*) FROM asset_request_items WHERE urgency = 'High';


-- ============================================================================
-- CLEAN UP (If needed - Delete sample data only)
-- ============================================================================

-- DELETE FROM asset_request_items;
-- DELETE FROM asset_requests;
-- DELETE FROM asset_request_to_procurement;
-- DELETE FROM asset_request_audit_log;

-- Reset auto increment if you delete all
-- ALTER TABLE asset_requests AUTO_INCREMENT = 1;
-- ALTER TABLE asset_request_items AUTO_INCREMENT = 1;


-- ============================================================================
-- DROP TABLES (If you want to remove everything)
-- ============================================================================

-- DROP TABLE IF EXISTS asset_request_audit_log;
-- DROP TABLE IF EXISTS asset_request_to_procurement;
-- DROP TABLE IF EXISTS asset_request_items;
-- DROP TABLE IF EXISTS asset_requests;
