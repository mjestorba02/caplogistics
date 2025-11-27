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
            $sql = "SELECT * FROM payment_invoices $where ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','invoices'=>$rows]);
        case 'POST':
            // Auto-generate invoice number
            // Validate required fields
            $po_number = $input['po_number'] ?? '';
            $supplier = $input['supplier'] ?? '';
            
            if (empty($po_number)) {
                json_response(['status' => 'error', 'message' => 'PO number is required'], 400);
            }
            if (empty($supplier)) {
                json_response(['status' => 'error', 'message' => 'Supplier is required'], 400);
            }
            
            // Auto-generate invoice number
            $result = $conn->query("SELECT MAX(CAST(SUBSTRING(invoice_number, 5) AS UNSIGNED)) as max_num FROM payment_invoices WHERE invoice_number LIKE 'INV-%'");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $next_num = ($row['max_num'] ?? 0) + 1;
            $invoice_number = 'INV-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO payment_invoices (invoice_number, po_number, supplier, amount, due_date, status, compliance_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$invoice_number, $po_number, $supplier, $input['amount']??0, $input['due_date']??null, $input['status']??'Pending', $input['compliance_notes']??'']);
            json_response(['status'=>'success','message'=>'Invoice created successfully', 'invoice_number' => $invoice_number]);
        case 'PUT':
            $sql = "UPDATE payment_invoices SET invoice_number=?, po_number=?, supplier=?, amount=?, due_date=?, status=?, compliance_notes=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['invoice_number']??'', $input['po_number']??'', $input['supplier']??'', $input['amount']??0, $input['due_date']??null, $input['status']??'Pending', $input['compliance_notes']??'', $input['id']??0]);
            json_response(['status'=>'success','message'=>'Invoice updated']);
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $sql = "DELETE FROM payment_invoices WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'Invoice deleted']);
        default:
            json_response(['status'=>'error','message'=>'Method not allowed'],405);
    }
} catch (Throwable $e) {
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}
