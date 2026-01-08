<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!$conn) {
    json_response(['status' => 'error', 'message' => 'Database connection failed'], 500);
}

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            // Simple reports: counts by status
            $requestStats = $conn->query("SELECT status, COUNT(*) as count FROM document_requests GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
            $uploadStats = $conn->query("SELECT status, COUNT(*) as count FROM document_uploads GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'request_stats' => $requestStats, 'upload_stats' => $uploadStats]);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>