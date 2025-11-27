<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit; }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }

function json_response($data, int $code = 200) { http_response_code($code); echo json_encode($data); exit; }

try {
    switch ($method) {
        case 'GET':
            // provide compatibility aliases expected by the frontend
            $sql = "SELECT *, performance_status AS monitoring_status, kpi_milestone_adherence_percent AS on_time_delivery_rate, kpi_cost_variance_percent AS cost_performance_index, notes AS remarks FROM project_performance_monitoring_closure ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'records' => $records]);
            break;

        case 'POST':
            $performance_id = trim($input['performance_id'] ?? '');
            $project_id = $input['project_id'] ?? '';

            if (empty($performance_id)) {
                $performance_id = 'PERF-' . date('Ymd') . '-' . random_int(100, 999);
            }

            // map frontend fields to DB columns
            $performance_status = $input['monitoring_status'] ?? ($input['performance_status'] ?? 'On Track');
            $kpi_milestone_adherence_percent = $input['on_time_delivery_rate'] ?? null;
            $kpi_cost_variance_percent = $input['cost_performance_index'] ?? null;
            $notes = $input['remarks'] ?? $input['notes'] ?? null;

            $sql = "INSERT INTO project_performance_monitoring_closure (performance_id, project_id, performance_status, kpi_milestone_adherence_percent, kpi_cost_variance_percent, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$performance_id, $project_id, $performance_status, $kpi_milestone_adherence_percent, $kpi_cost_variance_percent, $notes]);
            json_response(['status' => 'success', 'message' => 'Performance record created successfully', 'performance_id' => $performance_id]);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            
            $sql = "UPDATE project_performance_monitoring_closure SET project_id=?, performance_status=?, kpi_milestone_adherence_percent=?, kpi_cost_variance_percent=?, notes=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $input['project_id'] ?? '',
                $input['monitoring_status'] ?? ($input['performance_status'] ?? 'On Track'),
                intval($input['on_time_delivery_rate'] ?? 0),
                floatval($input['cost_performance_index'] ?? 0),
                $input['remarks'] ?? ($input['notes'] ?? null),
                $id
            ]);
            json_response(['status' => 'success', 'message' => 'Updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            $sql = "DELETE FROM project_performance_monitoring_closure WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            json_response(['status' => 'success', 'message' => 'Deleted successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
