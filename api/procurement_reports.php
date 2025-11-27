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
//     $sql = "CREATE TABLE IF NOT EXISTS procurement_contracts (
//         id INT AUTO_INCREMENT PRIMARY KEY,
//         reference VARCHAR(100) NOT NULL UNIQUE,
//         supplier VARCHAR(255) NOT NULL,
//         status ENUM('Active','Pending','Terminated','Expired','Cancelled','Completed','Archived') NOT NULL DEFAULT 'Pending',
//         start_date DATE NOT NULL,
//         end_date DATE NOT NULL,
//         total_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
//         currency VARCHAR(10) NOT NULL DEFAULT 'USD',
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
            $status = isset($_GET['status']) ? trim($_GET['status']) : null; // Active, Pending, etc.
            $archived = isset($_GET['archived']) ? trim($_GET['archived']) : '0'; // 0 | 1 | all
            $q = isset($_GET['q']) ? trim($_GET['q']) : null; // search by reference/supplier
            $start_from = isset($_GET['start_from']) ? trim($_GET['start_from']) : null; // YYYY-MM-DD
            $end_to = isset($_GET['end_to']) ? trim($_GET['end_to']) : null; // YYYY-MM-DD

            $where = [];
            $params = [];

            if ($id) {
                $where[] = 'pc.id = :id';
                $params[':id'] = $id;
            }

            if ($status) {
                $where[] = 'pc.status = :status';
                $params[':status'] = $status;
            }

            if ($archived === '1') {
                $where[] = 'pc.archived_at IS NOT NULL';
            } elseif ($archived === '0') {
                $where[] = 'pc.archived_at IS NULL';
            } else {
                // all -> no filter
            }

            if ($q) {
                $where[] = '(pc.reference LIKE :q OR pc.supplier LIKE :q)';
                $params[':q'] = '%' . sanitize_like($q) . '%';
            }

            if ($start_from) {
                $where[] = 'pc.start_date >= :start_from';
                $params[':start_from'] = $start_from;
            }

            if ($end_to) {
                $where[] = 'pc.end_date <= :end_to';
                $params[':end_to'] = $end_to;
            }

            $sql = 'SELECT pc.* FROM procurement_contracts pc';
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= ' ORDER BY pc.created_at DESC';

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
            // Create new contract
            $reference = trim($input['reference'] ?? '');
            $supplier = trim($input['supplier'] ?? '');
            $status = trim($input['status'] ?? 'Pending');
            $start_date = trim($input['start_date'] ?? ''); // YYYY-MM-DD
            $end_date = trim($input['end_date'] ?? ''); // YYYY-MM-DD
            $total_value = isset($input['total_value']) ? (float)$input['total_value'] : null;
            $currency = trim($input['currency'] ?? 'USD');

            if (!$reference || !$supplier || !$start_date || !$end_date || $total_value === null) {
                json_response(['status' => 'error', 'message' => 'Missing required fields.'], 400);
            }

            try {
                $stmt = $conn->prepare('INSERT INTO procurement_contracts (reference, supplier, status, start_date, end_date, total_value, currency) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$reference, $supplier, $status, $start_date, $end_date, $total_value, $currency]);
                $id = (int)$conn->lastInsertId();

                $stmt2 = $conn->prepare('SELECT * FROM procurement_contracts WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);

                json_response(['status' => 'success', 'message' => 'Contract created.', 'data' => $row], 201);
            } catch (PDOException $e) {
                // Unique constraint violation for reference
                if ((int)$e->getCode() === 23000) {
                    json_response(['status' => 'error', 'message' => 'Reference already exists.'], 409);
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
                $stmt = $conn->prepare("UPDATE procurement_contracts SET status = 'Archived', archived_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);

                $stmt2 = $conn->prepare('SELECT * FROM procurement_contracts WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Archived successfully.', 'data' => $row]);
            }

            if ($action === 'unarchive') {
                // Restore last non-archived status if client passes it, otherwise set Pending
                $restore_status = trim($input['restore_status'] ?? 'Pending');
                if (!in_array($restore_status, ['Active','Pending','Terminated','Expired','Cancelled','Completed'], true)) {
                    $restore_status = 'Pending';
                }
                $stmt = $conn->prepare('UPDATE procurement_contracts SET status = ?, archived_at = NULL WHERE id = ?');
                $stmt->execute([$restore_status, $id]);

                $stmt2 = $conn->prepare('SELECT * FROM procurement_contracts WHERE id = ?');
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'message' => 'Unarchived successfully.', 'data' => $row]);
            }

            // Standard update
            $fields = [];
            $params = [];

            if (isset($input['reference'])) { $fields[] = 'reference = :reference'; $params[':reference'] = trim($input['reference']); }
            if (isset($input['supplier'])) { $fields[] = 'supplier = :supplier'; $params[':supplier'] = trim($input['supplier']); }
            if (isset($input['status'])) { $fields[] = 'status = :status'; $params[':status'] = trim($input['status']); }
            if (isset($input['start_date'])) { $fields[] = 'start_date = :start_date'; $params[':start_date'] = trim($input['start_date']); }
            if (isset($input['end_date'])) { $fields[] = 'end_date = :end_date'; $params[':end_date'] = trim($input['end_date']); }
            if (isset($input['total_value'])) { $fields[] = 'total_value = :total_value'; $params[':total_value'] = (float)$input['total_value']; }
            if (isset($input['currency'])) { $fields[] = 'currency = :currency'; $params[':currency'] = trim($input['currency']); }

            if (empty($fields)) {
                json_response(['status' => 'error', 'message' => 'No fields to update.'], 400);
            }

            $sql = 'UPDATE procurement_contracts SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $params[':id'] = $id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $stmt2 = $conn->prepare('SELECT * FROM procurement_contracts WHERE id = ?');
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
