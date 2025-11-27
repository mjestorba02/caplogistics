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
            $sql = "SELECT *, coordination_id AS coordination_code FROM procurement_supplier_coordination ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'records' => $records]);
            break;

        case 'POST':
            $coordination_id = trim($input['coordination_id'] ?? '');
            $project_id = $input['project_id'] ?? '';
            $supplier_name = $input['supplier_name'] ?? '';
            
            if (empty($supplier_name)) {
                json_response(['status' => 'error', 'message' => 'Supplier Name required'], 400);
            }

            // generate a coordination id if not provided
            if (empty($coordination_id)) {
                $coordination_id = 'COORD-' . date('Ymd') . '-' . random_int(100, 999);
            }

            $sql = "INSERT INTO procurement_supplier_coordination (coordination_id, project_id, supplier_name, po_number, delivery_date, status, created_at) VALUES (?, ?, ?, ?, ?, 'Assigned', NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$coordination_id, $project_id, $supplier_name, $input['po_number'] ?? null, $input['delivery_date'] ?? null]);
            json_response(['status' => 'success', 'message' => 'Coordination created successfully', 'coordination_id' => $coordination_id]);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            
            $sql = "UPDATE procurement_supplier_coordination SET project_id=?, supplier_name=?, po_number=?, delivery_date=?, status=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $input['project_id'] ?? '',
                $input['supplier_name'] ?? '',
                $input['po_number'] ?? null,
                $input['delivery_date'] ?? null,
                $input['status'] ?? 'Assigned',
                $id
            ]);
            json_response(['status' => 'success', 'message' => 'Updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            if (empty($id)) json_response(['status' => 'error', 'message' => 'ID required'], 400);
            $sql = "DELETE FROM procurement_supplier_coordination WHERE id = ?";
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
