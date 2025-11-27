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
                $where[] = '(po_number LIKE :q OR received_by LIKE :q OR supplier_name LIKE :q OR item_description LIKE :q)';
                $params[':q'] = "%$q%";
            }
            $sql = 'SELECT * FROM asset_receiving_logistics';
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY received_date DESC, id DESC';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($id && empty($rows)) { json_response(['status' => 'error', 'message' => 'Not found'], 404); }
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            $po_number = trim($input['po_number'] ?? '');
            $received_date = trim($input['received_date'] ?? '');
            $received_by = trim($input['received_by'] ?? '');
            $supplier_name = trim($input['supplier_name'] ?? '');
            $item_description = trim($input['item_description'] ?? '');
            $quantity_received = (int)($input['quantity_received'] ?? 0);
            $quantity_expected = (int)($input['quantity_expected'] ?? 0);
            $damage_notes = trim($input['damage_notes'] ?? '');
            $discrepancy_notes = trim($input['discrepancy_notes'] ?? '');
            $status = trim($input['status'] ?? 'Received');

            // Validation
            if (!$po_number) {
                json_response(['status' => 'error', 'message' => 'PO Number is required.'], 400);
            }
            if (!$received_date) {
                json_response(['status' => 'error', 'message' => 'Received Date is required.'], 400);
            }
            if (!$received_by) {
                json_response(['status' => 'error', 'message' => 'Received By is required.'], 400);
            }
            if ($quantity_received <= 0) {
                json_response(['status' => 'error', 'message' => 'Quantity Received must be greater than 0.'], 400);
            }
            if ($quantity_expected <= 0) {
                json_response(['status' => 'error', 'message' => 'Quantity Expected must be greater than 0.'], 400);
            }

            $stmt = $conn->prepare('INSERT INTO asset_receiving_logistics (po_number, received_date, received_by, supplier_name, item_description, quantity_received, quantity_expected, damage_notes, discrepancy_notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$po_number, $received_date, $received_by, $supplier_name, $item_description, $quantity_received, $quantity_expected, $damage_notes, $discrepancy_notes, $status]);
            $id = (int)$conn->lastInsertId();
            $stmt2 = $conn->prepare('SELECT * FROM asset_receiving_logistics WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'message' => 'Receiving record created successfully.', 'data' => $row], 201);
        }
        case 'PUT': {
            $id = $input['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID is required.'], 400);
            
            $fields = [];
            $params = [];
            $allowedFields = ['po_number','received_date','received_by','supplier_name','item_description','quantity_received','quantity_expected','damage_notes','discrepancy_notes','status'];
            
            foreach($allowedFields as $f) {
                if (isset($input[$f])) { 
                    $value = trim($input[$f] ?? '');
                    if ($f === 'quantity_received' || $f === 'quantity_expected') {
                        $value = (int)$value;
                    }
                    $fields[] = "$f = :$f"; 
                    $params[":$f"] = $value; 
                }
            }
            
            if (empty($fields)) json_response(['status'=>'error','message'=>'No fields to update.'], 400);
            
            $params[':id'] = $id;
            $sql = 'UPDATE asset_receiving_logistics SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            $stmt2 = $conn->prepare('SELECT * FROM asset_receiving_logistics WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) json_response(['status'=>'error','message'=>'Record not found.'], 404);
            json_response(['status'=>'success','message'=>'Updated successfully.','data'=>$row]);
        }
        case 'DELETE': {
            $id = $_GET['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID is required.'], 400);
            
            $stmt = $conn->prepare('SELECT id FROM asset_receiving_logistics WHERE id = ?');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                json_response(['status'=>'error','message'=>'Record not found.'], 404);
            }
            
            $stmt = $conn->prepare('DELETE FROM asset_receiving_logistics WHERE id = ?');
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'Deleted successfully.']);
        }
        default: {
            header('Allow: GET, POST, PUT, DELETE');
            json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
