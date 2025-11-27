<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php'; // your PDO $conn
require_once __DIR__ . '/helpers/storage_helpers.php'; // we'll create below

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}
$userId = (int)$_SESSION['id'];
$method = $_SERVER['REQUEST_METHOD'];

function json_response($data, $code=200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    if ($method === 'GET') {
        // GET list or single
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM documents WHERE id=?");
            $stmt->execute([$id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$doc) json_response(['status'=>'error','message'=>'Not found'],404);
            // audit: view
            $stmt = $conn->prepare("INSERT INTO document_audit (document_id, user_id, action, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $userId, 'view', $_SERVER['REMOTE_ADDR'] ?? null]);
            json_response(['status'=>'success','document'=>$doc]);
        }

        // list with filters: status, search, po_no, shipment_no, supplier, date_from, date_to, limit, offset
        $where = []; $params = [];
        if (!empty($_GET['status']) && $_GET['status'] !== 'all') { $where[] = 'status = ?'; $params[] = $_GET['status']; }
        if (!empty($_GET['po_no'])) { $where[] = 'po_no = ?'; $params[] = $_GET['po_no']; }
        if (!empty($_GET['shipment_no'])) { $where[] = 'shipment_no = ?'; $params[] = $_GET['shipment_no']; }
        if (!empty($_GET['supplier'])) { $where[] = 'supplier LIKE ?'; $params[] = '%' . $_GET['supplier'] . '%'; }
        if (!empty($_GET['search'])) {
            $where[] = 'MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE)';
            $params[] = $_GET['search'];
        }
        $sql = "SELECT * FROM documents " . (count($where) ? 'WHERE ' . implode(' AND ', $where) : '') . " ORDER BY uploaded_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_response(['status'=>'success','documents'=>$rows]);
    }

    if ($method === 'POST') {
        // two modes: metadata-only (JSON) or file upload (multipart/form-data)
        if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            // metadata-only create (e.g., system-generated doc entry)
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $sql = "INSERT INTO documents (filename_orig, filename_stored, mime_type, size_bytes, uploaded_by, title, description, shipment_no, po_no, supplier, document_type, retention_days, metadata, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            // create a dummy stored filename placeholder when no file is uploaded
            $filename_orig = $input['filename_orig'] ?? 'n/a';
            $filename_stored = $input['filename_stored'] ?? 'n/a';
            $mime = $input['mime'] ?? 'application/octet-stream';
            $size = $input['size'] ?? 0;
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $filename_orig, $filename_stored, $mime, $size, $userId,
                $input['title'] ?? null, $input['description'] ?? null,
                $input['shipment_no'] ?? null, $input['po_no'] ?? null, $input['supplier'] ?? null,
                $input['document_type'] ?? null, $input['retention_days'] ?? 365,
                $input['metadata'] ? json_encode($input['metadata']) : null,
                $input['status'] ?? 'created'
            ]);
            $id = $conn->lastInsertId();
            // audit
            $stmt = $conn->prepare("INSERT INTO document_audit (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $userId, 'create_metadata', json_encode($input), $_SERVER['REMOTE_ADDR'] ?? null]);
            json_response(['status'=>'success','message'=>'Document created','id'=>$id]);
        } else {
            // file upload mode: expect multipart/form-data
            if (empty($_FILES['file'])) json_response(['status'=>'error','message'=>'No file uploaded'],400);
            $file = $_FILES['file'];
            // use helper to validate & store
            $allowed = ['application/pdf','image/png','image/jpeg','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','text/csv'];
            $maxBytes = 25 * 1024 * 1024; // 25 MB
            if ($file['error'] !== UPLOAD_ERR_OK) json_response(['status'=>'error','message'=>'Upload error'],400);
            if ($file['size'] > $maxBytes) json_response(['status'=>'error','message'=>'File too large'],400);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed)) json_response(['status'=>'error','message'=>'Unsupported file type: ' . $mime],400);

            // store file (returns stored filename)
            $stored = store_document_file($file['tmp_name'], $file['name']); 
            if (!$stored) json_response(['status'=>'error','message'=>'Storage failure'],500);

            // insert row
            $sql = "INSERT INTO documents (filename_orig, filename_stored, mime_type, size_bytes, uploaded_by, title, description, shipment_no, po_no, supplier, document_type, retention_days, metadata, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $file['name'], $stored, $mime, $file['size'], $userId,
                $_POST['title'] ?? null, $_POST['description'] ?? null,
                $_POST['shipment_no'] ?? null, $_POST['po_no'] ?? null, $_POST['supplier'] ?? null,
                $_POST['document_type'] ?? null, $_POST['retention_days'] ?? 365,
                !empty($_POST['metadata']) ? $_POST['metadata'] : null,
                'pending_verification'
            ]);
            $docId = $conn->lastInsertId();
            $stmt = $conn->prepare("INSERT INTO document_audit (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$docId, $userId, 'upload', null, $_SERVER['REMOTE_ADDR'] ?? null]);
            json_response(['status'=>'success','message'=>'Uploaded','id'=>$docId]);
        }
    }

    if ($method === 'PUT') {
        // update metadata or status
        parse_str(file_get_contents('php://input'), $input);
        if (empty($input['id'])) json_response(['status'=>'error','message'=>'Missing id'],400);
        $id = (int)$input['id'];

        if (!empty($input['action']) && $input['action'] === 'approve') {
            // only users with role 'approver' or admin should be allowed; check session role
            if (!in_array($_SESSION['role'] ?? '', ['Admin','Approver'])) {
                json_response(['status'=>'error','message'=>'Forbidden'],403);
            }
            $stmt = $conn->prepare("UPDATE documents SET status='approved' WHERE id=?");
            $stmt->execute([$id]);
            $stmt = $conn->prepare("INSERT INTO document_audit (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $userId, 'approve', $input['note'] ?? null, $_SERVER['REMOTE_ADDR'] ?? null]);
            json_response(['status'=>'success','message'=>'Approved']);
        } else {
            // update metadata
            $fields = []; $params = [];
            $allowed = ['title','description','shipment_no','po_no','supplier','document_type','retention_days','status'];
            foreach ($allowed as $f) {
                if (isset($input[$f])) { $fields[] = "$f = ?"; $params[] = $input[$f]; }
            }
            if (!$fields) json_response(['status'=>'error','message'=>'No fields to update'],400);
            $params[] = $id;
            $stmt = $conn->prepare("UPDATE documents SET " . implode(',', $fields) . " WHERE id=?");
            $stmt->execute($params);
            $stmt = $conn->prepare("INSERT INTO document_audit (document_id, user_id, action, note, ip_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $userId, 'edit_metadata', json_encode($input), $_SERVER['REMOTE_ADDR'] ?? null]);
            json_response(['status'=>'success','message'=>'Updated']);
        }
    }

    if ($method === 'DELETE') {
        // soft delete: update status & deleted_at
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) json_response(['status'=>'error','message'=>'Missing id'],400);
        // permission check: only admin or owner
        $stmt = $conn->prepare("SELECT uploaded_by FROM documents WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) json_response(['status'=>'error','message'=>'Not found'],404);
        if ($row['uploaded_by'] != $userId && !in_array($_SESSION['role'] ?? '', ['Admin'])) {
            json_response(['status'=>'error','message'=>'Forbidden'],403);
        }
        $stmt = $conn->prepare("UPDATE documents SET status='deleted', deleted_at=NOW() WHERE id=?");
        $stmt->execute([$id]);
        $stmt = $conn->prepare("INSERT INTO document_audit (document_id, user_id, action, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $userId, 'delete', $_SERVER['REMOTE_ADDR'] ?? null]);
        json_response(['status'=>'success','message'=>'Deleted']);
    }

    json_response(['status'=>'error','message'=>'Method not allowed'],405);

} catch (Throwable $e) {
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}