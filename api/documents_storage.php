<?php
// api/documents_storage.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../helpers/storage_helpers.php';
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$method = $_SERVER['REQUEST_METHOD'];

function json($d,$c=200){ http_response_code($c); echo json_encode($d); exit; }

try {
    if ($method === 'GET') {
        $stmt = $conn->prepare("SELECT d.id, d.doc_code, d.title, df.file_name, df.file_stored, d.status FROM documents d LEFT JOIN document_files df ON df.document_id=d.id GROUP BY d.id ORDER BY d.created_at DESC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json(['status'=>'success','files'=>$rows]);
    }

    if ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $input);
        $id = (int)($input['id'] ?? 0);
        if (!$id) json(['status'=>'error','message'=>'Missing id'],400);
        // e.g., move to different linked_module
        $fields = [];
        $params = [];
        if (isset($input['linked_module'])) { $fields[] = 'linked_module = ?'; $params[] = $input['linked_module']; }
        if (isset($input['linked_id'])) { $fields[] = 'linked_id = ?'; $params[] = (int)$input['linked_id']; }
        if (!$fields) json(['status'=>'error','message'=>'No change'],400);
        $params[] = $id;
        $stmt = $conn->prepare("UPDATE documents SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($params);
        $stmt = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, (int)$_SESSION['id'], 'storage_update', json_encode($input), $_SERVER['REMOTE_ADDR'] ?? null]);
        json(['status'=>'success','message'=>'Updated']);
    }

    json(['status'=>'error','message'=>'Method not allowed'],405);
} catch (Throwable $e) { json(['status'=>'error','message'=>$e->getMessage()],500); }