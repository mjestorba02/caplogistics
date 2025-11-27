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
                $where[] = '(asset_tag LIKE :q OR asset_name LIKE :q OR asset_type LIKE :q OR serial_number LIKE :q)';
                $params[':q'] = "%$q%";
            }
            $sql = 'SELECT * FROM asset_onboarding_registration';
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY registration_date DESC, id DESC';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($id && empty($rows)) { json_response(['status' => 'error', 'message' => 'Not found'], 404); }
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            $receiving_id = (int)($input['receiving_id'] ?? 0);
            $asset_tag = trim($input['asset_tag'] ?? '');
            $asset_name = trim($input['asset_name'] ?? '');
            $asset_type = trim($input['asset_type'] ?? '');
            $serial_number = trim($input['serial_number'] ?? '');
            $registration_date = trim($input['registration_date'] ?? '');
            $registered_by = trim($input['registered_by'] ?? '');
            $status = trim($input['status'] ?? 'In Inventory');
            if (!$receiving_id || !$asset_tag || !$asset_name || !$registration_date) {
                json_response(['status' => 'error', 'message' => 'Required fields missing.'], 400);
            }
            // Validate receiving_id exists in asset_receiving_logistics
            $checkStmt = $conn->prepare('SELECT id FROM asset_receiving_logistics WHERE id = ?');
            $checkStmt->execute([$receiving_id]);
            if (!$checkStmt->fetch()) {
                json_response(['status' => 'error', 'message' => 'Receiving Record ID does not exist. Please create a Receiving Record first.'], 404);
            }
            $stmt = $conn->prepare('INSERT INTO asset_onboarding_registration (receiving_id, asset_tag, asset_name, asset_type, serial_number, registration_date, registered_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$receiving_id, $asset_tag, $asset_name, $asset_type, $serial_number, $registration_date, $registered_by, $status]);
            $id = (int)$conn->lastInsertId();
            $stmt2 = $conn->prepare('SELECT * FROM asset_onboarding_registration WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'message' => 'Created.', 'data' => $row], 201);
        }
        case 'PUT': {
            $id = $input['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID required'], 400);
            $fields = [];
            $params = [];
            foreach(['receiving_id','asset_tag','asset_name','asset_type','serial_number','registration_date','registered_by','status'] as $f) {
                if (isset($input[$f])) { $fields[] = "$f = :$f"; $params[":$f"] = $input[$f]; }
            }
            if (empty($fields)) json_response(['status'=>'error','message'=>'No fields to update.'], 400);
            $params[':id'] = $id;
            $sql = 'UPDATE asset_onboarding_registration SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $stmt2 = $conn->prepare('SELECT * FROM asset_onboarding_registration WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','message'=>'Updated successfully.','data'=>$row]);
        }
        case 'DELETE': {
            $id = $_GET['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID required'], 400);
            $stmt = $conn->prepare('DELETE FROM asset_onboarding_registration WHERE id = ?');
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
