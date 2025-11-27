-- Supplier Identification & Pre-Qualification Table
CREATE TABLE IF NOT EXISTS supplier_identification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(150) NOT NULL UNIQUE,
    contact_email VARCHAR(150) NOT NULL,
    certifications VARCHAR(500),
    risk_level ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    phone VARCHAR(20),
    notes TEXT,
    status ENUM('Active', 'Inactive', 'Archived') NOT NULL DEFAULT 'Active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (risk_level),
    INDEX (status),
    INDEX (supplier_name),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
