<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Require authentication (session-based)
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Ensure table exists for suppliers/bidding
// function ensureSuppliersTable(PDO $conn)
// {
//     $sql = "CREATE TABLE IF NOT EXISTS suppliers (
//         id INT AUTO_INCREMENT PRIMARY KEY,
//         name VARCHAR(150) NOT NULL,
//         email VARCHAR(150) NOT NULL UNIQUE,
//         rfqs_sent INT NOT NULL DEFAULT 0,
//         bids_submitted INT NOT NULL DEFAULT 0,
//         status ENUM('Active','Inactive','Archived') NOT NULL DEFAULT 'Active',
//         archived_at DATETIME NULL,
//         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
//     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

//     $conn->exec($sql);
// }

// try {
//     ensureSuppliersTable($conn);
// } catch (Throwable $e) {
//     http_response_code(500);
//     echo json_encode(['status' => 'error', 'message' => 'Failed ensuring table: ' . $e->getMessage()]);
//     exit;
// }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }

function json_response($data, int $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function sanitize_like($term) {
    return str_replace(['%', '_'], ['\\%', '\\_'], $term);
}

try {
    switch ($method) {
        case 'GET': {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $status = isset($_GET['status']) ? trim($_GET['status']) : null; // Active, Inactive, Archived
            $archived = isset($_GET['archived']) ? trim($_GET['archived']) : '0'; // 0 | 1 | all
            $q = isset($_GET['q']) ? trim($_GET['q']) : null; // search by name/email

            $where = [];
            $params = [];

            if ($id) {
                $where[] = 's.id = :id';
                $params[':id'] = $id;
            }

            if ($status) {
                $where[] = 's.status = :status';
                $params[':status'] = $status;
            }

            if ($archived === '1') {
                $where[] = 's.archived_at IS NOT NULL';
            } elseif ($archived === '0') {
                $where[] = 's.archived_at IS NULL';
            }

            if ($q) {
                $where[] = '(s.name LIKE :q OR s.email LIKE :q)';
                $params[':q'] = '%' . sanitize_like($q) . '%';
            }

            $sql = 'SELECT s.* FROM suppliers s';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY s.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($id && empty($rows)) {
                json_response(['status' => 'error', 'message' => 'Not found'], 404);
            }

            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            // Create supplier
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $rfqs_sent = isset($input['rfqs_sent']) ? (int)$input['rfqs_sent'] : 0;
            $bids_submitted = isset($input['bids_submitted']) ? (int)$input['bids_submitted'] : 0;
            $status = trim($input['status'] ?? 'Active');

            if (!$name || !$email) {
                json_response(['status' => 'error', 'message' => 'Name and email are required.'], 400);
            }

            try {
                $stmt = $conn->prepare('INSERT INTO suppliers (name, email, rfqs_sent, bids_submitted, status) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $email, $rfqs_sent, $bids_submitted, $status]);
                $id = (int)$conn->lastInsertId();

                $stmt2 = $conn->prepare('SELECT * FROM suppliers WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                json_response(['status' => 'success', 'message' => 'Supplier created.', 'data' => $row], 201);
            } catch (PDOException $e) {
                if ((int)$e->getCode() === 23000) { // duplicate email
                    json_response(['status' => 'error', 'message' => 'Email already exists.'], 409);
                }
                throw $e;
            }
        }
        case 'PUT': {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
            $id = isset($qs['id']) ? (int)$qs['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
            $action = $qs['action'] ?? ($input['action'] ?? null); // archive | unarchive

            if ($id <= 0) {
                json_response(['status' => 'error', 'message' => 'Valid id is required for PUT.'], 400);
            }

            if ($action === 'archive') {
                $stmt = $conn->prepare("UPDATE suppliers SET status = 'Archived', archived_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $stmt2 = $conn->prepare('SELECT * FROM suppliers WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Archived successfully.', 'data' => $row]);
            }

            if ($action === 'unarchive') {
                $restore_status = trim($input['restore_status'] ?? 'Active');
                if (!in_array($restore_status, ['Active','Inactive'], true)) { $restore_status = 'Active'; }
                $stmt = $conn->prepare('UPDATE suppliers SET status = ?, archived_at = NULL WHERE id = ?');
                $stmt->execute([$restore_status, $id]);
                $stmt2 = $conn->prepare('SELECT * FROM suppliers WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Unarchived successfully.', 'data' => $row]);
            }

            // Standard update
            $fields = [];
            $params = [];

            if (isset($input['name'])) { $fields[] = 'name = :name'; $params[':name'] = trim($input['name']); }
            if (isset($input['email'])) { $fields[] = 'email = :email'; $params[':email'] = trim($input['email']); }
            if (isset($input['rfqs_sent'])) { $fields[] = 'rfqs_sent = :rfqs_sent'; $params[':rfqs_sent'] = (int)$input['rfqs_sent']; }
            if (isset($input['bids_submitted'])) { $fields[] = 'bids_submitted = :bids_submitted'; $params[':bids_submitted'] = (int)$input['bids_submitted']; }
            if (isset($input['status'])) { $fields[] = 'status = :status'; $params[':status'] = trim($input['status']); }

            if (empty($fields)) {
                json_response(['status' => 'error', 'message' => 'No fields to update.'], 400);
            }

            $sql = 'UPDATE suppliers SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $params[':id'] = $id;
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
            } catch (PDOException $e) {
                if ((int)$e->getCode() === 23000) {
                    json_response(['status' => 'error', 'message' => 'Email already exists.'], 409);
                }
                throw $e;
            }

            $stmt2 = $conn->prepare('SELECT * FROM suppliers WHERE id = ?');
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
