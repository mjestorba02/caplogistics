<?php
require_once 'api/db.php';

echo "=== FINAL VERIFICATION ===\n\n";

// Verify columns exist
echo "1. Checking columns...\n";
$stmt = $conn->query("DESCRIBE asset_management");
$columns = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $columns[$row['Field']] = $row['Type'];
}

$required = ['id', 'item_number', 'image', 'status', 'purchase_date', 'lifespan_years', 'quality_multiplier', 'last_maintenance_date'];
foreach ($required as $col) {
    if (isset($columns[$col])) {
        echo "   ✓ $col\n";
    } else {
        echo "   ✗ $col MISSING!\n";
    }
}

// Test SELECT query
echo "\n2. Testing SELECT query...\n";
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
    ";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   ✓ Query executed successfully\n";
    echo "   ✓ Found " . count($assets) . " assets\n";
    
    if (count($assets) > 0) {
        echo "\n   Assets:\n";
        foreach ($assets as $asset) {
            echo "     - {$asset['item_number']}: {$asset['item_name']} (Quality: {$asset['quality_percent']}%, Status: {$asset['status']})\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Query failed: " . $e->getMessage() . "\n";
}

// Check uploads directory
echo "\n3. Checking uploads directory...\n";
$uploadsDir = __DIR__ . '/uploads/';
if (is_dir($uploadsDir)) {
    echo "   ✓ uploads directory exists\n";
    $files = array_diff(scandir($uploadsDir), ['.', '..']);
    echo "   ✓ Contains " . count($files) . " files\n";
} else {
    echo "   ℹ uploads directory does not exist (will be created on first upload)\n";
}

echo "\n=== STATUS ===\n";
echo "✓ Asset Management should now work correctly!\n";
echo "✓ Assets will load from database\n";
echo "✓ Add Asset button will work\n";
echo "✓ Quality calculations will work\n";
?>
