<?php
// api/documents_verification.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$userId = (int)$_SESSION['id'];
$method = $_SERVER['REQUEST_METHOD'];

function json($d,$c=200){ http_response_code($c); echo json_encode($d); exit; }

try {
    if ($method === 'GET') {
        $sql = "SELECT d.*, (SELECT file_name FROM document_files df WHERE df.document_id=d.id LIMIT 1) AS file_name FROM documents d WHERE d.status IN ('Pending Verification','Created') ORDER BY d.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json(['status'=>'success','documents'=>$rows]);
    }

    if ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $input);
        $id = (int)($input['id'] ?? 0);
        $action = $input['action'] ?? '';
        $remarks = $input['remarks'] ?? null;
        if (!$id || !in_array($action,['verify','reject'])) json(['status'=>'error','message'=>'Invalid'],400);

        if ($action === 'verify') {
            $stmt = $conn->prepare("UPDATE documents SET status='Verified' WHERE id=?");
            $stmt->execute([$id]);
            $auditAction = 'verify';
        } else {
            $stmt = $conn->prepare("UPDATE documents SET status='Rejected' WHERE id=?");
            $stmt->execute([$id]);
            $auditAction = 'reject';
        }
        $stmt = $conn->prepare("INSERT INTO document_verification (document_id, verifier_id, verification_status, remarks, verified_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$id, $userId, $action === 'verify' ? 'Verified' : 'Rejected', $remarks]);
        $stmt = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $userId, $auditAction, $remarks, $_SERVER['REMOTE_ADDR'] ?? null]);
        json(['status'=>'success','message'=>'OK']);
    }

    json(['status'=>'error','message'=>'Method not allowed'],405);
} catch (Throwable $e) { json(['status'=>'error','message'=>$e->getMessage()],500); }
