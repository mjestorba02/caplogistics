<?php
require_once 'api/db.php';

echo "=== ADDING MISSING COLUMNS ===\n\n";

try {
    // Add last_maintenance_date
    $conn->exec("ALTER TABLE asset_management ADD COLUMN last_maintenance_date DATETIME NULL");
    echo "✓ Added last_maintenance_date column\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "ℹ last_maintenance_date column already exists\n";
    } else {
        echo "✗ Error adding last_maintenance_date: " . $e->getMessage() . "\n";
    }
}

try {
    // Add quality_multiplier
    $conn->exec("ALTER TABLE asset_management ADD COLUMN quality_multiplier FLOAT DEFAULT 1.0");
    echo "✓ Added quality_multiplier column\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "ℹ quality_multiplier column already exists\n";
    } else {
        echo "✗ Error adding quality_multiplier: " . $e->getMessage() . "\n";
    }
}

echo "\n=== VERIFYING COLUMNS ===\n";
$stmt = $conn->query("DESCRIBE asset_management");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['Field']} ({$row['Type']})\n";
}

// Test the API query again
echo "\n=== TESTING API QUERY AGAIN ===\n";
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

$stmt = $conn->prepare($sql);
$stmt->execute();
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "✓ Query returned " . count($assets) . " assets\n";
if (count($assets) > 0) {
    echo "\nAssets:\n";
    foreach ($assets as $asset) {
        echo "  - {$asset['item_number']}: {$asset['item_name']} (Quality: {$asset['quality_percent']}%)\n";
    }
}
?>
