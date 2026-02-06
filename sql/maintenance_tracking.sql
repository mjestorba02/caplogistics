-- Maintenance Tracking for Asset Management
-- Run this to add maintenance support

CREATE TABLE IF NOT EXISTS asset_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    item_number VARCHAR(100) NOT NULL,
    maintenance_type ENUM('Preventive', 'Corrective', 'Emergency') DEFAULT 'Preventive',
    description TEXT NOT NULL,
    scheduled_date DATE,
    completed_date DATE,
    technician_name VARCHAR(255),
    status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    cost DECIMAL(12, 2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_asset_id (asset_id),
    INDEX idx_item_number (item_number),
    INDEX idx_status (status),
    INDEX idx_scheduled_date (scheduled_date),
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add maintenance status to assets if not already present
-- ALTER TABLE assets ADD COLUMN maintenance_status ENUM('Active', 'In Maintenance', 'Out of Service') DEFAULT 'Active';
