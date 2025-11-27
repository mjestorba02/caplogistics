<?php
session_start();

require_once __DIR__ . '/db.php';

// We will handle both JSON for listing and multipart for upload
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function json_response($data, int $code = 200) { http_response_code($code); header('Content-Type: application/json'); echo json_encode($data); exit; }

if (!isset($_SESSION['id'])) {
    json_response(['status' => 'error', 'message' => 'Unauthorized'], 401);
}

$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }

try {
    switch ($method) {
        case 'GET': {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $type = isset($_GET['type']) ? trim($_GET['type']) : null;
            $archived = isset($_GET['archived']) ? $_GET['archived'] : null;
            $q = isset($_GET['q']) ? trim($_GET['q']) : null;

            $where = [];
            $params = [];
            
            if ($id) { 
                $where[] = 'd.id = :id'; 
                $params[':id'] = $id; 
            }
            
            if ($type && $type !== 'all') { 
                $where[] = 'd.type = :type'; 
                $params[':type'] = $type; 
            }
            
            // Handle archive status filter
            if ($archived !== null) {
                if ($archived === '1' || $archived === 'true') { 
                    $where[] = 'd.archived_at IS NOT NULL'; 
                } elseif ($archived === '0' || $archived === 'false') { 
                    $where[] = 'd.archived_at IS NULL'; 
                }
            }
            
            if ($q) { 
                $where[] = '(d.name LIKE :q)'; 
                $params[':q'] = '%' . $q . '%'; 
            }

            $sql = 'SELECT d.* FROM documents d';
            if ($where) { 
                $sql .= ' WHERE ' . implode(' AND ', $where); 
            }
            $sql .= ' ORDER BY d.uploaded_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($id && empty($rows)) { 
                json_response(['status' => 'error', 'message' => 'Not found'], 404); 
            }
            
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            // Expect multipart/form-data for upload
            if (!isset($_FILES['file'])) {
                json_response(['status' => 'error', 'message' => 'File is required.'], 400);
            }
            $name = trim($_POST['name'] ?? '');
            $type = trim($_POST['type'] ?? 'Other');
            if (!$name) { $name = $_FILES['file']['name']; }

            $filename = basename($_FILES['file']['name']);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($filename, PATHINFO_FILENAME));
            
            // Define base path for your application
            $basePath = '/logistics1_ecommerce'; // Add your subdirectory here
            
            $targetRel = $basePath . '/uploads/' . $safeBase . '_' . time() . ($ext ? ('.' . $ext) : '');
            $targetAbs = realpath(__DIR__ . '/..') . str_replace($basePath, '', $targetRel);

            if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetAbs)) {
                json_response(['status' => 'error', 'message' => 'Failed to move uploaded file.'], 500);
            }

            $uploaded_by = $_SESSION['name'] ?? 'Unknown';

            $stmt = $conn->prepare('INSERT INTO documents (name, type, file_path, uploaded_by) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $type, $targetRel, $uploaded_by]);
            $id = (int)$conn->lastInsertId();

            $stmt2 = $conn->prepare('SELECT * FROM documents WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'message' => 'Uploaded successfully.', 'data' => $row], 201);
        }
        case 'PUT': {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
            $action = $qs['action'] ?? null; // archive | unarchive
            $id = isset($qs['id']) ? (int)$qs['id'] : 0;
            if ($id <= 0) { json_response(['status' => 'error', 'message' => 'Valid id is required.'], 400); }

            if ($action === 'archive') {
                $stmt = $conn->prepare("UPDATE documents SET archived_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $stmt2 = $conn->prepare('SELECT * FROM documents WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Archived successfully.', 'data' => $row]);
            }

            if ($action === 'unarchive') {
                $stmt = $conn->prepare('UPDATE documents SET archived_at = NULL WHERE id = ?');
                $stmt->execute([$id]);
                $stmt2 = $conn->prepare('SELECT * FROM documents WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Unarchived successfully.', 'data' => $row]);
            }

            json_response(['status' => 'error', 'message' => 'Unsupported operation'], 400);
        }
        default: {
            header('Allow: GET, POST, PUT');
            json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}