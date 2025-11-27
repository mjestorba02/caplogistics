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
function json_response($data, int $code = 200) { http_response_code($code); echo json_encode($data); exit; }
try {
    switch ($method) {
        case 'GET': {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $q = isset($_GET['q']) ? trim($_GET['q']) : null;
            $where = [];
            $params = [];
            if ($id) { $where[] = 'id = :id'; $params[':id'] = $id; }
            if ($q) {
                $where[] = '(work_order_number LIKE :q OR maintenance_type LIKE :q OR technician LIKE :q)';
                $params[':q'] = "%$q%";
            }
            $sql = 'SELECT * FROM asset_maintenance_servicing';
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY scheduled_date DESC, id DESC';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($id && empty($rows)) { json_response(['status' => 'error', 'message' => 'Not found'], 404); }
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            $asset_id = (int)($input['asset_id'] ?? 0);
            $work_order_number = trim($input['work_order_number'] ?? '');
            $trigger_type = trim($input['trigger_type'] ?? 'Manual');
            $maintenance_type = trim($input['maintenance_type'] ?? '');
            $scheduled_date = trim($input['scheduled_date'] ?? '');
            $completed_date = trim($input['completed_date'] ?? '');
            $technician = trim($input['technician'] ?? '');
            $status = trim($input['status'] ?? 'Pending');
            $parts_used = trim($input['parts_used'] ?? '');
            $labor_hours = $input['labor_hours'] ?? 0;
            $notes = trim($input['notes'] ?? '');
            if (!$asset_id || !$work_order_number || !$scheduled_date) {
                json_response(['status' => 'error', 'message' => 'Required fields missing.'], 400);
            }
            // Validate asset_id exists in asset_onboarding_registration
            $checkStmt = $conn->prepare('SELECT id FROM asset_onboarding_registration WHERE id = ?');
            $checkStmt->execute([$asset_id]);
            if (!$checkStmt->fetch()) {
                json_response(['status' => 'error', 'message' => 'Asset ID does not exist. Please onboard the asset first in the Onboarding & Registration module.'], 404);
            }
            $stmt = $conn->prepare('INSERT INTO asset_maintenance_servicing (asset_id, work_order_number, trigger_type, maintenance_type, scheduled_date, completed_date, technician, status, parts_used, labor_hours, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$asset_id, $work_order_number, $trigger_type, $maintenance_type, $scheduled_date, $completed_date ?: null, $technician, $status, $parts_used, $labor_hours, $notes]);
            $id = (int)$conn->lastInsertId();
            $stmt2 = $conn->prepare('SELECT * FROM asset_maintenance_servicing WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'message' => 'Created.', 'data' => $row], 201);
        }
        case 'PUT': {
            $id = $input['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID required'], 400);
            $fields = [];
            $params = [];
            foreach(['asset_id','work_order_number','trigger_type','maintenance_type','scheduled_date','completed_date','technician','status','parts_used','labor_hours','notes'] as $f) {
                if (isset($input[$f])) { $fields[] = "$f = :$f"; $params[":$f"] = $input[$f]; }
            }
            if (empty($fields)) json_response(['status'=>'error','message'=>'No fields to update.'], 400);
            $params[':id'] = $id;
            $sql = 'UPDATE asset_maintenance_servicing SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $stmt2 = $conn->prepare('SELECT * FROM asset_maintenance_servicing WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','message'=>'Updated successfully.','data'=>$row]);
        }
        case 'DELETE': {
            $id = $_GET['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID required'], 400);
            $stmt = $conn->prepare('DELETE FROM asset_maintenance_servicing WHERE id = ?');
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'Deleted.']);
        }
        default: {
            header('Allow: GET, POST, PUT, DELETE');
            json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
