<?php
// logout.php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: http://localhost/caplog1/pages/vendor/login.php');
exit();
?>