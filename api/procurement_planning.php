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
function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
try {
    switch ($method) {
        case 'GET':
            $status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
            $where = $status !== 'all' ? "WHERE status = '$status'" : '';
            $sql = "SELECT * FROM procurement_requisitions $where ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','requisitions'=>$rows]);
        case 'POST':
            // Auto-generate requisition number
            $result = $conn->query("SELECT MAX(CAST(SUBSTRING(requisition_number, 5) AS UNSIGNED)) as max_num FROM procurement_requisitions WHERE requisition_number LIKE 'REQ-%'");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $next_num = ($row['max_num'] ?? 0) + 1;
            $requisition_number = 'REQ-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO procurement_requisitions (requisition_number, department, description, total_amount, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$requisition_number, $input['department']??'', $input['description']??'', $input['total_amount']??0, $input['status']??'Draft']);
            json_response(['status'=>'success','message'=>'Requisition created', 'requisition_number' => $requisition_number]);
        case 'PUT':
            $sql = "UPDATE procurement_requisitions SET requisition_number=?, department=?, description=?, total_amount=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['requisition_number']??'', $input['department']??'', $input['description']??'', $input['total_amount']??0, $input['status']??'Draft', $input['id']??0]);
            json_response(['status'=>'success','message'=>'Requisition updated']);
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $sql = "DELETE FROM procurement_requisitions WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'Requisition deleted']);
        default:
            json_response(['status'=>'error','message'=>'Method not allowed'],405);
    }
} catch (Throwable $e) {
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}
