<?php
// Quick script to dump vendor id, name, email, address as JSON
require_once __DIR__ . '/../api/db.php';

try {
    $stmt = $conn->prepare('SELECT id, first_name, last_name, email, address FROM user_vendor LIMIT 100');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'count' => count($rows), 'data' => $rows], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>