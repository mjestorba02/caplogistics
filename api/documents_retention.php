<?php
// api/documents_retention.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$userId = (int)$_SESSION['id'];
$method = $_SERVER['REQUEST_METHOD'];
function json($d,$c=200){ http_response_code($c); echo json_encode($d); exit; }

try {
    if ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $input);
        $id = (int)($input['id'] ?? 0);
        $days = (int)($input['retention_days'] ?? 0);
        if (!$id || !$days) json(['status'=>'error','message'=>'Invalid'],400);
        $stmt = $conn->prepare("UPDATE documents SET retention_days = ? WHERE id = ?");
        $stmt->execute([$days, $id]);
        $stmt = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $userId, 'set_retention', "days={$days}"]);
        json(['status'=>'success','message'=>'Updated']);
    }

    if ($method === 'POST' && ($_SESSION['role'] ?? '') === 'Admin') {
        // run retention now (archive those exceeding retention_days)
        $today = new DateTime();
        $stmt = $conn->prepare("SELECT id, created_at, retention_days FROM documents WHERE status NOT IN ('Archived','Deleted')");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $archived = 0;
        foreach ($rows as $r) {
            $uploaded = new DateTime($r['created_at']);
            $age = $uploaded->diff($today)->days;
            if ($age >= (int)$r['retention_days']) {
                $u = $conn->prepare("UPDATE documents SET status='Archived' WHERE id = ?");
                $u->execute([$r['id']]);
                $a = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, note) VALUES (?, ?, ?, ?)");
                $a->execute([$r['id'], $userId, 'auto_archive', 'Retention run']);
                $archived++;
            }
        }
        json(['status'=>'success','archived'=>$archived]);
    }

    json(['status'=>'error','message'=>'Method not allowed'],405);
} catch (Throwable $e) { json(['status'=>'error','message'=>$e->getMessage()],500); }