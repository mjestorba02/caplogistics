<?php
require_once 'api/db.php';

echo "<h2>User IDs in asset_requests</h2>";
$sql = "SELECT DISTINCT requester_id FROM asset_requests";
$stmt = $conn->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($users, JSON_PRETTY_PRINT) . "</pre>";

echo "<h2>All asset_requests records (with all fields)</h2>";
$sql = "SELECT * FROM asset_requests";
$stmt = $conn->query($sql);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($all, JSON_PRETTY_PRINT) . "</pre>";

echo "<h2>All asset_request_items records</h2>";
$sql = "SELECT * FROM asset_request_items";
$stmt = $conn->query($sql);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($items, JSON_PRETTY_PRINT) . "</pre>";
?>
