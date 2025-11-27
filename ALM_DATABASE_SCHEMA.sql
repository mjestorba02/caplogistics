-- Asset Lifecycle & Maintenance (ALM) Database Schema
-- Created: 2025-11-26

-- 1. Receiving & Logistics Intake
CREATE TABLE IF NOT EXISTS asset_receiving_logistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) NOT NULL,
    received_date DATE NOT NULL,
    received_by VARCHAR(100) NOT NULL,
    supplier_name VARCHAR(100),
    item_description VARCHAR(255),
    quantity_received INT NOT NULL,
    quantity_expected INT NOT NULL,
    damage_notes TEXT,
    discrepancy_notes TEXT,
    status ENUM('Received','Pending','Discrepancy','Damaged') DEFAULT 'Received',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Onboarding & Registration
CREATE TABLE IF NOT EXISTS asset_onboarding_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receiving_id INT NOT NULL,
    asset_tag VARCHAR(50) NOT NULL UNIQUE,
    asset_name VARCHAR(100) NOT NULL,
    asset_type VARCHAR(50),
    serial_number VARCHAR(100),
    registration_date DATE NOT NULL,
    registered_by VARCHAR(100),
    status ENUM('In Inventory','Ready for Deployment','Registered') DEFAULT 'In Inventory',
    FOREIGN KEY (receiving_id) REFERENCES asset_receiving_logistics(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Deployment & Operational Life
CREATE TABLE IF NOT EXISTS asset_deployment_lifecycle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    assigned_to VARCHAR(100),
    assigned_location VARCHAR(100),
    assignment_date DATE,
    status ENUM('In Use','Transferred','Returned','Lost') DEFAULT 'In Use',
    custodian_acknowledged TINYINT(1) DEFAULT 0,
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES asset_onboarding_registration(id) ON DELETE CASCADE
);

-- 4. Maintenance & Servicing
CREATE TABLE IF NOT EXISTS asset_maintenance_servicing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    work_order_number VARCHAR(50) NOT NULL,
    trigger_type ENUM('Scheduled','Manual') DEFAULT 'Manual',
    maintenance_type VARCHAR(50),
    scheduled_date DATE,
    completed_date DATE,
    technician VARCHAR(100),
    status ENUM('Under Maintenance','Completed','Pending') DEFAULT 'Pending',
    parts_used TEXT,
    labor_hours DECIMAL(5,2),
    notes TEXT,
    FOREIGN KEY (asset_id) REFERENCES asset_onboarding_registration(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. End-of-Life & Disposal
CREATE TABLE IF NOT EXISTS asset_end_of_life_disposal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    disposal_request_date DATE NOT NULL,
    approved_by VARCHAR(100),
    approval_date DATE,
    disposal_method ENUM('Sold','Recycled','Scrapped') DEFAULT 'Scrapped',
    disposal_date DATE,
    proceeds DECIMAL(12,2),
    financial_closure_notes TEXT,
    archived TINYINT(1) DEFAULT 0,
    FOREIGN KEY (asset_id) REFERENCES asset_onboarding_registration(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Data
INSERT INTO asset_receiving_logistics (po_number, received_date, received_by, supplier_name, item_description, quantity_received, quantity_expected, damage_notes, discrepancy_notes, status)
VALUES
('PO-20251101', '2025-11-01', 'Juan Dela Cruz', 'ABC Supplies', 'Laptop Dell XPS 13', 10, 10, '', '', 'Received'),
('PO-20251102', '2025-11-02', 'Maria Santos', 'XYZ Tech', 'Projector Epson', 2, 2, '', '', 'Received'),
('PO-20251103', '2025-11-03', 'Pedro Reyes', 'LMN Office', 'Office Chair', 20, 20, '', '', 'Received');

INSERT INTO asset_onboarding_registration (receiving_id, asset_tag, asset_name, asset_type, serial_number, registration_date, registered_by, status)
VALUES
(1, 'TAG-0001', 'Laptop Dell XPS 13', 'Laptop', 'SN123456', '2025-11-01', 'Juan Dela Cruz', 'In Inventory'),
(2, 'TAG-0002', 'Projector Epson', 'Projector', 'SN654321', '2025-11-02', 'Maria Santos', 'In Inventory'),
(3, 'TAG-0003', 'Office Chair', 'Furniture', 'SN789012', '2025-11-03', 'Pedro Reyes', 'In Inventory');

INSERT INTO asset_deployment_lifecycle (asset_id, assigned_to, assigned_location, assignment_date, status, custodian_acknowledged)
VALUES
(1, 'Ana Lim', 'IT Office', '2025-11-05', 'In Use', 1),
(2, 'Ben Cruz', 'Conference Room', '2025-11-06', 'In Use', 1);

INSERT INTO asset_maintenance_servicing (asset_id, work_order_number, trigger_type, maintenance_type, scheduled_date, completed_date, technician, status, parts_used, labor_hours, notes)
VALUES
(1, 'WO-20251110', 'Scheduled', 'Battery Replacement', '2025-11-10', '2025-11-11', 'Tech1', 'Completed', 'Battery', 1.5, 'Routine maintenance'),
(2, 'WO-20251112', 'Manual', 'Lens Cleaning', '2025-11-12', NULL, 'Tech2', 'Under Maintenance', '', 0.5, 'Dust removal');

INSERT INTO asset_end_of_life_disposal (asset_id, disposal_request_date, approved_by, approval_date, disposal_method, disposal_date, proceeds, financial_closure_notes, archived)
VALUES
(3, '2025-11-20', 'Manager1', '2025-11-21', 'Scrapped', '2025-11-22', 0.00, 'Broken beyond repair', 1);
