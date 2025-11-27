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
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { 
    $input = []; 
}

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            $sql = "SELECT * FROM inbound_logistics ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($search) {
                $shipments = array_filter($shipments, function($s) use ($search) {
                    return stripos($s['shipment_id'] ?? '', $search) !== false || 
                           stripos($s['supplier_name'] ?? '', $search) !== false;
                });
            }
            
            json_response(['status' => 'success', 'shipments' => array_values($shipments)]);
            break;

        case 'POST':
            $shipment_id = $input['shipment_id'] ?? '';
            $po_number = $input['po_number'] ?? '';
            $supplier_name = $input['supplier_name'] ?? '';
            $total_items = intval($input['total_items'] ?? 0);
            $quality_status = $input['quality_status'] ?? 'Pending';
            $handler_name = $input['handler_name'] ?? '';
            $status = $input['status'] ?? 'Pending';
            $notes = $input['notes'] ?? '';

            if (empty($shipment_id) || empty($supplier_name)) {
                json_response(['status' => 'error', 'message' => 'Shipment ID and Supplier Name are required'], 400);
            }

            try {
                $sql = "INSERT INTO inbound_logistics (shipment_id, po_number, supplier_name, total_items, quality_status, handler_name, status, notes, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$shipment_id, $po_number, $supplier_name, $total_items, $quality_status, $handler_name, $status, $notes]);

                json_response(['status' => 'success', 'message' => 'Shipment added successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $shipment_id = $input['shipment_id'] ?? '';
            $supplier_name = $input['supplier_name'] ?? '';
            $items_received = intval($input['items_received'] ?? 0);
            $items_verified = intval($input['items_verified'] ?? 0);
            $quality_status = $input['quality_status'] ?? 'Pending';
            $storage_location = $input['storage_location'] ?? '';
            $status = $input['status'] ?? 'Pending';
            $notes = $input['notes'] ?? '';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'Shipment ID is required'], 400);
            }

            try {
                $sql = "UPDATE inbound_logistics SET shipment_id=?, supplier_name=?, items_received=?, items_verified=?, quality_status=?, storage_location=?, status=?, notes=? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$shipment_id, $supplier_name, $items_received, $items_verified, $quality_status, $storage_location, $status, $notes, $id]);

                json_response(['status' => 'success', 'message' => 'Shipment updated successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            
            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'Shipment ID is required'], 400);
            }

            try {
                $sql = "DELETE FROM inbound_logistics WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);

                json_response(['status' => 'success', 'message' => 'Shipment deleted successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
