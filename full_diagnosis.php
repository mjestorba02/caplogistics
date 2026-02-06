<?php
session_start();
require_once 'api/db.php';

// Force a test user session
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1;
    $_SESSION['name'] = 'Test User';
    $_SESSION['department'] = 'IT Department';
}

$current_user = $_SESSION['id'];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Full Diagnosis</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .section { margin: 30px 0; padding: 15px; background: #f5f5f5; border-left: 4px solid #2563EB; }
        pre { background: #eee; padding: 10px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #2563EB; color: white; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

<h1>🔍 Full Request Asset Diagnosis</h1>";

// 1. Session Info
echo "<div class='section'>";
echo "<h2>1. Session & User Info</h2>";
echo "<p><strong>Current User ID:</strong> " . htmlspecialchars($current_user) . "</p>";
echo "<p><strong>User Name:</strong> " . htmlspecialchars($_SESSION['name']) . "</p>";
echo "<p><strong>Department:</strong> " . htmlspecialchars($_SESSION['department']) . "</p>";
echo "</div>";

// 2. Database Connection
echo "<div class='section'>";
echo "<h2>2. Database Connection</h2>";
try {
    $test = $conn->query("SELECT 1");
    echo "<p class='success'>✓ Database connection OK</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// 3. Table Structure
echo "<div class='section'>";
echo "<h2>3. Table Structure Check</h2>";
$tables = ['asset_requests', 'asset_request_items'];
foreach ($tables as $table) {
    echo "<h4>$table</h4>";
    $stmt = $conn->query("DESCRIBE $table");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($cols as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// 4. Total Records
echo "<div class='section'>";
echo "<h2>4. Database Records Count</h2>";
$stmt = $conn->query("SELECT COUNT(*) as cnt FROM asset_requests");
$count = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Total asset_requests:</strong> " . $count['cnt'] . "</p>";

$stmt = $conn->query("SELECT COUNT(*) as cnt FROM asset_request_items");
$count = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Total asset_request_items:</strong> " . $count['cnt'] . "</p>";
echo "</div>";

// 5. All Requests
echo "<div class='section'>";
echo "<h2>5. ALL Requests in Database</h2>";
$stmt = $conn->query("SELECT * FROM asset_requests");
$all_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($all_requests) {
    echo "<table>";
    echo "<tr>";
    foreach (array_keys($all_requests[0]) as $col) {
        echo "<th>" . htmlspecialchars($col) . "</th>";
    }
    echo "</tr>";
    foreach ($all_requests as $req) {
        echo "<tr>";
        foreach ($req as $val) {
            echo "<td>" . htmlspecialchars($val) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No requests in database</p>";
}
echo "</div>";

// 6. API Test
echo "<div class='section'>";
echo "<h2>6. API Endpoint Test (my_requests)</h2>";
echo "<p>Testing: GET /api/asset_requests.php?action=my_requests</p>";
echo "<p>Expected User ID Filter: " . $current_user . "</p>";

$stmt = $conn->prepare("SELECT * FROM asset_requests WHERE requester_id = ?");
$stmt->execute([$current_user]);
$user_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p><strong>Requests for current user:</strong> " . count($user_requests) . "</p>";
if ($user_requests) {
    foreach ($user_requests as $req) {
        echo "<pre>" . json_encode($req, JSON_PRETTY_PRINT) . "</pre>";
    }
} else {
    echo "<p class='error'>No requests found for user ID " . $current_user . "</p>";
}
echo "</div>";

// 7. Items for those requests
echo "<div class='section'>";
echo "<h2>7. Items for User's Requests</h2>";
if ($user_requests) {
    foreach ($user_requests as $req) {
        echo "<h4>Request " . htmlspecialchars($req['request_id']) . " (ID: " . $req['id'] . ")</h4>";
        $stmt = $conn->prepare("SELECT * FROM asset_request_items WHERE asset_request_id = ?");
        $stmt->execute([$req['id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Items: " . count($items) . "</p>";
        if ($items) {
            foreach ($items as $item) {
                echo "<pre>" . json_encode($item, JSON_PRETTY_PRINT) . "</pre>";
            }
        }
    }
} else {
    echo "<p>No requests to check items for</p>";
}
echo "</div>";

// 8. Unique User IDs in DB
echo "<div class='section'>";
echo "<h2>8. All Unique User IDs in Database</h2>";
$stmt = $conn->query("SELECT DISTINCT requester_id FROM asset_requests");
$user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
if ($user_ids) {
    echo "<p>User IDs with requests: " . implode(", ", $user_ids) . "</p>";
} else {
    echo "<p>No user IDs found (no requests yet)</p>";
}
echo "</div>";

echo "</body></html>";
?>
