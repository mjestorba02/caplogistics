<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Optional: Require authenticated session
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Ensure table exists
// function ensureTableExists(PDO $conn)
// {
//     $sql = "CREATE TABLE IF NOT EXISTS purchase_requests (
//         id INT AUTO_INCREMENT PRIMARY KEY,
//         request_number VARCHAR(50) NOT NULL UNIQUE,
//         requested_by VARCHAR(100) NOT NULL,
//         items_quantity INT NOT NULL DEFAULT 0,
//         request_date DATE NOT NULL,
//         status ENUM('Pending','Approved','Rejected','Archived') NOT NULL DEFAULT 'Pending',
//         archived_at DATETIME NULL,
//         created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
//     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

//     $conn->exec($sql);
// }

// try {
//     ensureTableExists($conn);
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
    // Escape % and _ for LIKE queries
    return str_replace(['%', '_'], ['\\%', '\\_'], $term);
}

try {
    switch ($method) {
        case 'GET': {
            // Filters
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $status = isset($_GET['status']) ? trim($_GET['status']) : null; // Pending, Approved, Rejected, Archived
            $archived = isset($_GET['archived']) ? trim($_GET['archived']) : '0'; // 0 | 1 | all
            $q = isset($_GET['q']) ? trim($_GET['q']) : null; // search by request_number/requested_by

            $where = [];
            $params = [];

            if ($id) {
                $where[] = 'pr.id = :id';
                $params[':id'] = $id;
            }

            if ($status) {
                $where[] = 'pr.status = :status';
                $params[':status'] = $status;
            }

            if ($archived === '1') {
                $where[] = 'pr.archived_at IS NOT NULL';
            } elseif ($archived === '0') {
                $where[] = 'pr.archived_at IS NULL';
            } else {
                // all -> no filter
            }

            if ($q) {
                $where[] = '(pr.request_number LIKE :q OR pr.requested_by LIKE :q)';
                $params[':q'] = '%' . sanitize_like($q) . '%';
            }

            $sql = 'SELECT pr.* FROM purchase_requests pr';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY pr.created_at DESC';

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // If id requested and not found
            if ($id && empty($rows)) {
                json_response(['status' => 'error', 'message' => 'Not found'], 404);
            }

            json_response(['status' => 'success', 'data' => $id ? $rows[0] : $rows]);
        }
        case 'POST': {
            // Create new purchase request
            $request_number = trim($input['request_number'] ?? '');
            $requested_by = trim($input['requested_by'] ?? '');
            $items_quantity = isset($input['items_quantity']) ? (int)$input['items_quantity'] : null;
            $request_date = trim($input['request_date'] ?? ''); // YYYY-MM-DD
            $status = trim($input['status'] ?? 'Pending');

            if (!$request_number || !$requested_by || $items_quantity === null || !$request_date) {
                json_response(['status' => 'error', 'message' => 'Missing required fields.'], 400);
            }

            try {
                $stmt = $conn->prepare('INSERT INTO purchase_requests (request_number, requested_by, items_quantity, request_date, status) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$request_number, $requested_by, $items_quantity, $request_date, $status]);
                $id = (int)$conn->lastInsertId();

                $stmt2 = $conn->prepare('SELECT * FROM purchase_requests WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                json_response(['status' => 'success', 'message' => 'Purchase request created.', 'data' => $row], 201);
            } catch (PDOException $e) {
                // Unique constraint violation for request_number
                if ((int)$e->getCode() === 23000) {
                    json_response(['status' => 'error', 'message' => 'Request number already exists.'], 409);
                }
                throw $e;
            }
        }
        case 'PUT': {
            // Update or archive/unarchive
            parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
            $action = $qs['action'] ?? ($input['action'] ?? null); // 'archive' | 'unarchive' | null

            $id = isset($qs['id']) ? (int)$qs['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
            if ($id <= 0) {
                json_response(['status' => 'error', 'message' => 'Valid id is required for PUT.'], 400);
            }

            if ($action === 'archive') {
                $stmt = $conn->prepare("UPDATE purchase_requests SET status = 'Archived', archived_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);

                $stmt2 = $conn->prepare('SELECT * FROM purchase_requests WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Archived successfully.', 'data' => $row]);
            }

            if ($action === 'unarchive') {
                // Restore last non-archived status if client passes it, otherwise set Pending
                $restore_status = trim($input['restore_status'] ?? 'Pending');
                if (!in_array($restore_status, ['Pending','Approved','Rejected'], true)) {
                    $restore_status = 'Pending';
                }
                $stmt = $conn->prepare('UPDATE purchase_requests SET status = ?, archived_at = NULL WHERE id = ?');
                $stmt->execute([$restore_status, $id]);

                $stmt2 = $conn->prepare('SELECT * FROM purchase_requests WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Unarchived successfully.', 'data' => $row]);
            }

            // Standard update
            $fields = [];
            $params = [];

            if (isset($input['request_number'])) { $fields[] = 'request_number = :request_number'; $params[':request_number'] = trim($input['request_number']); }
            if (isset($input['requested_by'])) { $fields[] = 'requested_by = :requested_by'; $params[':requested_by'] = trim($input['requested_by']); }
            if (isset($input['items_quantity'])) { $fields[] = 'items_quantity = :items_quantity'; $params[':items_quantity'] = (int)$input['items_quantity']; }
            if (isset($input['request_date'])) { $fields[] = 'request_date = :request_date'; $params[':request_date'] = trim($input['request_date']); }
            if (isset($input['status'])) { $fields[] = 'status = :status'; $params[':status'] = trim($input['status']); }

            if (empty($fields)) {
                json_response(['status' => 'error', 'message' => 'No fields to update.'], 400);
            }

            $sql = 'UPDATE purchase_requests SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $params[':id'] = $id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $stmt2 = $conn->prepare('SELECT * FROM purchase_requests WHERE id = ?');
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
