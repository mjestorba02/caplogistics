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
            $sql = "SELECT *, shipment_id AS shipment_number, shipment_status AS status FROM shipment_scheduling_route_planning ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'records' => $records]);
            break;

        case 'POST':
            $shipment_id = trim($input['shipment_id'] ?? '');
            // some clients send shipment_number instead
            if (empty($shipment_id) && !empty($input['shipment_number'])) $shipment_id = trim($input['shipment_number']);
            $project_id = $input['project_id'] ?? '';
            $origin = $input['origin_location'] ?? '';
            $destination = $input['destination_location'] ?? '';
            
            if (empty($origin) || empty($destination)) {
                json_response(['status' => 'error', 'message' => 'Required fields missing'], 400);
            }

            // generate shipment id if not provided
            if (empty($shipment_id)) {
                $shipment_id = 'SHIP-' . date('Ymd') . '-' . random_int(100, 999);
            }

            $sql = "INSERT INTO shipment_scheduling_route_planning (shipment_id, project_id, origin_location, destination_location, transport_mode, carrier_name, total_cost, shipment_status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$shipment_id, $project_id, $origin, $destination, $input['transport_mode'] ?? 'Land', $input['carrier_name'] ?? null, $input['total_cost'] ?? 0]);
            json_response(['status' => 'success', 'message' => 'Shipment scheduled successfully', 'shipment_id' => $shipment_id]);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            
            $sql = "UPDATE shipment_scheduling_route_planning SET project_id=?, origin_location=?, destination_location=?, transport_mode=?, carrier_name=?, total_cost=?, shipment_status=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $input['project_id'] ?? '',
                $input['origin_location'] ?? '',
                $input['destination_location'] ?? '',
                $input['transport_mode'] ?? 'Land',
                $input['carrier_name'] ?? null,
                floatval($input['total_cost'] ?? 0),
                $input['shipment_status'] ?? 'Scheduled',
                $id
            ]);
            json_response(['status' => 'success', 'message' => 'Updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            $sql = "DELETE FROM shipment_scheduling_route_planning WHERE id = ?";
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
