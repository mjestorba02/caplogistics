<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// function ensureTableExists(PDO $conn)
// {
//     $sql = "CREATE TABLE IF NOT EXISTS audits (
//         id INT AUTO_INCREMENT PRIMARY KEY,
//         title VARCHAR(255) NOT NULL,
//         type VARCHAR(100) NOT NULL,
//         performed_by VARCHAR(100) NOT NULL,
//         audit_date DATE NOT NULL,
//         status ENUM('Planned','In Progress','Completed','Archived') NOT NULL DEFAULT 'Planned',
//         notes TEXT NULL,
//         file_path VARCHAR(255) NULL,
//         archived_at DATETIME NULL,
//         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//         INDEX idx_type (type),
//         INDEX idx_status (status),
//         INDEX idx_audit_date (audit_date)
//     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
//     $conn->exec($sql);
// }

// try { ensureTableExists($conn); } catch (Throwable $e) {
//     http_response_code(500);
//     echo json_encode(['status' => 'error', 'message' => 'Failed ensuring table: ' . $e->getMessage()]);
//     exit;
// }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }

function json_response($data, int $code = 200) { http_response_code($code); echo json_encode($data); exit; }
function sanitize_like($term) { return str_replace(['%', '_'], ['\\%', '\\_'], $term); }

try {
    switch ($method) {
        case 'GET': {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $status = isset($_GET['status']) ? trim($_GET['status']) : null;
            $archived = isset($_GET['archived']) ? trim($_GET['archived']) : '0';
            $q = isset($_GET['q']) ? trim($_GET['q']) : null; // search title/performed_by

            $where = [];
            $params = [];
            if ($id) { $where[] = 'a.id = :id'; $params[':id'] = $id; }
            if ($status) { $where[] = 'a.status = :status'; $params[':status'] = $status; }
            if ($archived === '1') { $where[] = 'a.archived_at IS NOT NULL'; }
            elseif ($archived === '0') { $where[] = 'a.archived_at IS NULL'; }
            if ($q) { $where[] = '(a.title LIKE :q OR a.performed_by LIKE :q)'; $params[':q'] = '%' . sanitize_like($q) . '%'; }

            $sql = 'SELECT a.* FROM audits a';
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY a.audit_date DESC, a.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($id && empty($rows)) { json_response(['status' => 'error', 'message' => 'Not found'], 404); }
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            $title = trim($input['title'] ?? '');
            $type = trim($input['type'] ?? 'General');
            $performed_by = trim($input['performed_by'] ?? ($_SESSION['name'] ?? 'Unknown'));
            $audit_date = trim($input['audit_date'] ?? '');
            $status = trim($input['status'] ?? 'Planned');
            $notes = trim($input['notes'] ?? '');
            $file_path = trim($input['file_path'] ?? '');

            if (!$title || !$audit_date) {
                json_response(['status' => 'error', 'message' => 'title and audit_date are required.'], 400);
            }

            $stmt = $conn->prepare('INSERT INTO audits (title, type, performed_by, audit_date, status, notes, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $type, $performed_by, $audit_date, $status, $notes ?: null, $file_path ?: null]);
            $id = (int)$conn->lastInsertId();

            $stmt2 = $conn->prepare('SELECT * FROM audits WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'message' => 'Audit created.', 'data' => $row], 201);
        }
        case 'PUT': {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
            $action = $qs['action'] ?? ($input['action'] ?? null);
            $id = isset($qs['id']) ? (int)$qs['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
            if ($id <= 0) { json_response(['status' => 'error', 'message' => 'Valid id is required for PUT.'], 400); }

            if ($action === 'archive') {
                $stmt = $conn->prepare("UPDATE audits SET status = 'Archived', archived_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $stmt2 = $conn->prepare('SELECT * FROM audits WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Archived successfully.', 'data' => $row]);
            }

            if ($action === 'unarchive') {
                $restore_status = trim($input['restore_status'] ?? 'Planned');
                if (!in_array($restore_status, ['Planned','In Progress','Completed'], true)) { $restore_status = 'Planned'; }
                $stmt = $conn->prepare('UPDATE audits SET status = ?, archived_at = NULL WHERE id = ?');
                $stmt->execute([$restore_status, $id]);
                $stmt2 = $conn->prepare('SELECT * FROM audits WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Unarchived successfully.', 'data' => $row]);
            }

            $fields = [];
            $params = [];
            if (isset($input['title'])) { $fields[] = 'title = :title'; $params[':title'] = trim($input['title']); }
            if (isset($input['type'])) { $fields[] = 'type = :type'; $params[':type'] = trim($input['type']); }
            if (isset($input['performed_by'])) { $fields[] = 'performed_by = :performed_by'; $params[':performed_by'] = trim($input['performed_by']); }
            if (isset($input['audit_date'])) { $fields[] = 'audit_date = :audit_date'; $params[':audit_date'] = trim($input['audit_date']); }
            if (isset($input['status'])) { $fields[] = 'status = :status'; $params[':status'] = trim($input['status']); }
            if (isset($input['notes'])) { $fields[] = 'notes = :notes'; $params[':notes'] = trim($input['notes']); }
            if (isset($input['file_path'])) { $fields[] = 'file_path = :file_path'; $params[':file_path'] = trim($input['file_path']); }

            if (empty($fields)) { json_response(['status' => 'error', 'message' => 'No fields to update.'], 400); }

            $sql = 'UPDATE audits SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $params[':id'] = $id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $stmt2 = $conn->prepare('SELECT * FROM audits WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'message' => 'Updated successfully.', 'data' => $row]);
        }
        default: {
            header('Allow: GET, POST, PUT');
            json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
