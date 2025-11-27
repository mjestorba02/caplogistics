<?php
// api/documents_delete.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$userId = (int)$_SESSION['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing id']); exit; }

try {
    // permission: owner or admin
    $stmt = $conn->prepare("SELECT created_by FROM documents WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo json_encode(['status'=>'error','message'=>'Not found']); exit; }
    if ($row['created_by'] != $userId && ($_SESSION['role'] ?? '') !== 'Admin') {
        echo json_encode(['status'=>'error','message'=>'Forbidden']); exit;
    }
    $stmt = $conn->prepare("UPDATE documents SET status='Deleted' WHERE id=?");
    $stmt->execute([$id]);
    $stmt = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $userId, 'delete', $_SERVER['REMOTE_ADDR'] ?? null]);
    echo json_encode(['status'=>'success','message'=>'Deleted']);
} catch (Throwable $e) {
    http_response_code(500); echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}