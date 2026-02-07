<?php
require_once 'api/db.php';

echo "=== CHECKING ASSET MANAGEMENT TABLES ===\n\n";

// Check if asset_management exists
$result = $conn->query("SHOW TABLES LIKE 'asset_management'");
if ($result && $result->rowCount() > 0) {
    echo "✓ asset_management table EXISTS\n";
    $stmt = $conn->query("DESCRIBE asset_management");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "✗ asset_management table NOT found\n";
}

echo "\n";

// Check if asset_maintenance exists
$result = $conn->query("SHOW TABLES LIKE 'asset_maintenance'");
if ($result && $result->rowCount() > 0) {
    echo "✓ asset_maintenance table EXISTS\n";
    $stmt = $conn->query("DESCRIBE asset_maintenance");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "✗ asset_maintenance table NOT found\n";
}

echo "\n";

// Check sample data
$stmt = $conn->query("SELECT COUNT(*) as count FROM asset_management");
$count = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Assets in asset_management: " . $count['count'] . "\n";

// Check one asset with image
$stmt = $conn->query("SELECT id, item_number, image FROM asset_management LIMIT 1");
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "\nSample asset:\n";
    echo "  ID: " . $row['id'] . "\n";
    echo "  Item Number: " . $row['item_number'] . "\n";
    echo "  Image: " . ($row['image'] ?? 'NULL') . "\n";
}
?>
