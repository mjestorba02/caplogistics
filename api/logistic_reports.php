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
            // Get overall statistics
            $statsQuery = $conn->query("
                SELECT
                    (SELECT COUNT(*) FROM projects) as total_projects,
                    (SELECT COUNT(*) FROM projects WHERE status IN ('Planning', 'InProgress')) as active_projects,
                    (SELECT COUNT(*) FROM project_requests) as total_requests,
                    (SELECT COUNT(*) FROM contracts) as total_contracts,
                    (SELECT COUNT(*) FROM projects WHERE status = 'Planning') as project_planning,
                    (SELECT COUNT(*) FROM projects WHERE status = 'InProgress') as project_in_progress,
                    (SELECT COUNT(*) FROM projects WHERE status = 'Completed') as project_completed,
                    (SELECT COUNT(*) FROM projects WHERE status = 'Cancelled') as project_cancelled,
                    (SELECT COUNT(*) FROM project_requests WHERE status = 'Pending') as request_pending,
                    (SELECT COUNT(*) FROM project_requests WHERE status = 'Approved') as request_approved,
                    (SELECT COUNT(*) FROM project_requests WHERE status = 'Rejected') as request_rejected
            ");
            $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

            // Get recent activity
            $recentQuery = $conn->query("
                (SELECT 'project' as type, project_name as title, created_at as date, status FROM projects ORDER BY created_at DESC LIMIT 5)
                UNION ALL
                (SELECT 'request' as type, request_type as title, request_date as date, status FROM project_requests ORDER BY request_date DESC LIMIT 5)
                UNION ALL
                (SELECT 'contract' as type, contract_title as title, request_date as date, status FROM contracts ORDER BY request_date DESC LIMIT 5)
                ORDER BY date DESC LIMIT 15
            ");
            $recent = $recentQuery->fetchAll(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'stats' => $stats, 'recent' => $recent]);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>