<?php
// api/documents_tracking.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing id']); exit; }

$stmt = $conn->prepare("SELECT * FROM document_audit_log WHERE document_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT id, doc_code, title, status, created_at, updated_at FROM documents WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(['status'=>'success','document'=>$doc,'audit'=>$logs]);