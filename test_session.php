<?php
session_start();

// Show current session
echo "<h2>Current Session Info</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Test API Response</h2>";

// Make a direct call to the API to see what happens
if (isset($_SESSION['id'])) {
    // Include the API file and capture its output
    ob_start();
    $_GET['action'] = 'my_requests';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    include 'api/asset_requests.php';
    $output = ob_get_clean();
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
} else {
    echo "<p style='color: red;'>NOT LOGGED IN - Session ID not set</p>";
    echo "<p>You need to log in first at <a href='index.php'>index.php</a></p>";
}
?>
