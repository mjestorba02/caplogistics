<?php
session_start();
$_SESSION['id'] = 1;
include 'api/db.php';

echo "=== ASSET DATA DEBUG ===\n\n";

// Check data in database
$stmt = $conn->query("SELECT * FROM asset_management");
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total assets in DB: " . count($assets) . "\n";
echo "Asset data:\n";
foreach ($assets as $asset) {
    echo "  - ID: {$asset['id']}, Item: {$asset['item_number']}, Name: {$asset['item_name']}, Status: {$asset['status']}\n";
}

// Now test the API
echo "\n=== API RESPONSE ===\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];

ob_start();
include 'api/asset_management.php';
$output = ob_get_clean();

// Extract JSON from output (in case there are notices)
$json_start = strpos($output, '{');
if ($json_start !== false) {
    $json = substr($output, $json_start);
    echo "JSON Response:\n";
    echo $json;
}
?>
