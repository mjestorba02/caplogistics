<?php
session_start();
$_SESSION['id'] = 19; // Force test user
$_SESSION['name'] = 'Mark John Estorba';
$_SESSION['department'] = 'IT';

require_once 'api/db.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Request Asset → Supply Debug</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #2563EB; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .new { background: #e0f2fe; border-left: 4px solid #0284c7; }
        h2 { color: #2563EB; margin-top: 0; }
        .count { font-size: 24px; font-weight: bold; color: #2563EB; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Request Asset → Supply Integration Debug</h1>";

// Count total records
$stmt = $conn->query("SELECT COUNT(*) as cnt FROM asset_requests");
$asset_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

$stmt = $conn->query("SELECT COUNT(*) as cnt FROM procurement_requests");
$supply_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

echo "<div class='section'>";
echo "<h2>📊 Database Statistics</h2>";
echo "<p>Asset Requests in DB: <span class='count'>" . $asset_count . "</span></p>";
echo "<p>Procurement Requests in DB: <span class='count'>" . $supply_count . "</span></p>";
echo "</div>";

// Latest asset requests
echo "<div class='section'>";
echo "<h2>📝 Latest Asset Requests (Last 5)</h2>";
$stmt = $conn->query("SELECT id, request_id, requester_name, total_items, priority, status, created_at FROM asset_requests ORDER BY created_at DESC LIMIT 5");
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($assets) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Request ID</th><th>Requester</th><th>Items</th><th>Priority</th><th>Status</th><th>Created</th></tr>";
    foreach ($assets as $asset) {
        echo "<tr>";
        echo "<td>" . $asset['id'] . "</td>";
        echo "<td><strong>" . $asset['request_id'] . "</strong></td>";
        echo "<td>" . $asset['requester_name'] . "</td>";
        echo "<td>" . $asset['total_items'] . "</td>";
        echo "<td>" . $asset['priority'] . "</td>";
        echo "<td>" . $asset['status'] . "</td>";
        echo "<td>" . substr($asset['created_at'], 0, 19) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// Latest procurement requests
echo "<div class='section'>";
echo "<h2>🛒 Latest Procurement Requests (Last 5)</h2>";
$stmt = $conn->query("SELECT id, item_name, quantity, urgency, requester_name, status, date_requested FROM procurement_requests ORDER BY date_requested DESC LIMIT 5");
$supplies = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($supplies) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Item Name</th><th>Qty</th><th>Urgency</th><th>Requester</th><th>Status</th><th>Date</th></tr>";
    foreach ($supplies as $supply) {
        echo "<tr>";
        echo "<td>" . $supply['id'] . "</td>";
        echo "<td>" . $supply['item_name'] . "</td>";
        echo "<td>" . $supply['quantity'] . "</td>";
        echo "<td>" . $supply['urgency'] . "</td>";
        echo "<td>" . $supply['requester_name'] . "</td>";
        echo "<td>" . $supply['status'] . "</td>";
        echo "<td>" . substr($supply['date_requested'], 0, 19) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</div>";

// Check correlation
echo "<div class='section'>";
echo "<h2>✅ Cross-Reference Check</h2>";

if ($asset_count > 0 && $supply_count > 0) {
    // Get latest asset request
    $stmt = $conn->query("SELECT * FROM asset_requests ORDER BY created_at DESC LIMIT 1");
    $latest_asset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Latest Asset Request:</strong> " . $latest_asset['request_id'] . " with " . $latest_asset['total_items'] . " item(s)</p>";
    
    // Check if there are procurement requests from this requester
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM procurement_requests WHERE requester_id = ?");
    $stmt->execute([$latest_asset['requester_id']]);
    $related_count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    echo "<p><strong>Procurement requests from this user:</strong> " . $related_count . "</p>";
    
    if ($related_count >= $latest_asset['total_items']) {
        echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS! All items have been forwarded to procurement</p>";
    } else {
        echo "<p style='color: orange; font-weight: bold;'>⚠ WARNING: Not all items made it to procurement</p>";
        echo "<p>Expected: " . $latest_asset['total_items'] . " items, Found: " . $related_count . " items</p>";
    }
}

echo "</div>";

// Instructions
echo "<div class='section'>";
echo "<h2>📌 How to Test</h2>";
echo "<ol>";
echo "<li>Go to <a href='pages/request_asset.php' target='_blank'>/pages/request_asset.php</a></li>";
echo "<li>Fill in the form with at least one item</li>";
echo "<li>Click 'Submit Request'</li>";
echo "<li>Refresh this page (F5) to see the latest data</li>";
echo "<li>Check that both tables have new entries</li>";
echo "</ol>";
echo "</div>";

// Browser console message
echo "<script>
console.log('Open your browser DevTools (F12) and go to Console tab');
console.log('When you submit a request, you should see logs like:');
console.log('  - \"Submitting X items to procurement\"');
console.log('  - \"Sending item 1: {...}\"');
console.log('  - \"Response status: 200\"');
console.log('  - \"Supply API response: {status: success}\"');
console.log('');
console.log('If you see errors there, it will tell you what went wrong');
</script>";

echo "</div></body></html>";
?>
