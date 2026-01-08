<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    switch ($method) {

        /* =======================
           GET (SEARCH + DATE)
        ======================== */
        case 'GET':
            $search    = trim($_GET['search'] ?? '');
            $fromDate  = $_GET['from_date'] ?? '';
            $toDate    = $_GET['to_date'] ?? '';
            $status = $_GET['status'] ?? '';

            $where = [];
            $params = [];

            if ($search !== '') {
                $where[] = "(shipment_id LIKE :search OR supplier_name LIKE :search)";
                $params[':search'] = "%{$search}%";
            }

            if ($fromDate !== '' && $toDate !== '') {
                $where[] = "DATE(created_at) BETWEEN :from_date AND :to_date";
                $params[':from_date'] = $fromDate;
                $params[':to_date'] = $toDate;
            } elseif ($fromDate !== '') {
                $where[] = "DATE(created_at) >= :from_date";
                $params[':from_date'] = $fromDate;
            } elseif ($toDate !== '') {
                $where[] = "DATE(created_at) <= :to_date";
                $params[':to_date'] = $toDate;
            }

            if ($status !== '') {
                $where[] = "status = :status";
                $params[':status'] = $status;
            }

            $sql = "SELECT * FROM inbound_logistics";

            if ($where) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            json_response([
                'status' => 'success',
                'shipments' => $shipments
            ]);
            break;

        /* =======================
           POST (CREATE)
        ======================== */
        case 'POST':
            if (empty($input['shipment_id']) || empty($input['supplier_name'])) {
                json_response(['status' => 'error', 'message' => 'Shipment ID and Supplier Name are required'], 400);
            }

            $stmt = $conn->prepare("
                INSERT INTO inbound_logistics
                (shipment_id, po_number, supplier_name, total_items, quality_status, handler_name, status, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $input['shipment_id'],
                $input['po_number'] ?? '',
                $input['supplier_name'],
                intval($input['total_items'] ?? 0),
                $input['quality_status'] ?? 'Pending',
                $input['handler_name'] ?? '',
                $input['status'] ?? 'Pending',
                $input['notes'] ?? ''
            ]);

            json_response(['status' => 'success', 'message' => 'Shipment added successfully']);
            break;

        /* =======================
           PUT (UPDATE)
        ======================== */
        case 'PUT':
            if (empty($input['id'])) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            $stmt = $conn->prepare("
                UPDATE inbound_logistics SET
                    shipment_id = ?,
                    po_number = ?,
                    supplier_name = ?,
                    total_items = ?,
                    quality_status = ?,
                    handler_name = ?,
                    status = ?,
                    notes = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $input['shipment_id'],
                $input['po_number'] ?? '',
                $input['supplier_name'],
                intval($input['total_items'] ?? 0),
                $input['quality_status'] ?? 'Pending',
                $input['handler_name'] ?? '',
                $input['status'] ?? 'Pending',
                $input['notes'] ?? '',
                $input['id']
            ]);

            json_response(['status' => 'success', 'message' => 'Shipment updated successfully']);
            break;

        /* =======================
           DELETE
        ======================== */
        case 'DELETE':
            if (empty($input['id'])) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            $stmt = $conn->prepare("DELETE FROM inbound_logistics WHERE id = ?");
            $stmt->execute([$input['id']]);

            json_response(['status' => 'success', 'message' => 'Shipment deleted successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}