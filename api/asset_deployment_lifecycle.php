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
                $where[] = '(assigned_to LIKE :q OR assigned_location LIKE :q)';
                $params[':q'] = "%$q%";
            }
            $sql = 'SELECT * FROM asset_deployment_lifecycle';
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY assignment_date DESC, id DESC';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($id && empty($rows)) { json_response(['status' => 'error', 'message' => 'Not found'], 404); }
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            $asset_id = (int)($input['asset_id'] ?? 0);
            $assigned_to = trim($input['assigned_to'] ?? '');
            $assigned_location = trim($input['assigned_location'] ?? '');
            $assignment_date = trim($input['assignment_date'] ?? '');
            $status = trim($input['status'] ?? 'In Use');
            $custodian_acknowledged = (int)($input['custodian_acknowledged'] ?? 0);
            if (!$asset_id || !$assignment_date) {
                json_response(['status' => 'error', 'message' => 'Required fields missing.'], 400);
            }
            // Validate asset_id exists in asset_onboarding_registration
            $checkStmt = $conn->prepare('SELECT id FROM asset_onboarding_registration WHERE id = ?');
            $checkStmt->execute([$asset_id]);
            if (!$checkStmt->fetch()) {
                json_response(['status' => 'error', 'message' => 'Asset ID does not exist. Please onboard the asset first in the Onboarding & Registration module.'], 404);
            }
            $stmt = $conn->prepare('INSERT INTO asset_deployment_lifecycle (asset_id, assigned_to, assigned_location, assignment_date, status, custodian_acknowledged) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$asset_id, $assigned_to, $assigned_location, $assignment_date, $status, $custodian_acknowledged]);
            $id = (int)$conn->lastInsertId();
            $stmt2 = $conn->prepare('SELECT * FROM asset_deployment_lifecycle WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'message' => 'Created.', 'data' => $row], 201);
        }
        case 'PUT': {
            $id = $input['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID required'], 400);
            $fields = [];
            $params = [];
            foreach(['asset_id','assigned_to','assigned_location','assignment_date','status','custodian_acknowledged'] as $f) {
                if (isset($input[$f])) { $fields[] = "$f = :$f"; $params[":$f"] = $input[$f]; }
            }
            if (empty($fields)) json_response(['status'=>'error','message'=>'No fields to update.'], 400);
            $params[':id'] = $id;
            $sql = 'UPDATE asset_deployment_lifecycle SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $stmt2 = $conn->prepare('SELECT * FROM asset_deployment_lifecycle WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','message'=>'Updated successfully.','data'=>$row]);
        }
        case 'DELETE': {
            $id = $_GET['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID required'], 400);
            $stmt = $conn->prepare('DELETE FROM asset_deployment_lifecycle WHERE id = ?');
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
