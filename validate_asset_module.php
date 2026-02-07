<?php
/**
 * Asset Management Module - Comprehensive Validation Report
 */
session_start();
$_SESSION['id'] = 1;

echo "=== ASSET MANAGEMENT VALIDATION REPORT ===\n\n";

// 1. Check database connection
require_once __DIR__ . '/api/db.php';

echo "1. DATABASE CONNECTION\n";
echo "   Status: " . ($conn ? "✓ CONNECTED\n" : "✗ FAILED\n");

if ($conn) {
    // 2. Check asset_management table
    echo "\n2. TABLE STRUCTURE\n";
    try {
        $result = $conn->query('SHOW TABLES LIKE "asset_management"');
        $table = $result->fetch();
        if ($table) {
            echo "   Status: ✓ TABLE EXISTS\n";
            
            // Get columns
            $schema = $conn->query('DESCRIBE asset_management');
            $columns = $schema->fetchAll(PDO::FETCH_COLUMN);
            echo "   Columns: " . count($columns) . "\n";
            foreach ($columns as $col) {
                echo "     - $col\n";
            }
        } else {
            echo "   Status: ✗ TABLE NOT FOUND\n";
        }
    } catch (Exception $e) {
        echo "   Error: " . $e->getMessage() . "\n";
    }
    
    // 3. Check data
    echo "\n3. DATA VALIDATION\n";
    try {
        $result = $conn->query('SELECT COUNT(*) as count FROM asset_management');
        $row = $result->fetch(PDO::FETCH_ASSOC);
        echo "   Total records: " . $row['count'] . "\n";
        
        if ($row['count'] > 0) {
            // Test the exact query that the API uses
            $sql = "
                SELECT *,
                    CASE 
                        WHEN purchase_date IS NULL THEN ROUND(100 * COALESCE(quality_multiplier, 1.0))
                        ELSE GREATEST(
                            0,
                            ROUND(
                                (100 - (
                                    (TIMESTAMPDIFF(DAY, purchase_date, CURDATE()) 
                                    / (lifespan_years * 365)) * 100
                                )) * COALESCE(quality_multiplier, 1.0)
                            )
                        )
                    END AS quality_percent
                FROM asset_management
                ORDER BY date DESC
                LIMIT 1
                ";
            $result = $conn->query($sql);
            $sample = $result->fetch(PDO::FETCH_ASSOC);
            echo "   Sample record: " . json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        }
    } catch (Exception $e) {
        echo "   Error: " . $e->getMessage() . "\n";
    }
}

// 4. Check API endpoint
echo "\n4. API ENDPOINT TEST\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];
ob_start();
try {
    include 'api/asset_management.php';
} catch (Throwable $e) {
    echo "API Error: " . $e->getMessage();
}
$apiOutput = ob_get_clean();
$apiData = json_decode($apiOutput, true);
echo "   Status: " . ($apiData['status'] ?? 'UNKNOWN') . "\n";
echo "   Assets returned: " . count($apiData['assets'] ?? []) . "\n";
if (isset($apiData['assets']) && count($apiData['assets']) > 0) {
    echo "   First asset: " . $apiData['assets'][0]['item_number'] . "\n";
}

// 5. Check PHP files syntax
echo "\n5. PHP SYNTAX CHECK\n";
exec('php -l ' . __DIR__ . '/pages/asset_management.php 2>&1', $output1);
echo "   pages/asset_management.php: " . trim($output1[0]) . "\n";

exec('php -l ' . __DIR__ . '/api/asset_management.php 2>&1', $output2);
echo "   api/asset_management.php: " . trim($output2[0]) . "\n";

echo "\n=== VALIDATION COMPLETE ===\n";
?>
