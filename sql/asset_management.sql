-- Asset Management Table
-- This SQL creates the asset_management table for storing asset information

CREATE TABLE IF NOT EXISTS asset_management (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_number VARCHAR(255) NOT NULL UNIQUE,
    qr_code VARCHAR(255) NULL,
    status ENUM('Release', 'InTransit', 'Pending') DEFAULT 'Pending',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_item_number (item_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
-- INSERT INTO asset_management (item_number, qr_code, status, description) VALUES
-- ('ITEM001', 'QR001', 'Release', 'Sample asset 1'),
-- ('ITEM002', 'QR002', 'InTransit', 'Sample asset 2');