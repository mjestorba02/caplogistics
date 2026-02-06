<?php
session_start();
$_SESSION['id'] = 1; // Test user ID
require_once 'api/db.php';

echo "<h2>Testing Asset Requests Data</h2>";

// Check if table exists and has data
$sql = "SELECT * FROM asset_requests";
$stmt = $conn->query($sql);
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>All Records in asset_requests:</h3>";
echo "<pre>" . json_encode($all_data, JSON_PRETTY_PRINT) . "</pre>";

// Check user_id = 1 specifically
$sql = "SELECT * FROM asset_requests WHERE requester_id = 1";
$stmt = $conn->query($sql);
$user_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Records for User ID 1:</h3>";
echo "<pre>" . json_encode($user_data, JSON_PRETTY_PRINT) . "</pre>";

// Check items
$sql = "SELECT * FROM asset_request_items";
$stmt = $conn->query($sql);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>All Items:</h3>";
echo "<pre>" . json_encode($items, JSON_PRETTY_PRINT) . "</pre>";

// Check table structure
echo "<h3>Table Structure - asset_requests:</h3>";
$sql = "DESCRIBE asset_requests";
$stmt = $conn->query($sql);
$structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . json_encode($structure, JSON_PRETTY_PRINT) . "</pre>";
?>
