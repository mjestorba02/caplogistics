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
            $sql = "SELECT *, CONCAT(current_latitude, ',', current_longitude) AS gps_coordinates FROM execution_realtime_tracking ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'records' => $records]);
            break;

        case 'POST':
            $tracking_id = trim($input['tracking_id'] ?? '');
            $shipment_id = trim($input['shipment_id'] ?? '');
            $project_id = $input['project_id'] ?? '';
            
            if (empty($shipment_id)) {
                json_response(['status' => 'error', 'message' => 'Shipment ID required'], 400);
            }

            if (empty($tracking_id)) {
                $tracking_id = 'TRACK-' . date('Ymd') . '-' . random_int(100, 999);
            }

            // map gps coordinates to current_location if provided
            $current_location = $input['current_location'] ?? ($input['gps_coordinates'] ?? null);

            $sql = "INSERT INTO execution_realtime_tracking (tracking_id, shipment_id, project_id, current_location, current_latitude, current_longitude, tracking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'In Transit', NOW())";
            $stmt = $conn->prepare($sql);
            $lat = $input['current_latitude'] ?? null;
            $lon = $input['current_longitude'] ?? null;
            // if gps_coordinates provided as 'lat,lon' try to split
            if (empty($lat) && !empty($input['gps_coordinates'])) {
                $parts = explode(',', $input['gps_coordinates']);
                if (count($parts) >= 2) { $lat = trim($parts[0]); $lon = trim($parts[1]); }
            }
            $stmt->execute([$tracking_id, $shipment_id, $project_id, $current_location, $lat, $lon]);
            json_response(['status' => 'success', 'message' => 'Tracking created successfully', 'tracking_id' => $tracking_id]);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            
            $sql = "UPDATE execution_realtime_tracking SET shipment_id=?, project_id=?, current_location=?, current_latitude=?, current_longitude=?, speed_kmh=?, temperature_reading=?, vehicle_condition=?, tracking_status=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $lat = $input['current_latitude'] ?? null;
            $lon = $input['current_longitude'] ?? null;
            if (empty($lat) && !empty($input['gps_coordinates'])) {
                $parts = explode(',', $input['gps_coordinates']);
                if (count($parts) >= 2) { $lat = trim($parts[0]); $lon = trim($parts[1]); }
            }
            $stmt->execute([
                $input['shipment_id'] ?? '',
                $input['project_id'] ?? '',
                $input['current_location'] ?? null,
                $lat,
                $lon,
                floatval($input['speed_kmh'] ?? 0),
                floatval($input['temperature_reading'] ?? 0),
                $input['vehicle_condition'] ?? 'Good',
                $input['tracking_status'] ?? 'In Transit',
                $id
            ]);
            json_response(['status' => 'success', 'message' => 'Updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            $sql = "DELETE FROM execution_realtime_tracking WHERE id = ?";
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
