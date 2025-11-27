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
            $sql = "SELECT * FROM customs_regulatory_compliance ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'records' => $records]);
            break;

        case 'POST':
            $compliance_id = $input['compliance_id'] ?? '';
            $shipment_id = $input['shipment_id'] ?? '';
            $project_id = $input['project_id'] ?? '';
            
            if (empty($compliance_id)) {
                json_response(['status' => 'error', 'message' => 'Compliance ID required'], 400);
            }

            $sql = "INSERT INTO customs_regulatory_compliance (compliance_id, shipment_id, project_id, declaration_status, created_at) VALUES (?, ?, ?, 'Draft', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$compliance_id, $shipment_id, $project_id]);
            json_response(['status' => 'success', 'message' => 'Compliance record created successfully']);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            
            $sql = "UPDATE customs_regulatory_compliance SET shipment_id=?, project_id=?, declaration_status=?, customs_clearance_status=?, permits_obtained=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $input['shipment_id'] ?? '',
                $input['project_id'] ?? '',
                $input['declaration_status'] ?? 'Draft',
                $input['customs_clearance_status'] ?? 'Pending',
                $input['permits_obtained'] ?? 0,
                $id
            ]);
            json_response(['status' => 'success', 'message' => 'Updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            $sql = "DELETE FROM customs_regulatory_compliance WHERE id = ?";
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
