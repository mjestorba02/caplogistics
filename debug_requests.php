<?php
session_start();

echo "<!DOCTYPE html>
<html>
<head><title>Debug Request Asset</title></head>
<body>
<h1>Request Asset Debug</h1>";

echo "<h2>Session Info</h2>";
echo "<pre>";
echo "User ID: " . ($_SESSION['id'] ?? 'NOT SET') . "\n";
echo "Name: " . ($_SESSION['name'] ?? 'NOT SET') . "\n";
echo "Department: " . ($_SESSION['department'] ?? 'NOT SET') . "\n";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Testing API Direct Call</h2>";
echo "<p>Testing: /api/asset_requests.php?action=my_requests</p>";

ob_start();
$_GET = ['action' => 'my_requests'];
$_SERVER['REQUEST_METHOD'] = 'GET';
include 'api/asset_requests.php';
$response = ob_get_clean();

echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h2>All Database Records</h2>";
require_once 'api/db.php';

$sql = "SELECT * FROM asset_requests";
$stmt = $conn->query($sql);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>Total records: " . count($all) . "</p>";
if ($all) {
    foreach ($all as $record) {
        echo "<pre>" . json_encode($record, JSON_PRETTY_PRINT) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>No records found in database</p>";
}

echo "</body></html>";
?>
