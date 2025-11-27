-- ═════════════════════════════════════════════════════════════════════════════════
-- SMART WAREHOUSING SYSTEM (SWS) - DATABASE SCHEMA
-- Database: logcap1
-- ═════════════════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 1: INBOUND LOGISTICS (Receiving & Putaway)
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS inbound_logistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id VARCHAR(50) UNIQUE NOT NULL,
    po_number VARCHAR(50),
    supplier_name VARCHAR(255) NOT NULL,
    shipment_date DATETIME,
    expected_arrival DATETIME,
    actual_arrival DATETIME,
    total_items INT,
    items_received INT DEFAULT 0,
    items_verified INT DEFAULT 0,
    quality_status ENUM('Pending', 'Good', 'Damaged', 'Partial') DEFAULT 'Pending',
    storage_location VARCHAR(100),
    assigned_dock INT,
    temperature_reading DECIMAL(5,2),
    humidity_reading DECIMAL(5,2),
    barcode_scan_count INT DEFAULT 0,
    handler_id INT,
    handler_name VARCHAR(255),
    handler_photo VARCHAR(500),
    status ENUM('Pending', 'In Progress', 'Received', 'Verified', 'Putaway Complete') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_po_number (po_number),
    INDEX idx_status (status),
    INDEX idx_actual_arrival (actual_arrival),
    INDEX idx_storage_location (storage_location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 2: STORAGE & INVENTORY MANAGEMENT
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS storage_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    bin_location VARCHAR(100) NOT NULL,
    warehouse_zone VARCHAR(50),
    current_stock INT DEFAULT 0,
    reserved_stock INT DEFAULT 0,
    available_stock INT DEFAULT 0,
    min_stock INT DEFAULT 10,
    max_stock INT DEFAULT 1000,
    reorder_point INT DEFAULT 20,
    unit_of_measure VARCHAR(20),
    weight_kg DECIMAL(10,2),
    dimensions_cm VARCHAR(100),
    temperature_requirement DECIMAL(5,2),
    humidity_requirement DECIMAL(5,2),
    movement_frequency ENUM('Fast', 'Medium', 'Slow') DEFAULT 'Medium',
    last_counted DATETIME,
    cycle_count_status ENUM('Due', 'In Progress', 'Complete') DEFAULT 'Due',
    stock_status ENUM('Optimal', 'Low', 'Overstock', 'Critical') DEFAULT 'Optimal',
    supplier_id INT,
    supplier_name VARCHAR(255),
    ai_forecast_30_days INT DEFAULT 0,
    ai_forecast_90_days INT DEFAULT 0,
    stockout_risk ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_sku (sku),
    INDEX idx_bin_location (bin_location),
    INDEX idx_warehouse_zone (warehouse_zone),
    INDEX idx_movement_frequency (movement_frequency),
    INDEX idx_stock_status (stock_status),
    INDEX idx_stockout_risk (stockout_risk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 3: OUTBOUND LOGISTICS (Dispatch & Shipping)
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS outbound_logistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_number VARCHAR(50) UNIQUE NOT NULL,
    order_id VARCHAR(50) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    customer_phone VARCHAR(20),
    pickup_address TEXT,
    delivery_address TEXT NOT NULL,
    total_items INT,
    items_packed INT DEFAULT 0,
    items_loaded INT DEFAULT 0,
    total_weight_kg DECIMAL(10,2),
    carrier_name VARCHAR(255),
    carrier_type ENUM('Truck', 'Van', 'Motorcycle', 'Drone') DEFAULT 'Truck',
    route_optimized_time INT,
    estimated_delivery DATETIME,
    actual_dispatch DATETIME,
    actual_delivery DATETIME,
    loading_dock INT,
    dispatch_confirmation VARCHAR(50),
    tracking_number VARCHAR(100),
    dispatch_staff_id INT,
    dispatch_staff_name VARCHAR(255),
    dispatch_photo VARCHAR(500),
    gps_last_location VARCHAR(255),
    gps_last_update DATETIME,
    delivery_status ENUM('Pending', 'Packed', 'Loaded', 'Dispatched', 'In Transit', 'Delivered', 'Failed') DEFAULT 'Pending',
    customer_tracking_enabled TINYINT DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_shipment_number (shipment_number),
    INDEX idx_order_id (order_id),
    INDEX idx_delivery_status (delivery_status),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_estimated_delivery (estimated_delivery),
    INDEX idx_carrier_name (carrier_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────────
-- MODULE 4: RETURNS MANAGEMENT (Reverse Logistics)
-- ─────────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS returns_management (
    id INT AUTO_INCREMENT PRIMARY KEY,
    return_id VARCHAR(50) UNIQUE NOT NULL,
    shipment_number VARCHAR(50),
    order_id VARCHAR(50),
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    reason_code VARCHAR(50),
    return_reason VARCHAR(500) NOT NULL,
    item_count INT,
    original_purchase_price DECIMAL(10,2),
    refund_amount DECIMAL(10,2),
    return_carrier VARCHAR(255),
    tracking_number_return VARCHAR(100),
    received_date DATETIME,
    inspection_status ENUM('Pending', 'In Progress', 'Complete') DEFAULT 'Pending',
    item_classification ENUM('Resellable', 'Refurbish', 'Recycle', 'Dispose') DEFAULT 'Resellable',
    condition_assessment TEXT,
    restocking_bin VARCHAR(100),
    disposal_method VARCHAR(100),
    inspector_id INT,
    inspector_name VARCHAR(255),
    inspector_notes TEXT,
    approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    approved_by INT,
    approved_by_name VARCHAR(255),
    return_status ENUM('Initiated', 'Received', 'Inspected', 'Restocked', 'Refunded', 'Disposed') DEFAULT 'Initiated',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_return_id (return_id),
    INDEX idx_shipment_number (shipment_number),
    INDEX idx_order_id (order_id),
    INDEX idx_return_status (return_status),
    INDEX idx_item_classification (item_classification),
    INDEX idx_inspection_status (inspection_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═════════════════════════════════════════════════════════════════════════════════
-- SAMPLE DATA
-- ═════════════════════════════════════════════════════════════════════════════════

-- Sample Inbound Logistics
INSERT INTO inbound_logistics (shipment_id, po_number, supplier_name, shipment_date, expected_arrival, actual_arrival, total_items, items_received, items_verified, quality_status, storage_location, assigned_dock, temperature_reading, humidity_reading, handler_id, handler_name, status, notes) 
VALUES 
('SHIP-001', 'PO-2024-001', 'ABC Manufacturing Corp', NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY), 500, 500, 450, 'Good', 'ZONE-A-BIN-01', 1, 22.5, 45.3, 1, 'John Doe', 'Verified', 'Shipment received in good condition'),
('SHIP-002', 'PO-2024-002', 'XYZ Electronics Ltd', NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), NULL, 300, 0, 0, 'Pending', NULL, 2, NULL, NULL, 2, 'Jane Smith', 'Pending', 'Awaiting arrival'),
('SHIP-003', 'PO-2024-003', 'Global Supplies Inc', DATE_SUB(NOW(), INTERVAL 1 DAY), NOW(), NOW(), 750, 750, 700, 'Good', 'ZONE-B-BIN-05', 3, 23.1, 48.2, 1, 'John Doe', 'Putaway Complete', 'All items stored successfully');

-- Sample Storage & Inventory
INSERT INTO storage_inventory (sku, product_name, category, bin_location, warehouse_zone, current_stock, reserved_stock, available_stock, min_stock, max_stock, reorder_point, unit_of_measure, weight_kg, dimensions_cm, movement_frequency, last_counted, cycle_count_status, stock_status, supplier_name, ai_forecast_30_days, ai_forecast_90_days, stockout_risk) 
VALUES 
('SKU-001', 'Electronic Component A', 'Electronics', 'ZONE-A-BIN-01', 'ZONE-A', 500, 100, 400, 50, 1000, 100, 'Pieces', 0.5, '10x10x10', 'Fast', NOW(), 'Complete', 'Optimal', 'ABC Manufacturing Corp', 350, 900, 'Low'),
('SKU-002', 'Mechanical Part B', 'Machinery', 'ZONE-A-BIN-02', 'ZONE-A', 250, 50, 200, 30, 500, 60, 'Pieces', 2.3, '15x20x25', 'Medium', NOW(), 'Complete', 'Optimal', 'XYZ Electronics Ltd', 180, 450, 'Low'),
('SKU-003', 'Plastic Housing C', 'Components', 'ZONE-B-BIN-05', 'ZONE-B', 120, 30, 90, 20, 300, 50, 'Pieces', 0.3, '8x8x8', 'Slow', DATE_SUB(NOW(), INTERVAL 3 DAY), 'Due', 'Low', 'Global Supplies Inc', 40, 100, 'Medium'),
('SKU-004', 'High Demand Item D', 'Electronics', 'ZONE-A-BIN-03', 'ZONE-A', 1500, 500, 1000, 200, 2000, 400, 'Units', 0.2, '5x5x5', 'Fast', NOW(), 'Complete', 'Optimal', 'ABC Manufacturing Corp', 1200, 3000, 'Low');

-- Sample Outbound Logistics
INSERT INTO outbound_logistics (shipment_number, order_id, customer_name, customer_email, customer_phone, delivery_address, total_items, items_packed, items_loaded, total_weight_kg, carrier_name, carrier_type, estimated_delivery, actual_dispatch, dispatch_staff_id, dispatch_staff_name, tracking_number, delivery_status, notes) 
VALUES 
('SHOUT-001', 'ORD-2024-001', 'Acme Corporation', 'contact@acmecorp.com', '555-0001', '123 Business St, City, Country', 50, 50, 50, 125.5, 'FastShip Logistics', 'Truck', DATE_ADD(NOW(), INTERVAL 5 DAY), NOW(), 1, 'Mike Johnson', 'FS-2024-001001', 'Dispatched', 'Priority delivery'),
('SHOUT-002', 'ORD-2024-002', 'TechCorp Inc', 'sales@techcorp.com', '555-0002', '456 Tech Avenue, City, Country', 30, 20, 0, 75.2, 'Express Cargo', 'Van', DATE_ADD(NOW(), INTERVAL 3 DAY), NULL, 2, 'Sarah Lee', 'EC-2024-002001', 'Packed', 'Standard delivery'),
('SHOUT-003', 'ORD-2024-003', 'Manufacturing Ltd', 'orders@mfgltd.com', '555-0003', '789 Industrial Blvd, City, Country', 100, 100, 100, 250.8, 'DroneDeliver AI', 'Drone', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 'Mike Johnson', 'DA-2024-003001', 'In Transit', 'Drone delivery');

-- Sample Returns Management
INSERT INTO returns_management (return_id, shipment_number, order_id, customer_name, customer_email, reason_code, return_reason, item_count, original_purchase_price, refund_amount, received_date, inspection_status, item_classification, return_status) 
VALUES 
('RET-001', 'SHOUT-001', 'ORD-2024-001', 'Acme Corporation', 'contact@acmecorp.com', 'DEFECT', 'Item arrived with defect', 5, 500.00, 500.00, DATE_SUB(NOW(), INTERVAL 2 DAY), 'Complete', 'Dispose', 'Disposed'),
('RET-002', 'SHOUT-002', 'ORD-2024-002', 'TechCorp Inc', 'sales@techcorp.com', 'CHANGE_MIND', 'Customer changed mind', 3, 300.00, 300.00, DATE_SUB(NOW(), INTERVAL 1 DAY), 'In Progress', 'Resellable', 'Inspected'),
('RET-003', 'SHOUT-003', 'ORD-2024-003', 'Manufacturing Ltd', 'orders@mfgltd.com', 'DAMAGE', 'Package damaged in transit', 10, 1000.00, 1000.00, NOW(), 'Pending', 'Refurbish', 'Received');

-- ═════════════════════════════════════════════════════════════════════════════════
-- END OF SCHEMA
-- ═════════════════════════════════════════════════════════════════════════════════
