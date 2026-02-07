<?php
// Simulate the API request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];
$_SESSION['id'] = 19; // Simulate logged in user

require_once 'api/asset_management.php';
?>
