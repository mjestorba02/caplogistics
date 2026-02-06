<?php
require_once 'api/db.php';

echo "<!DOCTYPE html>
<html>
<head><title>Request Supplies Integration Test</title><style>
body { font-family: Arial; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background: #2563EB; color: white; }
tr:nth-child(even) { background: #f5f5f5; }
.section { margin: 30px 0; padding: 15px; background: #f0f0f0; border-left: 4px solid #2563EB; }
</style></head>
<body>
<h1>Request Asset → Request Supplies Integration Test</h1>";

// Check asset_requests
echo "<div class='section'>";
echo "<h2>Asset Requests (latest 3)</h2>";
$stmt = $conn->query("SELECT * FROM asset_requests ORDER BY created_at DESC LIMIT 3");
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($assets) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Request ID</th><th>Requester</th><th>Items</th><th>Priority</th><th>Status</th><th>Created</th></tr>";
    foreach ($assets as $req) {
        echo "<tr>";
        echo "<td>" . $req['id'] . "</td>";
        echo "<td>" . $req['request_id'] . "</td>";
        echo "<td>" . $req['requester_name'] . "</td>";
        echo "<td>" . $req['total_items'] . "</td>";
        echo "<td>" . $req['priority'] . "</td>";
        echo "<td>" . $req['status'] . "</td>";
        echo "<td>" . substr($req['created_at'], 0, 10) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No asset requests</p>";
}
echo "</div>";

// Check procurement_requests
echo "<div class='section'>";
echo "<h2>Procurement Requests (latest 3)</h2>";
$stmt = $conn->query("SELECT * FROM procurement_requests ORDER BY date_requested DESC LIMIT 3");
$procurements = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($procurements) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Item Name</th><th>Quantity</th><th>Requester</th><th>Urgency</th><th>Status</th><th>Date</th></tr>";
    foreach ($procurements as $proc) {
        echo "<tr>";
        echo "<td>" . $proc['id'] . "</td>";
        echo "<td>" . $proc['item_name'] . "</td>";
        echo "<td>" . $proc['quantity'] . "</td>";
        echo "<td>" . $proc['requester_name'] . "</td>";
        echo "<td>" . $proc['urgency'] . "</td>";
        echo "<td>" . $proc['status'] . "</td>";
        echo "<td>" . substr($proc['date_requested'], 0, 10) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No procurement requests</p>";
}
echo "</div>";

// Show correlation
echo "<div class='section'>";
echo "<h2>Cross-Reference Check</h2>";
echo "<p><strong>Asset Requests Count:</strong> " . count($assets) . "</p>";
echo "<p><strong>Procurement Requests Count:</strong> " . count($procurements) . "</p>";
if (count($assets) > 0 && count($procurements) > 0) {
    echo "<p style='color: green;'>✓ Both systems have data</p>";
} else {
    echo "<p style='color: red;'>✗ Data may not be flowing correctly</p>";
}
echo "</div>";

echo "</body></html>";
?>
