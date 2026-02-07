<?php
session_start();
header('Content-Type: application/json');

// Simple direct test of the API
echo json_encode([
    'session_id' => $_SESSION['id'] ?? null,
    'is_session_set' => isset($_SESSION['id']),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'test' => 'API can return JSON'
], JSON_PRETTY_PRINT);
?>
