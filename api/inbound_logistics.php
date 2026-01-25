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
                (shipment_id, po_number, supplier_name, total_items, items_received, items_verified, quality_status, handler_name, status, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $input['shipment_id'],
                $input['po_number'] ?? '',
                $input['supplier_name'],
                intval($input['total_items'] ?? 0),
                intval($input['items_received'] ?? 0),
                intval($input['items_verified'] ?? 0),
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
                    items_received = ?,
                    items_verified = ?,
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
                intval($input['items_received'] ?? 0),
                intval($input['items_verified'] ?? 0),
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

        /* =======================
           PATCH (APPROVE/ACCEPT SHIPMENT)
        ======================== */
        case 'PATCH':
            if (empty($input['id'])) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            // Get the shipment details
            $stmt = $conn->prepare("SELECT * FROM inbound_logistics WHERE id = ?");
            $stmt->execute([$input['id']]);
            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$shipment) {
                json_response(['status' => 'error', 'message' => 'Shipment not found'], 404);
            }

            try {
                // Begin transaction
                $conn->beginTransaction();

                // Update shipment status to "Putaway Complete" (approved for storage)
                $stmt = $conn->prepare("
                    UPDATE inbound_logistics 
                    SET status = 'Putaway Complete', updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$input['id']]);

                // Create item in storage_inventory from the shipment data
                // Use items_received as the current_stock and available_stock
                $stmt = $conn->prepare("
                    INSERT INTO storage_inventory 
                    (sku, product_name, category, bin_location, warehouse_zone, current_stock, 
                     min_stock, max_stock, reserved_stock, available_stock, movement_frequency, 
                     supplier_name, supplier_id, stock_status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                // Extract details from shipment
                $sku = 'SKU-' . str_replace(' ', '-', $shipment['shipment_id']);
                $product_name = 'Shipment ' . $shipment['shipment_id'] . ' from ' . $shipment['supplier_name'];
                $category = $input['category'] ?? 'Received';
                $bin_location = $input['bin_location'] ?? 'PENDING-LOCATION';
                $warehouse_zone = $input['warehouse_zone'] ?? 'ZONE-INBOUND';
                
                // Use items_received as current_stock, if not provided use total_items
                $items_received = intval($shipment['items_received'] ?? $shipment['total_items'] ?? 0);
                $current_stock = $items_received;
                $min_stock = intval($shipment['total_items'] ?? 100) * 0.1; // 10% of total
                $max_stock = intval($shipment['total_items'] ?? 100) * 2; // 200% of total
                $available_stock = $current_stock; // All received items are available
                $reserved_stock = 0;
                $movement_frequency = $input['movement_frequency'] ?? 'Medium';
                
                // Determine stock status based on received vs expected
                if ($items_received == intval($shipment['total_items'] ?? 0)) {
                    $stock_status = 'Optimal';
                } elseif ($items_received >= intval($shipment['total_items'] ?? 0) * 0.8) {
                    $stock_status = 'Low';
                } else {
                    $stock_status = 'Critical';
                }

                $stmt->execute([
                    $sku,
                    $product_name,
                    $category,
                    $bin_location,
                    $warehouse_zone,
                    $current_stock,
                    $min_stock,
                    $max_stock,
                    $reserved_stock,
                    $available_stock,
                    $movement_frequency,
                    $shipment['supplier_name'],
                    null, // supplier_id
                    $stock_status
                ]);

                // Commit transaction
                $conn->commit();

                json_response([
                    'status' => 'success', 
                    'message' => 'Shipment approved! ' . $items_received . ' items moved to Storage & Inventory'
                ]);

            } catch (Throwable $e) {
                // Rollback transaction on error
                $conn->rollBack();
                json_response(['status' => 'error', 'message' => 'Error approving shipment: ' . $e->getMessage()], 500);
            }
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}