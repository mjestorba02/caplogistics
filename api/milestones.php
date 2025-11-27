<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Require authenticated session (consistent with other pages)
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// function ensureTableExists(PDO $conn)
// {
//     $sql = "CREATE TABLE IF NOT EXISTS shipment_milestones (
//         id INT AUTO_INCREMENT PRIMARY KEY,
//         shipment_number VARCHAR(50) NOT NULL,
//         milestone VARCHAR(255) NOT NULL,
//         status ENUM('Pending','In Progress','Completed','Archived') NOT NULL DEFAULT 'Pending',
//         milestone_date DATE NOT NULL,
//         notes TEXT NULL,
//         archived_at DATETIME NULL,
//         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//         INDEX idx_shipment_number (shipment_number),
//         INDEX idx_status (status),
//         INDEX idx_milestone_date (milestone_date)
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
            $shipment_number = isset($_GET['shipment_number']) ? trim($_GET['shipment_number']) : null;
            $status = isset($_GET['status']) ? trim($_GET['status']) : null; // Pending, In Progress, Completed, Archived
            $archived = isset($_GET['archived']) ? trim($_GET['archived']) : '0'; // 0 | 1 | all
            $q = isset($_GET['q']) ? trim($_GET['q']) : null; // search by milestone or notes
            $date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : null; // YYYY-MM-DD
            $date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : null; // YYYY-MM-DD

            $where = [];
            $params = [];

            if ($id) { $where[] = 'sm.id = :id'; $params[':id'] = $id; }
            if ($shipment_number) { $where[] = 'sm.shipment_number = :shipment_number'; $params[':shipment_number'] = $shipment_number; }
            if ($status) { $where[] = 'sm.status = :status'; $params[':status'] = $status; }
            if ($archived === '1') { $where[] = 'sm.archived_at IS NOT NULL'; }
            elseif ($archived === '0') { $where[] = 'sm.archived_at IS NULL'; }
            if ($q) { $where[] = '(sm.milestone LIKE :q OR sm.notes LIKE :q)'; $params[':q'] = '%' . sanitize_like($q) . '%'; }
            if ($date_from) { $where[] = 'sm.milestone_date >= :date_from'; $params[':date_from'] = $date_from; }
            if ($date_to) { $where[] = 'sm.milestone_date <= :date_to'; $params[':date_to'] = $date_to; }

            $sql = 'SELECT sm.* FROM shipment_milestones sm';
            if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
            $sql .= ' ORDER BY sm.milestone_date DESC, sm.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($id && empty($rows)) { json_response(['status' => 'error', 'message' => 'Not found'], 404); }
            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            $shipment_number = trim($input['shipment_number'] ?? '');
            $milestone = trim($input['milestone'] ?? '');
            $status = trim($input['status'] ?? 'Pending');
            $milestone_date = trim($input['milestone_date'] ?? '');
            $notes = trim($input['notes'] ?? '');

            if (!$shipment_number || !$milestone || !$milestone_date) {
                json_response(['status' => 'error', 'message' => 'Missing required fields.'], 400);
            }

            $stmt = $conn->prepare('INSERT INTO shipment_milestones (shipment_number, milestone, status, milestone_date, notes) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$shipment_number, $milestone, $status, $milestone_date, $notes]);
            $id = (int)$conn->lastInsertId();

            $stmt2 = $conn->prepare('SELECT * FROM shipment_milestones WHERE id = ?');
            $stmt2->execute([$id]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);

            json_response(['status' => 'success', 'message' => 'Milestone created.', 'data' => $row], 201);
        }
        case 'PUT': {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
            $action = $qs['action'] ?? ($input['action'] ?? null); // archive | unarchive | null

            $id = isset($qs['id']) ? (int)$qs['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
            if ($id <= 0) { json_response(['status' => 'error', 'message' => 'Valid id is required for PUT.'], 400); }

            if ($action === 'archive') {
                $stmt = $conn->prepare("UPDATE shipment_milestones SET status = 'Archived', archived_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $stmt2 = $conn->prepare('SELECT * FROM shipment_milestones WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Archived successfully.', 'data' => $row]);
            }

            if ($action === 'unarchive') {
                $restore_status = trim($input['restore_status'] ?? 'Pending');
                if (!in_array($restore_status, ['Pending','In Progress','Completed'], true)) { $restore_status = 'Pending'; }
                $stmt = $conn->prepare('UPDATE shipment_milestones SET status = ?, archived_at = NULL WHERE id = ?');
                $stmt->execute([$restore_status, $id]);
                $stmt2 = $conn->prepare('SELECT * FROM shipment_milestones WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Unarchived successfully.', 'data' => $row]);
            }

            $fields = [];
            $params = [];
            if (isset($input['shipment_number'])) { $fields[] = 'shipment_number = :shipment_number'; $params[':shipment_number'] = trim($input['shipment_number']); }
            if (isset($input['milestone'])) { $fields[] = 'milestone = :milestone'; $params[':milestone'] = trim($input['milestone']); }
            if (isset($input['status'])) { $fields[] = 'status = :status'; $params[':status'] = trim($input['status']); }
            if (isset($input['milestone_date'])) { $fields[] = 'milestone_date = :milestone_date'; $params[':milestone_date'] = trim($input['milestone_date']); }
            if (isset($input['notes'])) { $fields[] = 'notes = :notes'; $params[':notes'] = trim($input['notes']); }

            if (empty($fields)) { json_response(['status' => 'error', 'message' => 'No fields to update.'], 400); }

            $sql = 'UPDATE shipment_milestones SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $params[':id'] = $id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $stmt2 = $conn->prepare('SELECT * FROM shipment_milestones WHERE id = ?');
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
