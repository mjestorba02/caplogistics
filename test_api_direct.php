<?php
session_start();
$_SESSION['id'] = 19;
$_SESSION['name'] = 'Mark John Estorba';

require_once 'api/db.php';

echo "<!DOCTYPE html>
<html>
<head><title>Request Supplies API Test</title>
<style>
body { font-family: Arial; margin: 20px; }
.section { background: #f5f5f5; padding: 15px; margin: 20px 0; border-left: 4px solid #2563EB; }
pre { background: #eee; padding: 10px; overflow-x: auto; }
.success { color: green; }
.error { color: red; }
</style>
</head>
<body>
<h1>🧪 Request Supplies API Direct Test</h1>";

// Test 1: Check if procurement_requests table exists
echo "<div class='section'>";
echo "<h2>1. Table Structure</h2>";
$stmt = $conn->query("DESCRIBE procurement_requests");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
foreach ($cols as $col) {
    echo "<tr>";
    echo "<td>" . $col['Field'] . "</td>";
    echo "<td>" . $col['Type'] . "</td>";
    echo "<td>" . $col['Null'] . "</td>";
    echo "<td>" . $col['Key'] . "</td>";
    echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// Test 2: Manually submit via API
echo "<div class='section'>";
echo "<h2>2. Manual API Test - POST Request</h2>";

$testPayload = [
    'item_name' => 'Test Laptop',
    'quantity' => 2,
    'description' => 'Testing API integration',
    'urgency' => 'High',
    'requester_id' => 19,
    'requester_name' => 'Mark John Estorba',
    'request_type' => 'Manual'
];

echo "<p>Sending payload:</p>";
echo "<pre>" . json_encode($testPayload, JSON_PRETTY_PRINT) . "</pre>";

// Simulate the API call
$method = 'POST';
$input = $testPayload;

$item_name = $input['item_name'] ?? '';
$sku = $input['sku'] ?? '';
$quantity = intval($input['quantity'] ?? 0);
$description = $input['description'] ?? '';
$urgency = $input['urgency'] ?? 'Medium';
$requester_id = $input['requester_id'] ?? '';
$requester_name = $input['requester_name'] ?? '';
$storage_item_id = intval($input['storage_item_id'] ?? 0);
$request_type = $input['request_type'] ?? 'Manual';

echo "<p>Parsed values:</p>";
echo "<ul>";
echo "<li>item_name: " . htmlspecialchars($item_name) . "</li>";
echo "<li>quantity: " . $quantity . "</li>";
echo "<li>description: " . htmlspecialchars($description) . "</li>";
echo "<li>urgency: " . htmlspecialchars($urgency) . "</li>";
echo "<li>requester_id: " . htmlspecialchars($requester_id) . "</li>";
echo "<li>requester_name: " . htmlspecialchars($requester_name) . "</li>";
echo "<li>request_type: " . htmlspecialchars($request_type) . "</li>";
echo "</ul>";

if (empty($item_name) || $quantity <= 0) {
    echo "<p class='error'>✗ Validation Error: item_name or quantity missing</p>";
} else {
    try {
        $sql = "INSERT INTO procurement_requests (item_name, sku, quantity, description, urgency, requester_id, requester_name, storage_item_id, request_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$item_name, $sku, $quantity, $description, $urgency, $requester_id, $requester_name, $storage_item_id > 0 ? $storage_item_id : null, $request_type]);
        
        if ($result) {
            echo "<p class='success'>✓ INSERT successful! Last insert ID: " . $conn->lastInsertId() . "</p>";
        } else {
            echo "<p class='error'>✗ INSERT failed</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "</div>";

// Test 3: Check what's in the table now
echo "<div class='section'>";
echo "<h2>3. Current Procurement Requests</h2>";
$stmt = $conn->query("SELECT * FROM procurement_requests ORDER BY id DESC LIMIT 5");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($records) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>";
    foreach (array_keys($records[0]) as $key) {
        echo "<th>" . htmlspecialchars($key) . "</th>";
    }
    echo "</tr>";
    foreach ($records as $record) {
        echo "<tr>";
        foreach ($record as $val) {
            echo "<td>" . htmlspecialchars($val) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records found</p>";
}
echo "</div>";

// Test 4: Check the actual request_supplies.php API code
echo "<div class='section'>";
echo "<h2>4. Check request_supplies.php API Code</h2>";
$api_content = file_get_contents('api/request_supplies.php');
$start = strpos($api_content, 'case \'POST\':');
$end = strpos($api_content, 'break;', $start) + 6;
$post_code = substr($api_content, $start, $end - $start);
echo "<pre>" . htmlspecialchars($post_code) . "</pre>";
echo "</div>";

echo "</body></html>";
?>
