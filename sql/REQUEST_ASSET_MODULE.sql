-- ============================================================================
-- REQUEST ASSET SUBMODULE (Asset Management Module)
-- This submodule handles asset requests which flow to Procurement > Request Supplies
-- ============================================================================

-- ============================================================================
-- TABLE 1: asset_requests (Main Request Asset Table)
-- This table stores all asset requests from the Asset Management module
-- ============================================================================
CREATE TABLE IF NOT EXISTS asset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id VARCHAR(50) NOT NULL UNIQUE,
    requester_id INT NOT NULL,
    requester_name VARCHAR(100) NOT NULL,
    requester_department VARCHAR(100) NOT NULL,
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending Approval', 'Approved', 'Rejected', 'In Process', 'Completed', 'Archived') DEFAULT 'Pending Approval',
    total_items INT NOT NULL DEFAULT 0,
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    notes TEXT,
    approval_date DATETIME,
    approved_by VARCHAR(100),
    rejection_reason TEXT,
    archived_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_requester_id (requester_id),
    INDEX idx_status (status),
    INDEX idx_request_date (request_date),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE 2: asset_request_items (Individual items within each request)
-- This table stores the individual assets being requested
-- ============================================================================
CREATE TABLE IF NOT EXISTS asset_request_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_request_id INT NOT NULL,
    item_sequence INT NOT NULL,
    asset_description VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    department VARCHAR(100) NOT NULL,
    urgency ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    estimated_cost DECIMAL(12,2),
    item_status ENUM('Pending', 'Approved', 'Rejected', 'In Process', 'Delivered') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_request_id) REFERENCES asset_requests(id) ON DELETE CASCADE,
    INDEX idx_asset_request_id (asset_request_id),
    INDEX idx_item_status (item_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE 3: asset_request_to_procurement (Bridge table for linking to Procurement)
-- This table tracks when an asset request is sent to Procurement Module
-- ============================================================================
CREATE TABLE IF NOT EXISTS asset_request_to_procurement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_request_id INT NOT NULL,
    procurement_request_id INT,
    asset_item_id INT NOT NULL,
    sent_to_procurement_date DATETIME,
    procurement_status VARCHAR(100) DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_request_id) REFERENCES asset_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (procurement_request_id) REFERENCES procurement_requests(id) ON DELETE SET NULL,
    FOREIGN KEY (asset_item_id) REFERENCES asset_request_items(id) ON DELETE CASCADE,
    INDEX idx_asset_request_id (asset_request_id),
    INDEX idx_procurement_request_id (procurement_request_id),
    INDEX idx_sent_date (sent_to_procurement_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE 4: asset_request_audit_log (Audit trail for all changes)
-- This table maintains a complete audit trail of all request changes
-- ============================================================================
CREATE TABLE IF NOT EXISTS asset_request_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_request_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    action_by VARCHAR(100) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    notes TEXT,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_request_id) REFERENCES asset_requests(id) ON DELETE CASCADE,
    INDEX idx_asset_request_id (asset_request_id),
    INDEX idx_action_date (action_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- SAMPLE DATA (as per your example)
-- ============================================================================
INSERT INTO asset_requests (request_id, requester_id, requester_name, requester_department, status, total_items, priority, approved_by, approval_date)
VALUES
    ('AR-001', 1, 'John Smith', 'IT', 'Pending Approval', 1, 'High', NULL, NULL),
    ('AR-002', 2, 'Maria Garcia', 'HR', 'Approved', 1, 'Medium', 'Admin User', NOW()),
    ('AR-003', 3, 'Robert Chen', 'Finance', 'In Process', 1, 'Low', 'Admin User', NOW());

INSERT INTO asset_request_items (asset_request_id, item_sequence, asset_description, quantity, department, urgency, estimated_cost)
VALUES
    (1, 1, 'Laptop', 5, 'IT', 'High', 50000.00),
    (2, 1, 'Office Chairs', 10, 'HR', 'Medium', 25000.00),
    (3, 1, 'Software License', 1, 'Finance', 'Low', 15000.00);

-- ============================================================================
-- SAMPLE VIEWS FOR EASIER QUERYING
-- ============================================================================

-- View: All Asset Requests with Item Count
DROP VIEW IF EXISTS vw_asset_requests_summary;
CREATE VIEW vw_asset_requests_summary AS
SELECT 
    ar.id,
    ar.request_id,
    ar.requester_name,
    ar.requester_department,
    ar.status,
    ar.priority,
    ar.total_items,
    COUNT(ari.id) as actual_item_count,
    ar.request_date,
    ar.approval_date,
    ar.approved_by
FROM asset_requests ar
LEFT JOIN asset_request_items ari ON ar.id = ari.asset_request_id
GROUP BY ar.id, ar.request_id, ar.requester_name, ar.requester_department, 
         ar.status, ar.priority, ar.total_items, ar.request_date, ar.approval_date, ar.approved_by
ORDER BY ar.request_date DESC;

-- View: All Asset Request Items with Request Details
DROP VIEW IF EXISTS vw_asset_request_items_detail;
CREATE VIEW vw_asset_request_items_detail AS
SELECT 
    ari.id as item_id,
    ar.request_id,
    ar.requester_name,
    ari.asset_description,
    ari.quantity,
    ari.department,
    ari.urgency,
    ari.item_status,
    ari.estimated_cost,
    ar.status as request_status,
    ar.request_date
FROM asset_request_items ari
JOIN asset_requests ar ON ari.asset_request_id = ar.id
ORDER BY ar.request_date DESC, ari.item_sequence;

-- View: Pending and Approved Requests Ready for Procurement
DROP VIEW IF EXISTS vw_asset_requests_for_procurement;
CREATE VIEW vw_asset_requests_for_procurement AS
SELECT 
    ar.id,
    ar.request_id,
    ari.id as item_id,
    ari.asset_description,
    ari.quantity,
    ari.department,
    ari.urgency,
    ari.estimated_cost,
    ar.requester_name,
    ar.requester_id,
    ar.status
FROM asset_requests ar
JOIN asset_request_items ari ON ar.id = ari.asset_request_id
WHERE ar.status IN ('Approved', 'In Process')
  AND ari.item_status IN ('Pending', 'Approved')
ORDER BY ari.urgency DESC, ar.request_date ASC;

-- ============================================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- ============================================================================

-- Procedure: Create New Asset Request
DROP PROCEDURE IF EXISTS sp_create_asset_request;
DELIMITER $$
CREATE PROCEDURE sp_create_asset_request(
    IN p_requester_id INT,
    IN p_requester_name VARCHAR(100),
    IN p_department VARCHAR(100),
    IN p_priority ENUM('Low', 'Medium', 'High'),
    IN p_notes TEXT,
    OUT p_request_id VARCHAR(50),
    OUT p_status VARCHAR(50)
)
BEGIN
    DECLARE v_max_id INT;
    
    -- Get the next request ID
    SELECT COALESCE(MAX(CAST(SUBSTRING(request_id, 4) AS UNSIGNED)), 0) + 1 INTO v_max_id
    FROM asset_requests;
    
    SET p_request_id = CONCAT('AR-', LPAD(v_max_id, 3, '0'));
    
    INSERT INTO asset_requests (request_id, requester_id, requester_name, requester_department, priority, notes)
    VALUES (p_request_id, p_requester_id, p_requester_name, p_department, p_priority, p_notes);
    
    SET p_status = 'SUCCESS';
END$$
DELIMITER ;

-- Procedure: Add Item to Asset Request
DROP PROCEDURE IF EXISTS sp_add_asset_request_item;
DELIMITER $$
CREATE PROCEDURE sp_add_asset_request_item(
    IN p_request_id INT,
    IN p_asset_description VARCHAR(255),
    IN p_quantity INT,
    IN p_department VARCHAR(100),
    IN p_urgency ENUM('Low', 'Medium', 'High'),
    IN p_estimated_cost DECIMAL(12,2),
    OUT p_status VARCHAR(50)
)
BEGIN
    DECLARE v_max_sequence INT;
    
    -- Get the next sequence number
    SELECT COALESCE(MAX(item_sequence), 0) + 1 INTO v_max_sequence
    FROM asset_request_items
    WHERE asset_request_id = p_request_id;
    
    INSERT INTO asset_request_items (
        asset_request_id, item_sequence, asset_description, quantity,
        department, urgency, estimated_cost
    ) VALUES (
        p_request_id, v_max_sequence, p_asset_description, p_quantity,
        p_department, p_urgency, p_estimated_cost
    );
    
    -- Update total_items count in asset_requests
    UPDATE asset_requests
    SET total_items = (SELECT COUNT(*) FROM asset_request_items WHERE asset_request_id = p_request_id)
    WHERE id = p_request_id;
    
    SET p_status = 'SUCCESS';
END$$
DELIMITER ;

-- Procedure: Approve Asset Request
DROP PROCEDURE IF EXISTS sp_approve_asset_request;
DELIMITER $$
CREATE PROCEDURE sp_approve_asset_request(
    IN p_request_id INT,
    IN p_approved_by VARCHAR(100),
    OUT p_status VARCHAR(50)
)
BEGIN
    UPDATE asset_requests
    SET status = 'Approved',
        approval_date = NOW(),
        approved_by = p_approved_by,
        updated_at = NOW()
    WHERE id = p_request_id;
    
    -- Log the action
    INSERT INTO asset_request_audit_log (asset_request_id, action, action_by, new_value)
    VALUES (p_request_id, 'APPROVED', p_approved_by, 'Request approved');
    
    SET p_status = 'SUCCESS';
END$$
DELIMITER ;

-- Procedure: Reject Asset Request
DROP PROCEDURE IF EXISTS sp_reject_asset_request;
DELIMITER $$
CREATE PROCEDURE sp_reject_asset_request(
    IN p_request_id INT,
    IN p_rejected_by VARCHAR(100),
    IN p_rejection_reason TEXT,
    OUT p_status VARCHAR(50)
)
BEGIN
    UPDATE asset_requests
    SET status = 'Rejected',
        rejection_reason = p_rejection_reason,
        updated_at = NOW()
    WHERE id = p_request_id;
    
    -- Log the action
    INSERT INTO asset_request_audit_log (asset_request_id, action, action_by, new_value)
    VALUES (p_request_id, 'REJECTED', p_rejected_by, p_rejection_reason);
    
    SET p_status = 'SUCCESS';
END$$
DELIMITER ;

-- Procedure: Send Asset Request to Procurement
DROP PROCEDURE IF EXISTS sp_send_to_procurement;
DELIMITER $$
CREATE PROCEDURE sp_send_to_procurement(
    IN p_asset_request_id INT,
    OUT p_procurement_request_id INT,
    OUT p_status VARCHAR(50)
)
BEGIN
    DECLARE v_item_id INT;
    DECLARE v_asset_description VARCHAR(255);
    DECLARE v_quantity INT;
    DECLARE v_sku VARCHAR(50);
    DECLARE v_requester_id INT;
    DECLARE v_requester_name VARCHAR(100);
    DECLARE done INT DEFAULT FALSE;
    DECLARE cur CURSOR FOR 
        SELECT id, asset_description, quantity FROM asset_request_items 
        WHERE asset_request_id = p_asset_request_id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Get requester info
    SELECT requester_id, requester_name INTO v_requester_id, v_requester_name
    FROM asset_requests WHERE id = p_asset_request_id;
    
    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_item_id, v_asset_description, v_quantity;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Create procurement request for each item
        INSERT INTO procurement_requests (
            item_name, quantity, description, urgency, requester_id, requester_name, request_type
        ) VALUES (
            v_asset_description, v_quantity, CONCAT('From Asset Request: ', p_asset_request_id),
            'Medium', v_requester_id, v_requester_name, 'Manual'
        );
        
        SET p_procurement_request_id = LAST_INSERT_ID();
        
        -- Record the link
        INSERT INTO asset_request_to_procurement (
            asset_request_id, procurement_request_id, asset_item_id, sent_to_procurement_date
        ) VALUES (p_asset_request_id, p_procurement_request_id, v_item_id, NOW());
    END LOOP;
    
    CLOSE cur;
    
    -- Update asset request status
    UPDATE asset_requests
    SET status = 'In Process'
    WHERE id = p_asset_request_id;
    
    SET p_status = 'SUCCESS';
END$$
DELIMITER ;

-- ============================================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================================
-- Already added in table definitions, but here are additional useful indexes

ALTER TABLE asset_requests ADD INDEX idx_status_priority (status, priority) IF NOT EXISTS;
ALTER TABLE asset_request_items ADD INDEX idx_department (department) IF NOT EXISTS;
ALTER TABLE asset_request_to_procurement ADD INDEX idx_sent_date (sent_to_procurement_date) IF NOT EXISTS;

-- ============================================================================
-- END OF REQUEST ASSET MODULE SQL SCRIPT
-- ============================================================================
