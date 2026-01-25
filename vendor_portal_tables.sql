-- Vendor Portal Database Schema
-- Created for Supplier Registration, Validation, Verification and Requirements
-- Date: 2025-01-25

-- 1. Vendor Portal Registration Table
CREATE TABLE IF NOT EXISTS vendor_portal_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_name VARCHAR(150) NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255),
    city VARCHAR(100),
    state_province VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    tax_id VARCHAR(50),
    registration_number VARCHAR(100),
    business_type ENUM('Manufacturer', 'Distributor', 'Retailer', 'Service Provider', 'Wholesaler') NOT NULL,
    annual_revenue DECIMAL(15,2),
    employees_count INT,
    website_url VARCHAR(255),
    years_in_business INT,
    status ENUM('Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Inactive', 'Archived') DEFAULT 'Draft',
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    submitted_date DATETIME,
    reviewed_by VARCHAR(100),
    reviewed_date DATETIME,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_vendor_name (vendor_name),
    INDEX idx_registration_date (registration_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Vendor Validation Checklist
CREATE TABLE IF NOT EXISTS vendor_validation_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    business_license_verified TINYINT(1) DEFAULT 0,
    tax_compliance_verified TINYINT(1) DEFAULT 0,
    financial_statements_verified TINYINT(1) DEFAULT 0,
    references_checked TINYINT(1) DEFAULT 0,
    insurance_documents_verified TINYINT(1) DEFAULT 0,
    compliance_documents_verified TINYINT(1) DEFAULT 0,
    background_check_done TINYINT(1) DEFAULT 0,
    validation_status ENUM('Pending', 'In Progress', 'Approved', 'Failed', 'Incomplete') DEFAULT 'Pending',
    validation_date DATETIME,
    validated_by VARCHAR(100),
    validation_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendor_portal_registration(id) ON DELETE CASCADE,
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_validation_status (validation_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Vendor Verification Details
CREATE TABLE IF NOT EXISTS vendor_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    verification_type ENUM('Email', 'Phone', 'Address', 'Business', 'Financial', 'Compliance', 'References') NOT NULL,
    verification_status ENUM('Pending', 'In Progress', 'Verified', 'Failed', 'Expired') DEFAULT 'Pending',
    verification_code VARCHAR(100),
    verification_date DATETIME,
    verified_by VARCHAR(100),
    verification_notes TEXT,
    evidence_document VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendor_portal_registration(id) ON DELETE CASCADE,
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_verification_type (verification_type),
    INDEX idx_verification_status (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Vendor Requirements Registration
CREATE TABLE IF NOT EXISTS vendor_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    requirement_type ENUM('Certification', 'Insurance', 'Compliance', 'Quality Standard', 'Technical', 'Financial', 'Legal') NOT NULL,
    requirement_name VARCHAR(150) NOT NULL,
    requirement_description TEXT,
    is_mandatory TINYINT(1) DEFAULT 1,
    requirement_status ENUM('Not Started', 'In Progress', 'Submitted', 'Approved', 'Rejected', 'Expired') DEFAULT 'Not Started',
    document_url VARCHAR(255),
    submission_date DATETIME,
    approval_date DATETIME,
    expires_date DATE,
    approved_by VARCHAR(100),
    requirement_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendor_portal_registration(id) ON DELETE CASCADE,
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_requirement_type (requirement_type),
    INDEX idx_requirement_status (requirement_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Vendor Rating & Comments
CREATE TABLE IF NOT EXISTS vendor_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    rating_by VARCHAR(100),
    rating_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    overall_rating DECIMAL(3,2),
    quality_rating DECIMAL(3,2),
    reliability_rating DECIMAL(3,2),
    communication_rating DECIMAL(3,2),
    pricing_rating DECIMAL(3,2),
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendor_portal_registration(id) ON DELETE CASCADE,
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_rating_date (rating_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample Data
INSERT INTO vendor_portal_registration (vendor_name, company_name, contact_person, email, phone, address, city, state_province, country, postal_code, tax_id, registration_number, business_type, annual_revenue, employees_count, website_url, years_in_business, status, submitted_date)
VALUES
('John Manufacturing Ltd', 'John Manufacturing Ltd', 'John Doe', 'john.manufacturing@example.com', '+1-555-0001', '123 Business Ave', 'Manila', 'NCR', 'Philippines', '1000', 'TID-001', 'REG-001', 'Manufacturer', 5000000.00, 50, 'www.johnmfg.com', 10, 'Approved', NOW()),
('Global Supplies Co', 'Global Supplies Co', 'Maria Garcia', 'maria@globalsupplies.com', '+1-555-0002', '456 Commerce St', 'Cebu', 'Cebu', 'Philippines', '6000', 'TID-002', 'REG-002', 'Distributor', 2000000.00, 30, 'www.globalsupplies.com', 5, 'Approved', NOW()),
('Tech Solutions Inc', 'Tech Solutions Inc', 'Robert Chen', 'robert@techsolutions.com', '+1-555-0003', '789 Tech Park', 'Davao', 'Davao del Sur', 'Philippines', '8000', 'TID-003', 'REG-003', 'Service Provider', 3000000.00, 40, 'www.techsolutions.com', 7, 'Under Review', NOW());
