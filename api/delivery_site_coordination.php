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
            // return site_location as site_address for compatibility with existing frontend
            $sql = "SELECT *, site_location AS site_address FROM delivery_site_coordination ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'records' => $records]);
            break;

        case 'POST':
            $delivery_id = $input['delivery_id'] ?? '';
            $project_id = $input['project_id'] ?? '';
            
            if (empty($delivery_id)) {
                json_response(['status' => 'error', 'message' => 'Delivery ID required'], 400);
            }

            // support both site_address (old) and site_location (schema)
            $site_location = $input['site_location'] ?? ($input['site_address'] ?? '');

            $sql = "INSERT INTO delivery_site_coordination (delivery_id, project_id, site_location, delivery_status, site_preparation, receiving_team_assigned, created_at) VALUES (?, ?, ?, 'Pending', ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$delivery_id, $project_id, $site_location, $input['site_preparation'] ?? 'Not Started', $input['receiving_team_assigned'] ?? 0]);
            json_response(['status' => 'success', 'message' => 'Delivery coordination record created successfully', 'delivery_id' => $delivery_id]);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            
            $site_location = $input['site_location'] ?? ($input['site_address'] ?? null);
            
            $sql = "UPDATE delivery_site_coordination SET project_id=?, site_location=?, delivery_status=?, site_preparation=?, receiving_team_assigned=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $input['project_id'] ?? '',
                $site_location,
                $input['delivery_status'] ?? 'Pending',
                $input['site_preparation'] ?? 'Not Started',
                $input['receiving_team_assigned'] ?? 0,
                $id
            ]);
            json_response(['status' => 'success', 'message' => 'Updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            $sql = "DELETE FROM delivery_site_coordination WHERE id = ?";
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
