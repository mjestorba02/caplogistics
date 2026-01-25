<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!$conn) {
    json_response(['status' => 'error', 'message' => 'Database connection failed'], 500);
}

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    // Ensure procurement_requests table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS procurement_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        storage_item_id INT,
        item_name VARCHAR(255) NOT NULL,
        sku VARCHAR(50),
        quantity INT NOT NULL,
        description TEXT,
        urgency ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
        request_type ENUM('Manual', 'Auto-Low-Stock') DEFAULT 'Manual',
        requester_id INT NOT NULL,
        requester_name VARCHAR(255) NOT NULL,
        date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        INDEX idx_requester_id (requester_id),
        INDEX idx_status (status),
        INDEX idx_storage_item_id (storage_item_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            $search = trim($_GET['search'] ?? '');
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';

            $conditions = [];
            $params = [];

            if ($search !== '') {
                $conditions[] = "(item_name LIKE ? OR requester_name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($dateFrom !== '') {
                $conditions[] = "DATE(date_requested) >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo !== '') {
                $conditions[] = "DATE(date_requested) <= ?";
                $params[] = $dateTo;
            }

            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

            $sql = "SELECT * FROM procurement_requests $where ORDER BY date_requested DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response(['status' => 'success', 'requests' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'POST':
            $item_name = $input['item_name'] ?? '';
            $sku = $input['sku'] ?? '';
            $quantity = intval($input['quantity'] ?? 0);
            $description = $input['description'] ?? '';
            $urgency = $input['urgency'] ?? 'Medium';
            $requester_id = $input['requester_id'] ?? '';
            $requester_name = $input['requester_name'] ?? '';
            $storage_item_id = intval($input['storage_item_id'] ?? 0);
            $request_type = $input['request_type'] ?? 'Manual'; // 'Manual' or 'Auto-Low-Stock'

            if (empty($item_name) || $quantity <= 0) {
                json_response(['status' => 'error', 'message' => 'Item name and quantity are required'], 400);
            }

            $sql = "INSERT INTO procurement_requests (item_name, sku, quantity, description, urgency, requester_id, requester_name, storage_item_id, request_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$item_name, $sku, $quantity, $description, $urgency, $requester_id, $requester_name, $storage_item_id > 0 ? $storage_item_id : null, $request_type]);

            json_response(['status' => 'success', 'message' => 'Request submitted successfully']);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $item_name = $input['item_name'] ?? '';
            $quantity = intval($input['quantity'] ?? 0);
            $description = $input['description'] ?? '';
            $urgency = $input['urgency'] ?? 'Medium';

            if (empty($id) || empty($item_name) || $quantity <= 0) {
                json_response(['status' => 'error', 'message' => 'ID, item name and quantity are required'], 400);
            }

            $sql = "UPDATE procurement_requests SET item_name=?, quantity=?, description=?, urgency=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$item_name, $quantity, $description, $urgency, $id]);

            json_response(['status' => 'success', 'message' => 'Request updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            $sql = "DELETE FROM procurement_requests WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);

            json_response(['status' => 'success', 'message' => 'Request deleted successfully']);
            break;

        /* =======================
           PATCH (APPROVE REQUEST & CREATE PO)
        ======================== */
        case 'PATCH':
            if (empty($input['id'])) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            // Get the request details
            $stmt = $conn->prepare("SELECT * FROM procurement_requests WHERE id = ?");
            $stmt->execute([$input['id']]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                json_response(['status' => 'error', 'message' => 'Request not found'], 404);
            }

            try {
                // Begin transaction
                $conn->beginTransaction();

                // Update request status to "Approved"
                $stmt = $conn->prepare("UPDATE procurement_requests SET status = 'Approved' WHERE id = ?");
                $stmt->execute([$input['id']]);

                // Auto-generate PO number
                $result = $conn->query("SELECT MAX(CAST(SUBSTRING(po_number, 4) AS UNSIGNED)) as max_num FROM purchase_orders WHERE po_number LIKE 'PO-%'");
                $row = $result->fetch(PDO::FETCH_ASSOC);
                $next_num = ($row['max_num'] ?? 0) + 1;
                $po_number = 'PO-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);

                // Create Purchase Order from the request
                $stmt = $conn->prepare("INSERT INTO purchase_orders (po_number, request_id, supplier, description, quantity, total_value, due_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), 'Draft', NOW())");
                $stmt->execute([
                    $po_number,
                    $request['id'],
                    $input['supplier'] ?? 'TBD',
                    $request['item_name'] . ' (SKU: ' . ($request['sku'] ?? 'N/A') . ') - ' . $request['description'],
                    $request['quantity'],
                    intval($request['quantity']) * 100, // Default unit price of 100
                ]);

                // Create Outbound Logistics entry
                $shipment_number = 'SHIP-' . time();
                $stmt = $conn->prepare("INSERT INTO outbound_logistics (shipment_number, order_id, customer_name, delivery_address, total_items, carrier_name, delivery_status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $shipment_number,
                    $po_number,
                    'Auto-generated from Request',
                    'Pending Address',
                    $request['quantity'],
                    'Pending',
                    'Pending',
                    'Auto-created from approved request: ' . $request['item_name']
                ]);

                // Commit transaction
                $conn->commit();

                json_response([
                    'status' => 'success',
                    'message' => 'Request approved! PO ' . $po_number . ' created successfully',
                    'po_number' => $po_number
                ]);

            } catch (Throwable $e) {
                // Rollback transaction on error
                $conn->rollBack();
                json_response(['status' => 'error', 'message' => 'Error approving request: ' . $e->getMessage()], 500);
            }
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>