<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../helpers/storage_helpers.php';

if (!isset($_SESSION['id'])) { http_response_code(401); exit; }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { http_response_code(400); exit; }

$stmt = $conn->prepare("SELECT df.file_name, df.file_stored, d.status FROM document_files df JOIN documents d ON d.id = df.document_id WHERE df.document_id = ? LIMIT 1");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$file) { http_response_code(404); exit; }
if ($file['status'] === 'Deleted') { http_response_code(403); exit; }

// Log download
$stmt = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, ip_address) VALUES (?, ?, ?, ?)");
$stmt->execute([$id, (int)$_SESSION['id'], 'download', $_SERVER['REMOTE_ADDR'] ?? null]);

$path = get_document_path($file['file_stored']);
if (!$path || !file_exists($path)) { http_response_code(404); exit; }

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;