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
            $perf = isset($_GET['performance']) ? trim($_GET['performance']) : 'all';
            $where = $perf !== 'all' ? "WHERE performance_rating = '$perf'" : '';
            $sql = "SELECT * FROM supplier_relationships $where ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status'=>'success','suppliers'=>$rows]);
        case 'POST':
            $sql = "INSERT INTO supplier_relationships (supplier_name, contact_email, performance_rating, ontime_delivery, quality_score) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['supplier_name']??'', $input['contact_email']??'', $input['performance_rating']??'Good', $input['ontime_delivery']??0, $input['quality_score']??0]);
            json_response(['status'=>'success','message'=>'Supplier added']);
        case 'PUT':
            $sql = "UPDATE supplier_relationships SET supplier_name=?, contact_email=?, performance_rating=?, ontime_delivery=?, quality_score=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['supplier_name']??'', $input['contact_email']??'', $input['performance_rating']??'Good', $input['ontime_delivery']??0, $input['quality_score']??0, $input['id']??0]);
            json_response(['status'=>'success','message'=>'Supplier updated']);
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $sql = "DELETE FROM supplier_relationships WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'Supplier deleted']);
        default:
            json_response(['status'=>'error','message'=>'Method not allowed'],405);
    }
} catch (Throwable $e) {
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}
