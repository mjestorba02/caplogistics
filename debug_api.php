<?php
session_start();
$_SESSION['id'] = 1;
$_SESSION['name'] = 'Test User';
$_SESSION['department'] = 'IT';

header('Content-Type: application/json');
require_once 'api/db.php';

$user_id = $_SESSION['id'];

// Test the exact query from the API
$sql = "SELECT * FROM asset_requests 
        WHERE requester_id = :user_id 
        ORDER BY request_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'session_user_id' => $user_id,
    'query' => $sql,
    'results_found' => count($requests),
    'requests' => $requests
], JSON_PRETTY_PRINT);
?>
