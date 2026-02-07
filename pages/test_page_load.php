<?php
session_start();
$_SESSION['id'] = 1;
$_SESSION['name'] = 'Test User';

echo "<h1>Asset Page Test</h1>";
echo "<p>If this works, the page dependencies are correct.</p>";
echo "<iframe src='asset_management.php' style='width:100%; height:800px;'></iframe>";
?>
