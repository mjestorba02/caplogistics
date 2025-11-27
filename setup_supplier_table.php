<?php
/**
 * Setup script for Supplier Identification table
 * This file creates the supplier_identification table if it doesn't exist
 * Run this once after deployment or call it from your setup/installation page
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/api/db.php';

try {
    // Check if table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'supplier_identification'");
    
    if ($checkTable->rowCount() > 0) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Table supplier_identification already exists']);
        exit;
    }

    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS supplier_identification (
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
        INDEX idx_risk_level (risk_level),
        INDEX idx_status (status),
        INDEX idx_supplier_name (supplier_name),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $conn->exec($sql);

    // Insert sample data
    $sampleData = "INSERT INTO supplier_identification (supplier_name, contact_email, certifications, risk_level, phone, notes, status, created_at) VALUES
    ('ABC Manufacturing Corp', 'contact@abccorp.com', 'ISO 9001, ISO 14001', 'Low', '+1-555-0101', 'Reliable supplier, on-time delivery', 'Active', NOW()),
    ('XYZ Electronics Ltd', 'sales@xyzelectronics.com', 'ISO 9001, CE Mark, RoHS', 'Medium', '+1-555-0102', 'Good quality, occasional delays', 'Active', NOW()),
    ('Global Supplies Inc', 'info@globalsupplies.com', 'ISO 9001', 'Medium', '+1-555-0103', 'Competitive pricing', 'Active', NOW())";

    $conn->exec($sampleData);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Table created successfully with sample data']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
