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
            $sql = "SELECT * FROM purchase_orders $where ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','pos'=>$rows]);
        case 'POST':
            // Auto-generate PO number
            $result = $conn->query("SELECT MAX(CAST(SUBSTRING(po_number, 4) AS UNSIGNED)) as max_num FROM purchase_orders WHERE po_number LIKE 'PO-%'");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $next_num = ($row['max_num'] ?? 0) + 1;
            $po_number = 'PO-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO purchase_orders (po_number, supplier, description, total_value, due_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$po_number, $input['supplier']??'', $input['description']??'', $input['total_value']??0, $input['due_date']??null, $input['status']??'Draft']);
            json_response(['status'=>'success','message'=>'PO created', 'po_number' => $po_number]);
        case 'PUT':
            $sql = "UPDATE purchase_orders SET po_number=?, supplier=?, description=?, total_value=?, due_date=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['po_number']??'', $input['supplier']??'', $input['description']??'', $input['total_value']??0, $input['due_date']??null, $input['status']??'Draft', $input['id']??0]);
            json_response(['status'=>'success','message'=>'PO updated']);
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $sql = "DELETE FROM purchase_orders WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'PO deleted']);
        default:
            json_response(['status'=>'error','message'=>'Method not allowed'],405);
    }
} catch (Throwable $e) {
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}
