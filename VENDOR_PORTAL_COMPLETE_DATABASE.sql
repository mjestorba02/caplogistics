-- ============================================================================
-- VENDOR PORTAL - COMPLETE DATABASE SCHEMA
-- ============================================================================
-- This file contains the complete database structure for the Vendor Portal system
-- Database Name: logcap1
-- Created: 2025-01-25
-- ============================================================================

-- Make sure you're using the correct database
USE logcap1;

-- ============================================================================
-- TABLE 1: VENDOR PORTAL REGISTRATION
-- ============================================================================
CREATE TABLE IF NOT EXISTS vendor_portal_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_name VARCHAR(150) NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    vendor_type ENUM('Supplier', 'Contractor', 'Service Provider', 'Distributor') NOT NULL DEFAULT 'Supplier',
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
    business_type ENUM('Manufacturer', 'Distributor', 'Retailer', 'Service Provider', 'Wholesaler') NOT NULL DEFAULT 'Manufacturer',
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
    INDEX idx_vendor_type (vendor_type),
    INDEX idx_registration_date (registration_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE 2: VENDOR VALIDATION CHECKLIST
-- ============================================================================
CREATE TABLE IF NOT EXISTS vendor_validation_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    business_license_verified TINYINT(1) DEFAULT 0,
    tax_compliance_verified TINYINT(1) DEFAULT 0,
    financial_statements_verified TINYINT(1) DEFAULT 0,
    financial_stability_verified TINYINT(1) DEFAULT 0,
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

-- ============================================================================
-- TABLE 3: VENDOR VERIFICATION DETAILS
-- ============================================================================
CREATE TABLE IF NOT EXISTS vendor_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    verification_type ENUM('Email', 'Phone', 'Address', 'Business', 'Financial', 'Compliance', 'References') NOT NULL,
    type VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pending',
    verification_status ENUM('Pending', 'In Progress', 'Verified', 'Failed', 'Expired') DEFAULT 'Pending',
    verification_code VARCHAR(100),
    date DATETIME,
    verification_date DATETIME,
    verified_by VARCHAR(100),
    notes TEXT,
    verification_notes TEXT,
    evidence_document VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendor_portal_registration(id) ON DELETE CASCADE,
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_verification_type (verification_type),
    INDEX idx_verification_status (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE 4: VENDOR REQUIREMENTS
-- ============================================================================
CREATE TABLE IF NOT EXISTS vendor_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    type VARCHAR(150),
    requirement_type ENUM('Certification', 'Insurance', 'Compliance', 'Quality Standard', 'Technical', 'Financial', 'Legal') NOT NULL,
    requirement_name VARCHAR(150) NOT NULL,
    requirement_description TEXT,
    description TEXT,
    is_mandatory TINYINT(1) DEFAULT 1,
    status VARCHAR(50) DEFAULT 'Not Started',
    requirement_status ENUM('Not Started', 'In Progress', 'Submitted', 'Approved', 'Rejected', 'Expired') DEFAULT 'Not Started',
    document_url VARCHAR(255),
    submission_date DATETIME,
    approval_date DATETIME,
    expires_date DATE,
    deadline DATE,
    approved_by VARCHAR(100),
    requirement_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendor_portal_registration(id) ON DELETE CASCADE,
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_requirement_type (requirement_type),
    INDEX idx_requirement_status (requirement_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- TABLE 5: VENDOR RATINGS
-- ============================================================================
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

-- ============================================================================
-- SAMPLE DATA (Optional - Comment out if you don't want sample data)
-- ============================================================================

-- Sample Vendors
INSERT IGNORE INTO vendor_portal_registration 
(vendor_name, company_name, vendor_type, contact_person, email, phone, address, city, state_province, country, postal_code, tax_id, registration_number, business_type, annual_revenue, employees_count, website_url, years_in_business, status, submitted_date)
VALUES
('John Manufacturing Ltd', 'John Manufacturing Ltd', 'Supplier', 'John Doe', 'john.manufacturing@example.com', '+1-555-0001', '123 Business Ave', 'Manila', 'NCR', 'Philippines', '1000', 'TID-001', 'REG-001', 'Manufacturer', 5000000.00, 50, 'www.johnmfg.com', 10, 'Approved', NOW()),
('Global Supplies Co', 'Global Supplies Co', 'Distributor', 'Maria Garcia', 'maria@globalsupplies.com', '+1-555-0002', '456 Commerce St', 'Cebu', 'Cebu', 'Philippines', '6000', 'TID-002', 'REG-002', 'Distributor', 2000000.00, 30, 'www.globalsupplies.com', 5, 'Approved', NOW()),
('Tech Solutions Inc', 'Tech Solutions Inc', 'Service Provider', 'Robert Chen', 'robert@techsolutions.com', '+1-555-0003', '789 Tech Park', 'Davao', 'Davao del Sur', 'Philippines', '8000', 'TID-003', 'REG-003', 'Service Provider', 3000000.00, 40, 'www.techsolutions.com', 7, 'Under Review', NOW());

-- Sample Validations
INSERT IGNORE INTO vendor_validation_checklist (vendor_id, business_license_verified, tax_compliance_verified, financial_stability_verified, validation_status, validated_by, validation_date)
VALUES
(1, 1, 1, 1, 'Approved', 'Admin', NOW()),
(2, 1, 1, 0, 'In Progress', 'Admin', NOW()),
(3, 0, 1, 1, 'In Progress', 'Admin', NOW());

-- Sample Verifications
INSERT IGNORE INTO vendor_verification (vendor_id, verification_type, type, verification_status, status, verified_by, verification_date)
VALUES
(1, 'Email', 'Email', 'Verified', 'Verified', 'Admin', NOW()),
(1, 'Business', 'Business', 'Verified', 'Verified', 'Admin', NOW()),
(2, 'Phone', 'Phone', 'Verified', 'Verified', 'Admin', NOW());

-- Sample Requirements
INSERT IGNORE INTO vendor_requirements (vendor_id, requirement_type, type, requirement_name, description, requirement_status, status, approved_by, approval_date)
VALUES
(1, 'Certification', 'ISO 9001', 'ISO 9001 Certification', 'Quality Management System Certification', 'Approved', 'Approved', 'Admin', NOW()),
(1, 'Insurance', 'General Liability', 'General Liability Insurance', 'Business Liability Coverage', 'Approved', 'Approved', 'Admin', NOW()),
(2, 'Compliance', 'Tax Compliance', 'Tax Compliance Certificate', 'BIR Tax Clearance', 'In Progress', 'In Progress', NULL, NULL),
(3, 'Financial', 'Audited Statements', 'Audited Financial Statements', 'Annual Financial Audit', 'Submitted', 'Submitted', NULL, NULL);

-- ============================================================================
-- END OF VENDOR PORTAL DATABASE SCHEMA
-- ============================================================================
