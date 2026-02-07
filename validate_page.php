<?php
session_start();
$_SESSION['id'] = 1;
$_SESSION['name'] = 'Test User';

// Render the page
ob_start();
include 'pages/asset_management.php';
$html = ob_get_clean();

// Check if it contains key elements
$checks = [
    'Asset Management' => strpos($html, 'Asset Management') !== false,
    'Add Asset button' => strpos($html, 'Add Asset') !== false,
    'assetsTable' => strpos($html, 'id="assetsTable"') !== false,
    'fetchAssets() call' => strpos($html, 'fetchAssets()') !== false,
    'Script tag' => strpos($html, '<script>') !== false,
];

echo "=== ASSET MANAGEMENT PAGE VALIDATION ===\n\n";
foreach ($checks as $name => $result) {
    echo ($result ? '✓' : '✗') . " $name\n";
}

// Count lines of HTML
$lineCount = substr_count($html, "\n");
echo "\nTotal HTML lines: $lineCount\n";
echo "HTML file size: " . strlen($html) . " bytes\n";

// Check for common errors
if (strpos($html, 'Parse error') !== false) {
    echo "\n⚠️ Parse error detected in HTML\n";
}

if (substr_count($html, '<script>') !== substr_count($html, '</script>')) {
    echo "\n⚠️ Script tag mismatch\n";
}

echo "\n✅ Page validation complete\n";
?>
