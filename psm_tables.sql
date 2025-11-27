-- Procurement & Sourcing Management (PSM) Tables

-- 1. Supplier Evaluation & Selection Table
CREATE TABLE IF NOT EXISTS supplier_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_description VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    budget DECIMAL(10, 2) NOT NULL DEFAULT 0,
    suppliers TEXT NOT NULL,
    status ENUM('Pending', 'Submitted', 'Selected') NOT NULL DEFAULT 'Pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Procurement Planning & Requisition Table
CREATE TABLE IF NOT EXISTS procurement_requisitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_number VARCHAR(50) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    status ENUM('Draft', 'Submitted', 'Approved', 'Rejected') NOT NULL DEFAULT 'Draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (department),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Purchase Order (PO) Management Table
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    supplier VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    total_value DECIMAL(12, 2) NOT NULL DEFAULT 0,
    due_date DATE,
    status ENUM('Draft', 'Sent', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (supplier),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Receiving & Quality Assurance Table
CREATE TABLE IF NOT EXISTS goods_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(50) NOT NULL UNIQUE,
    po_number VARCHAR(50) NOT NULL,
    quantity_received INT NOT NULL DEFAULT 0,
    quantity_inspected INT NOT NULL DEFAULT 0,
    condition ENUM('Good', 'Damaged', 'Defective') NOT NULL DEFAULT 'Good',
    status ENUM('Received', 'Inspecting', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Received',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (po_number),
    INDEX (condition),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Supplier Relationship Management Table
CREATE TABLE IF NOT EXISTS supplier_relationships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100),
    performance_rating ENUM('Excellent', 'Good', 'Fair', 'Poor') NOT NULL DEFAULT 'Good',
    ontime_delivery DECIMAL(5, 2) NOT NULL DEFAULT 0,
    quality_score DECIMAL(5, 2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (performance_rating),
    INDEX (supplier_name),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Payment & Compliance Table
CREATE TABLE IF NOT EXISTS payment_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    po_number VARCHAR(50) NOT NULL,
    supplier VARCHAR(100) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    due_date DATE,
    status ENUM('Pending', 'Approved', 'Paid', 'Rejected') NOT NULL DEFAULT 'Pending',
    compliance_notes TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (po_number),
    INDEX (supplier),
    INDEX (created_at),
    INDEX (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data (optional)
INSERT INTO supplier_evaluations (item_description, quantity, budget, suppliers, status) VALUES
('Office Supplies', 100, 5000, 'Supplier A, Supplier B', 'Pending'),
('Raw Materials', 500, 25000, 'Supplier C, Supplier D', 'Submitted');

INSERT INTO procurement_requisitions (requisition_number, department, description, total_amount, status) VALUES
('REQ-001', 'IT', 'Computer Equipment', 15000, 'Draft'),
('REQ-002', 'HR', 'Training Materials', 5000, 'Approved');

INSERT INTO purchase_orders (po_number, supplier, description, total_value, due_date, status) VALUES
('PO-001', 'Supplier A', 'Office Supplies', 5000, '2025-12-15', 'Sent'),
('PO-002', 'Supplier C', 'Raw Materials', 25000, '2025-12-20', 'Confirmed');

INSERT INTO goods_receipts (receipt_number, po_number, quantity_received, quantity_inspected, condition, status) VALUES
('RCP-001', 'PO-001', 100, 100, 'Good', 'Accepted'),
('RCP-002', 'PO-002', 500, 480, 'Damaged', 'Received');

INSERT INTO supplier_relationships (supplier_name, contact_email, performance_rating, ontime_delivery, quality_score) VALUES
('Supplier A', 'contact@suppliera.com', 'Excellent', 98.5, 96.2),
('Supplier C', 'contact@supplierc.com', 'Good', 85.0, 88.5);

INSERT INTO payment_invoices (invoice_number, po_number, supplier, amount, due_date, status, compliance_notes) VALUES
('INV-001', 'PO-001', 'Supplier A', 5000, '2025-12-25', 'Pending', 'Awaiting receipt verification'),
('INV-002', 'PO-002', 'Supplier C', 25000, '2026-01-05', 'Approved', 'Approved for payment');
