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
            // Get project statistics
            $statsQuery = $conn->query("
                SELECT
                    COUNT(*) as total_projects,
                    SUM(CASE WHEN status = 'Planning' THEN 1 ELSE 0 END) as planning,
                    SUM(CASE WHEN status = 'InProgress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM projects
            ");
            $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

            // Add total requests
            $requestsQuery = $conn->query("SELECT COUNT(*) as total_requests FROM project_requests");
            $stats['total_requests'] = $requestsQuery->fetch(PDO::FETCH_ASSOC)['total_requests'];

            // Get timeline data (recent projects and requests)
            $timelineQuery = $conn->query("
                (SELECT 'project' as type, project_name as title, description, created_at as date, status FROM projects ORDER BY created_at DESC LIMIT 10)
                UNION ALL
                (SELECT 'request' as type, request_type as title, description, request_date as date, status FROM project_requests ORDER BY request_date DESC LIMIT 10)
                ORDER BY date DESC LIMIT 20
            ");
            $timeline = $timelineQuery->fetchAll(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'stats' => $stats, 'timeline' => $timeline]);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>