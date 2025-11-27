<?php
// api/documents_integration.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$method = $_SERVER['REQUEST_METHOD'];
function json($d,$c=200){ http_response_code($c); echo json_encode($d); exit; }

try {
    if ($method === 'POST') {
        // link document to module: document_id, linked_module, linked_id
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $id = (int)($input['document_id'] ?? 0);
        if (!$id) json(['status'=>'error','message'=>'Missing document_id'],400);
        $lm = $input['linked_module'] ?? null;
        $lid = isset($input['linked_id']) ? (int)$input['linked_id'] : null;
        $stmt = $conn->prepare("UPDATE documents SET linked_module = ?, linked_id = ? WHERE id = ?");
        $stmt->execute([$lm, $lid, $id]);
        $stmt = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, (int)$_SESSION['id'], 'link', json_encode(['linked_module'=>$lm,'linked_id'=>$lid]), $_SERVER['REMOTE_ADDR'] ?? null]);
        json(['status'=>'success','message'=>'Linked']);
    }
    if ($method === 'DELETE') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) json(['status'=>'error','message'=>'Missing id'],400);
        $stmt = $conn->prepare("UPDATE documents SET linked_module = NULL, linked_id = NULL WHERE id = ?");
        $stmt->execute([$id]);
        $stmt = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, (int)$_SESSION['id'], 'unlink', $_SERVER['REMOTE_ADDR'] ?? null]);
        json(['status'=>'success','message'=>'Unlinked']);
    }
    json(['status'=>'error','message'=>'Method not allowed'],405);
} catch (Throwable $e) { json(['status'=>'error','message'=>$e->getMessage()],500); }