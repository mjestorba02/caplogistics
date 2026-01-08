CREATE TABLE IF NOT EXISTS outbound_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outbound_id INT NOT NULL,
    inventory_id INT NOT NULL,
    sku VARCHAR(255),
    quantity INT NOT NULL,
    department VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outbound_id) REFERENCES outbound_logistics(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES storage_inventory(id),
    INDEX idx_outbound_id (outbound_id),
    INDEX idx_inventory_id (inventory_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;