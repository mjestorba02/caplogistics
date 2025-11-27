<?php
// api/documents_create.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';           // provide $conn (PDO)
require_once __DIR__ . '/../helpers/storage_helpers.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}
$userId = (int)$_SESSION['id'];

function json_response($data, $code=200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Only accept POST here for creation/upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['status'=>'error','message'=>'Method not allowed'],405);

try {
    // If incoming is multipart/form-data -> file upload
    $isMultipart = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

    // Basic input fields (title, description, shipment_no, po_no, supplier)
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $document_type = trim($_POST['document_type'] ?? '');
    $linked_module = trim($_POST['linked_module'] ?? null);
    $linked_id = isset($_POST['linked_id']) ? (int)$_POST['linked_id'] : null;
    $retention_days = isset($_POST['retention_days']) ? (int)$_POST['retention_days'] : 365;
    $metadata = !empty($_POST['metadata']) ? json_encode($_POST['metadata']) : null;

    // simple doc_code generator
    $doc_code = 'DOC-' . strtoupper(substr(sha1(uniqid((string)$userId, true)), 0, 8));

    // insert documents row first (file may be uploaded)
    $sql = "INSERT INTO documents (doc_code, title, description, document_type, created_by, linked_module, linked_id, retention_days, metadata, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$doc_code, $title, $description, $document_type, $userId, $linked_module, $linked_id, $retention_days, $metadata, 'Pending Verification']);
    $docId = (int)$conn->lastInsertId();

    // if file provided, validate & store
    if ($isMultipart && !empty($_FILES['file'])) {
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error code: ' . $file['error']);
        }
        $maxBytes = 30 * 1024 * 1024; // 30MB
        if ($file['size'] > $maxBytes) throw new Exception('File too large');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed = ['application/pdf','image/png','image/jpeg','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','text/csv'];
        if (!in_array($mime, $allowed)) throw new Exception('Unsupported file type: ' . $mime);
        $stored = store_document_file($file['tmp_name'], $file['name']);
        if (!$stored) throw new Exception('Failed to store file');

        $sql2 = "INSERT INTO document_files (document_id, file_name, file_stored, mime_type, size_bytes, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$docId, $file['name'], $stored, $mime, $file['size'], $userId]);

        // audit
        $stmt3 = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt3->execute([$docId, $userId, 'upload', null, $_SERVER['REMOTE_ADDR'] ?? null]);
    } else {
        // metadata-only create (system-generated)
        $stmt3 = $conn->prepare("INSERT INTO document_audit_log (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt3->execute([$docId, $userId, 'create_metadata', json_encode(['title'=>$title]), $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    json_response(['status'=>'success','message'=>'Document created','id'=>$docId,'doc_code'=>$doc_code]);

} catch (Throwable $e) {
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}