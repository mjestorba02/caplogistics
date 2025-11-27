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
            $sql = "SELECT * FROM supplier_evaluations $where ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','rfqs'=>$rows]);
        case 'POST':
            $sql = "INSERT INTO supplier_evaluations (item_description, quantity, budget, suppliers, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['item_description']??'', $input['quantity']??0, $input['budget']??0, $input['suppliers']??'', $input['status']??'Pending']);
            json_response(['status'=>'success','message'=>'RFQ created']);
        case 'PUT':
            $sql = "UPDATE supplier_evaluations SET item_description=?, quantity=?, budget=?, suppliers=?, status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['item_description']??'', $input['quantity']??0, $input['budget']??0, $input['suppliers']??'', $input['status']??'Pending', $input['id']??0]);
            json_response(['status'=>'success','message'=>'RFQ updated']);
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $sql = "DELETE FROM supplier_evaluations WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'RFQ deleted']);
        default:
            json_response(['status'=>'error','message'=>'Method not allowed'],405);
    }
} catch (Throwable $e) {
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}
