-- Create supplier_identification table for Module 1
-- Database: logcap1

CREATE TABLE IF NOT EXISTS supplier_identification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) UNIQUE NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    certifications VARCHAR(500),
    risk_level ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    phone VARCHAR(20),
    notes TEXT,
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_risk_level (risk_level),
    INDEX idx_status (status),
    INDEX idx_supplier_name (supplier_name),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO supplier_identification (supplier_name, contact_email, certifications, risk_level, phone, notes) 
VALUES 
('ABC Manufacturing Corp', 'contact@abccorp.com', 'ISO 9001, ISO 14001', 'Low', '+1-555-0101', 'Reliable supplier'),
('XYZ Electronics Ltd', 'sales@xyzelectronics.com', 'ISO 9001, CE Mark, RoHS', 'Medium', '+1-555-0102', 'Good quality'),
('Global Supplies Inc', 'info@globalsupplies.com', 'ISO 9001', 'Medium', '+1-555-0103', 'Competitive pricing');
