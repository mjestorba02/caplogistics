<?php
require_once 'api/db.php';

echo "=== ASSET MANAGEMENT DEBUG ===\n\n";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'asset_management'");
if ($result && $result->rowCount() > 0) {
    echo "✓ asset_management table EXISTS\n";
} else {
    echo "✗ asset_management table NOT found\n";
}

// Check columns
echo "\nTable Columns:\n";
$stmt = $conn->query("DESCRIBE asset_management");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['Field']} ({$row['Type']})\n";
}

// Count assets
echo "\n";
$stmt = $conn->query("SELECT COUNT(*) as count FROM asset_management");
$count = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total Assets: " . $count['count'] . "\n";

// Show all assets
echo "\n=== ALL ASSETS ===\n";
$stmt = $conn->query("SELECT id, item_number, item_name, status, purchase_date FROM asset_management");
if ($stmt && $stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Number: {$row['item_number']} | Name: {$row['item_name']} | Status: {$row['status']}\n";
    }
} else {
    echo "No assets found\n";
}

// Test the API query
echo "\n=== TESTING API QUERY ===\n";
$searchItem = '';
$searchStatus = '';

$conditions = [];
$params = [];

if ($searchItem !== '') {
    $conditions[] = "item_number LIKE ?";
    $params[] = "%$searchItem%";
}

if ($searchStatus !== '') {
    $conditions[] = "status = ?";
    $params[] = $searchStatus;
}

$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

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
    $where
    ORDER BY date DESC
    ";

echo "SQL Query:\n$sql\n\n";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Query Result: " . count($assets) . " assets returned\n";
if (count($assets) > 0) {
    echo "\nFirst Asset:\n";
    print_r($assets[0]);
}

// Check uploads directory
echo "\n=== CHECKING UPLOADS DIRECTORY ===\n";
$uploadsDir = __DIR__ . '/uploads/';
if (is_dir($uploadsDir)) {
    $files = scandir($uploadsDir);
    $imageFiles = array_filter($files, function($f) { 
        return !in_array($f, ['.', '..']) && is_file($uploadsDir . $f);
    });
    echo "✓ uploads directory exists\n";
    echo "Images: " . count($imageFiles) . "\n";
    foreach ($imageFiles as $f) {
        echo "  - $f\n";
    }
} else {
    echo "✗ uploads directory NOT found\n";
}
?>
