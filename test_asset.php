<?php
include 'api/db.php';

echo "=== ASSET MANAGEMENT DIAGNOSTIC TEST ===\n\n";

if ($conn) {
    echo "✓ Database connected\n";
    try {
        // Check if table exists
        $result = $conn->query('SHOW TABLES LIKE "asset_management"');
        $table = $result->fetch();
        if ($table) {
            echo "✓ asset_management table exists\n";
            
            // Get table structure
            echo "\n--- Table Structure ---\n";
            $schema = $conn->query('DESCRIBE asset_management');
            $columns = $schema->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo "  {$col['Field']}: {$col['Type']}\n";
            }
            
            // Get row count
            $stmt = $conn->query('SELECT COUNT(*) as count FROM asset_management');
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "\n✓ Total assets in database: " . $row['count'] . "\n";
            
            // Get sample data
            if ($row['count'] > 0) {
                echo "\n--- Sample Data ---\n";
                $sample = $conn->query('SELECT id, item_number, item_name, status FROM asset_management LIMIT 3');
                $samples = $sample->fetchAll(PDO::FETCH_ASSOC);
                foreach ($samples as $s) {
                    echo "  ID: {$s['id']}, Item: {$s['item_number']}, Name: {$s['item_name']}, Status: {$s['status']}\n";
                }
            }
        } else {
            echo "✗ asset_management table NOT found\n";
            echo "Creating table...\n";
            $conn->exec("CREATE TABLE IF NOT EXISTS asset_management (
                id INT AUTO_INCREMENT PRIMARY KEY,
                item_number VARCHAR(255) NOT NULL UNIQUE,
                image VARCHAR(255) NULL,
                type_of_asset VARCHAR(100) NULL,
                item_code VARCHAR(100) NULL,
                item_name VARCHAR(255) NULL,
                status ENUM('Active', 'Inactive', 'Maintenance', 'Retired') DEFAULT 'Active',
                purchase_date DATE NULL,
                lifespan_years INT DEFAULT 5,
                last_maintenance_date DATETIME NULL,
                quality_multiplier FLOAT DEFAULT 1.0,
                date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_item_number (item_number),
                INDEX idx_status (status)
            )");
            echo "✓ Table created successfully\n";
        }
    } catch(Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Database connection failed\n";
}
?>
