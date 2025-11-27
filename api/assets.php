<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Require authenticated session
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// function ensureTableExists(PDO $conn)
// {
//     $sql = "CREATE TABLE IF NOT EXISTS assets (
//         id INT AUTO_INCREMENT PRIMARY KEY,
//         asset_id VARCHAR(50) NOT NULL UNIQUE,
//         name VARCHAR(255) NOT NULL,
//         category VARCHAR(100) NOT NULL,
//         status ENUM('Available','In Use','Maintenance','Retired','Archived') NOT NULL DEFAULT 'Available',
//         assigned_to VARCHAR(100) NULL,
//         purchased_date DATE NULL,
//         notes TEXT NULL,
//         archived_at DATETIME NULL,
//         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//         INDEX idx_name (name),
//         INDEX idx_category (category),
//         INDEX idx_status (status)
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
            $archived = isset($_GET['archived']) ? trim($_GET['archived']) : '0'; // 0 | 1 | all
            $q = isset($_GET['q']) ? trim($_GET['q']) : null; // search name/asset_id/category/assigned_to

            $where = [];
            $params = [];
            if ($id) { $where[] = 'a.id = :id'; $params[':id'] = $id; }
            if ($status) { $where[] = 'a.status = :status'; $params[':status'] = $status; }
            if ($archived === '1') { $where[] = 'a.archived_at IS NOT NULL'; }
            elseif ($archived === '0') { $where[] = 'a.archived_at IS NULL'; }
            if ($q) {
                $where[] = '(a.name LIKE :q OR a.asset_id LIKE :q OR a.category LIKE :q OR a.assigned_to LIKE :q)';
                $params[':q'] = '%' . sanitize_like($q) . '%';
            }

            $sql = 'SELECT a.* FROM assets a';
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY a.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($id && empty($rows)) { json_response(['status' => 'error', 'message' => 'Not found'], 404); }
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            $asset_id = trim($input['asset_id'] ?? '');
            $name = trim($input['name'] ?? '');
            $category = trim($input['category'] ?? '');
            $status = trim($input['status'] ?? 'Available');
            $assigned_to = trim($input['assigned_to'] ?? '');
            $purchased_date = trim($input['purchased_date'] ?? ''); // YYYY-MM-DD
            $notes = trim($input['notes'] ?? '');

            if (!$asset_id || !$name || !$category) {
                json_response(['status' => 'error', 'message' => 'asset_id, name and category are required.'], 400);
            }

            try {
                $stmt = $conn->prepare('INSERT INTO assets (asset_id, name, category, status, assigned_to, purchased_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$asset_id, $name, $category, $status, $assigned_to ?: null, $purchased_date ?: null, $notes ?: null]);
                $id = (int)$conn->lastInsertId();

                $stmt2 = $conn->prepare('SELECT * FROM assets WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                json_response(['status' => 'success', 'message' => 'Asset created.', 'data' => $row], 201);
            } catch (PDOException $e) {
                if ((int)$e->getCode() === 23000) {
                    json_response(['status' => 'error', 'message' => 'asset_id already exists.'], 409);
                }
                throw $e;
            }
        }
        case 'PUT': {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
            $action = $qs['action'] ?? ($input['action'] ?? null); // archive | unarchive | null

            $id = isset($qs['id']) ? (int)$qs['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
            if ($id <= 0) { json_response(['status' => 'error', 'message' => 'Valid id is required for PUT.'], 400); }

            if ($action === 'archive') {
                $stmt = $conn->prepare("UPDATE assets SET status = 'Archived', archived_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $stmt2 = $conn->prepare('SELECT * FROM assets WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Archived successfully.', 'data' => $row]);
            }

            if ($action === 'unarchive') {
                $restore_status = trim($input['restore_status'] ?? 'Available');
                if (!in_array($restore_status, ['Available','In Use','Maintenance','Retired'], true)) { $restore_status = 'Available'; }
                $stmt = $conn->prepare('UPDATE assets SET status = ?, archived_at = NULL WHERE id = ?');
                $stmt->execute([$restore_status, $id]);
                $stmt2 = $conn->prepare('SELECT * FROM assets WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Unarchived successfully.', 'data' => $row]);
            }

            $fields = [];
            $params = [];
            if (isset($input['asset_id'])) { $fields[] = 'asset_id = :asset_id'; $params[':asset_id'] = trim($input['asset_id']); }
            if (isset($input['name'])) { $fields[] = 'name = :name'; $params[':name'] = trim($input['name']); }
            if (isset($input['category'])) { $fields[] = 'category = :category'; $params[':category'] = trim($input['category']); }
            if (isset($input['status'])) { $fields[] = 'status = :status'; $params[':status'] = trim($input['status']); }
            if (isset($input['assigned_to'])) { $fields[] = 'assigned_to = :assigned_to'; $params[':assigned_to'] = trim($input['assigned_to']); }
            if (isset($input['purchased_date'])) { $fields[] = 'purchased_date = :purchased_date'; $params[':purchased_date'] = trim($input['purchased_date']); }
            if (isset($input['notes'])) { $fields[] = 'notes = :notes'; $params[':notes'] = trim($input['notes']); }

            if (empty($fields)) { json_response(['status' => 'error', 'message' => 'No fields to update.'], 400); }

            $sql = 'UPDATE assets SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $params[':id'] = $id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $stmt2 = $conn->prepare('SELECT * FROM assets WHERE id = ?');
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
