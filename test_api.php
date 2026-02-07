<?php
session_start();
// Simulate a logged-in user
$_SESSION['id'] = 1;

// Now simulate a GET request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];

ob_start();
include 'api/asset_management.php';
$output = ob_get_clean();

echo "=== API RESPONSE TEST ===\n\n";
echo $output;
?>
