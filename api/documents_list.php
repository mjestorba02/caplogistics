<?php
// api/documents_list.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$qs = $_GET;
$where = [];
$params = [];

if (!empty($qs['status']) && $qs['status'] !== 'all') { $where[] = 'status = ?'; $params[] = $qs['status']; }
if (!empty($qs['document_type'])) { $where[] = 'document_type = ?'; $params[] = $qs['document_type']; }
if (!empty($qs['linked_module'])) { $where[] = 'linked_module = ?'; $params[] = $qs['linked_module']; }
if (!empty($qs['search'])) {
    // basic LIKE search (you can replace with fulltext)
    $where[] = '(title LIKE ? OR description LIKE ? OR doc_code LIKE ? OR metadata LIKE ?)';
    $q = '%' . $qs['search'] . '%';
    $params = array_merge($params, [$q,$q,$q,$q]);
}
$sql = "SELECT d.*, df.file_stored, df.file_name
        FROM documents d
        LEFT JOIN (SELECT document_id, file_stored, file_name FROM document_files GROUP BY document_id) df ON df.document_id = d.id"
     . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY d.created_at DESC LIMIT 100";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['status'=>'success','documents'=>$rows]);