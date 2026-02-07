<?php
// $host = "localhost";
// $db_name = "logcap1";
// $username = "root"; // change if needed
// $password = "";     // change if needed

$host = "localhost";
$db_name = "log1_logisticss1_ecommerce";
$username = "log1_logistics1_admin"; // change if needed
$password = "ZVz@o@KMRZ^0NFve";     // change if needed

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Instead of dying, set conn to null and handle in APIs
    $conn = null;
}
?>