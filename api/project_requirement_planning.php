<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $sql = "SELECT * FROM project_requirement_planning ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($search) {
                $projects = array_filter($projects, function($p) use ($search) {
                    return stripos($p['project_id'] ?? '', $search) !== false || 
                           stripos($p['project_name'] ?? '', $search) !== false;
                });
            }
            json_response(['status' => 'success', 'projects' => array_values($projects)]);
            break;

        case 'POST':
            $project_id = $input['project_id'] ?? '';
            $project_name = $input['project_name'] ?? '';
            $start_date = $input['start_date'] ?? '';
            $end_date = $input['end_date'] ?? '';
            $total_budget = floatval($input['total_budget'] ?? 0);
            $logistics_scope = $input['logistics_scope'] ?? 'Multi-Phase';
            $project_manager_name = $input['project_manager_name'] ?? '';
            $project_status = $input['project_status'] ?? 'Planning';

            // generate a fallback project_id if not provided
            if (empty($project_id)) {
                $base = preg_replace('/[^A-Za-z0-9]/', '', strtoupper(substr($project_name, 0, 8)));
                $project_id = ($base ? $base : 'PRJ') . '-' . substr((string)microtime(true), -6);
            }

            if (empty($project_name)) {
                json_response(['status' => 'error', 'message' => 'Project Name is required'], 400);
            }

            try {
                $sql = "INSERT INTO project_requirement_planning (project_id, project_name, start_date, end_date, total_budget, logistics_scope, project_manager_name, project_status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$project_id, $project_name, $start_date ?: null, $end_date ?: null, $total_budget ?: 0, $logistics_scope, $project_manager_name, $project_status]);
                json_response(['status' => 'success', 'message' => 'Project created successfully', 'project_id' => $project_id]);
            } catch (PDOException $e) {
                // return friendly message for duplicate key or other DB errors
                $msg = $e->getMessage();
                if (strpos($msg, 'Duplicate') !== false || strpos($msg, 'duplicate') !== false) {
                    json_response(['status' => 'error', 'message' => 'Project ID already exists. Try changing the project name.'], 400);
                }
                json_response(['status' => 'error', 'message' => $msg], 500);
            }
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'Project ID required'], 400);
            
            $sql = "UPDATE project_requirement_planning SET project_name=?, start_date=?, end_date=?, total_budget=?, logistics_scope=?, project_status=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $input['project_name'] ?? '',
                $input['start_date'] ?? null,
                $input['end_date'] ?? null,
                floatval($input['total_budget'] ?? 0),
                $input['logistics_scope'] ?? 'Multi-Phase',
                $input['project_status'] ?? 'Planning',
                $id
            ]);
            json_response(['status' => 'success', 'message' => 'Project updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'Project ID required'], 400);
            
            $sql = "DELETE FROM project_requirement_planning WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            json_response(['status' => 'success', 'message' => 'Project deleted successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
