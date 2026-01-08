-- Procurement Contracts Table
-- This SQL creates the procurement_contracts table for storing contract information

CREATE TABLE IF NOT EXISTS procurement_contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contract_title VARCHAR(255) NOT NULL,
    supplier_name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    contract_value DECIMAL(15,2) NULL,
    details TEXT NULL,
    status ENUM('Active', 'Expired', 'Terminated') DEFAULT 'Active',
    me),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
-- INSERT INTO procurement_contracts (contract_title, supplier_name, start_date, end_date, contract_value, details) VALUES
-- ('Office Supplies Contract', 'ABC Suppliers', '2024-01-01', '2024-12-31', 50000.00, 'Annual contract for office supplies'),
-- ('IT Equipment Contract', 'TechCorp', '2024-02-01', '2025-01-31', 150000.00, 'Hardware and software procurement');</content>
<parameter name="filePath">c:\xampp\htdocs\caplog1\sql\procurement_contracts.sql