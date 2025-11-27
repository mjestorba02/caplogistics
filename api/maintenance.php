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
//     $sql = "CREATE TABLE IF NOT EXISTS asset_maintenance (
//         id INT AUTO_INCREMENT PRIMARY KEY,
//         asset_id VARCHAR(50) NOT NULL,
//         asset_name VARCHAR(255) NOT NULL,
//         maintenance_date DATE NOT NULL,
//         type ENUM('Preventive','Corrective','Predictive') NOT NULL DEFAULT 'Preventive',
//         status ENUM('Scheduled','Completed','Cancelled','Archived') NOT NULL DEFAULT 'Scheduled',
//         notes TEXT NULL,
//         archived_at DATETIME NULL,
//         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//         INDEX idx_asset_id (asset_id),
//         INDEX idx_status (status),
//         INDEX idx_date (maintenance_date)
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
            $q = isset($_GET['q']) ? trim($_GET['q']) : null; // search asset_name/asset_id/notes

            $where = [];
            $params = [];
            if ($id) { $where[] = 'm.id = :id'; $params[':id'] = $id; }
            if ($status) { $where[] = 'm.status = :status'; $params[':status'] = $status; }
            if ($archived === '1') { $where[] = 'm.archived_at IS NOT NULL'; }
            elseif ($archived === '0') { $where[] = 'm.archived_at IS NULL'; }
            if ($q) {
                $where[] = '(m.asset_name LIKE :q OR m.asset_id LIKE :q OR m.notes LIKE :q)';
                $params[':q'] = '%' . sanitize_like($q) . '%';
            }

            $sql = 'SELECT m.* FROM asset_maintenance m';
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY m.maintenance_date DESC, m.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($id && empty($rows)) { json_response(['status' => 'error', 'message' => 'Not found'], 404); }
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            $asset_id = trim($input['asset_id'] ?? '');
            $asset_name = trim($input['asset_name'] ?? '');
            $maintenance_date = trim($input['maintenance_date'] ?? '');
            $type = trim($input['type'] ?? 'Preventive');
            $status = trim($input['status'] ?? 'Scheduled');
            $notes = trim($input['notes'] ?? '');

            if (!$asset_id || !$asset_name || !$maintenance_date) {
                json_response(['status' => 'error', 'message' => 'asset_id, asset_name and maintenance_date are required.'], 400);
            }

            $stmt = $conn->prepare('INSERT INTO asset_maintenance (asset_id, asset_name, maintenance_date, type, status, notes) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$asset_id, $asset_name, $maintenance_date, $type, $status, $notes ?: null]);
            $id = (int)$conn->lastInsertId();

            $stmt2 = $conn->prepare('SELECT * FROM asset_maintenance WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'message' => 'Maintenance scheduled.', 'data' => $row], 201);
        }
        case 'PUT': {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
            $action = $qs['action'] ?? ($input['action'] ?? null);
            $id = isset($qs['id']) ? (int)$qs['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
            if ($id <= 0) { json_response(['status' => 'error', 'message' => 'Valid id is required for PUT.'], 400); }

            if ($action === 'archive') {
                $stmt = $conn->prepare("UPDATE asset_maintenance SET status = 'Archived', archived_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $stmt2 = $conn->prepare('SELECT * FROM asset_maintenance WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Archived successfully.', 'data' => $row]);
            }

            if ($action === 'unarchive') {
                $restore_status = trim($input['restore_status'] ?? 'Scheduled');
                if (!in_array($restore_status, ['Scheduled','Completed','Cancelled'], true)) { $restore_status = 'Scheduled'; }
                $stmt = $conn->prepare('UPDATE asset_maintenance SET status = ?, archived_at = NULL WHERE id = ?');
                $stmt->execute([$restore_status, $id]);
                $stmt2 = $conn->prepare('SELECT * FROM asset_maintenance WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Unarchived successfully.', 'data' => $row]);
            }

            $fields = [];
            $params = [];
            if (isset($input['asset_id'])) { $fields[] = 'asset_id = :asset_id'; $params[':asset_id'] = trim($input['asset_id']); }
            if (isset($input['asset_name'])) { $fields[] = 'asset_name = :asset_name'; $params[':asset_name'] = trim($input['asset_name']); }
            if (isset($input['maintenance_date'])) { $fields[] = 'maintenance_date = :maintenance_date'; $params[':maintenance_date'] = trim($input['maintenance_date']); }
            if (isset($input['type'])) { $fields[] = 'type = :type'; $params[':type'] = trim($input['type']); }
            if (isset($input['status'])) { $fields[] = 'status = :status'; $params[':status'] = trim($input['status']); }
            if (isset($input['notes'])) { $fields[] = 'notes = :notes'; $params[':notes'] = trim($input['notes']); }

            if (empty($fields)) { json_response(['status' => 'error', 'message' => 'No fields to update.'], 400); }

            $sql = 'UPDATE asset_maintenance SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $params[':id'] = $id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $stmt2 = $conn->prepare('SELECT * FROM asset_maintenance WHERE id = ?');
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
