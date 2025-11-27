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
            $sql = "SELECT * FROM goods_receipts $where ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','receipts'=>$rows]);
        case 'POST':
            // Auto-generate receipt number
            // Validate required fields
            $po_number = $input['po_number'] ?? '';
            if (empty($po_number)) {
                json_response(['status' => 'error', 'message' => 'PO number is required'], 400);
            }
            
            // Auto-generate receipt number
            $result = $conn->query("SELECT MAX(CAST(SUBSTRING(receipt_number, 5) AS UNSIGNED)) as max_num FROM goods_receipts WHERE receipt_number LIKE 'RCP-%'");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $next_num = ($row['max_num'] ?? 0) + 1;
            $receipt_number = 'RCP-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO goods_receipts (`receipt_number`, `po_number`, `quantity_received`, `quantity_inspected`, `item_condition`, `status`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$receipt_number, $po_number, $input['quantity_received']??0, $input['quantity_inspected']??0, $input['item_condition']??'Good', $input['status']??'Pending']);
            json_response(['status'=>'success','message'=>'Receipt created successfully', 'receipt_number' => $receipt_number]);
        case 'PUT':
            $sql = "UPDATE goods_receipts SET `receipt_number` = ?, `po_number` = ?, `quantity_received` = ?, `quantity_inspected` = ?, `item_condition` = ?, `status` = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['receipt_number']??'', $input['po_number']??'', $input['quantity_received']??0, $input['quantity_inspected']??0, $input['item_condition']??'Good', $input['status']??'Received', $input['id']??0]);
            json_response(['status'=>'success','message'=>'Receipt updated']);
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $sql = "DELETE FROM goods_receipts WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'Receipt deleted']);
        default:
            json_response(['status'=>'error','message'=>'Method not allowed'],405);
    }
} catch (Throwable $e) {
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}
