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
            
            $sql = "SELECT * FROM outbound_logistics ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($search) {
                $shipments = array_filter($shipments, function($s) use ($search) {
                    return stripos($s['shipment_number'] ?? '', $search) !== false || 
                           stripos($s['customer_name'] ?? '', $search) !== false;
                });
            }
            
            json_response(['status' => 'success', 'shipments' => array_values($shipments)]);
            break;

        case 'POST':
            $shipment_number = $input['shipment_number'] ?? '';
            $order_id = $input['order_id'] ?? '';
            $customer_name = $input['customer_name'] ?? '';
            $customer_email = $input['customer_email'] ?? '';
            $delivery_address = $input['delivery_address'] ?? '';
            $total_items = intval($input['total_items'] ?? 0);
            $carrier_name = $input['carrier_name'] ?? '';
            $delivery_status = $input['delivery_status'] ?? 'Pending';
            $notes = $input['notes'] ?? '';

            if (empty($shipment_number) || empty($customer_name)) {
                json_response(['status' => 'error', 'message' => 'Shipment Number and Customer Name are required'], 400);
            }

            try {
                $sql = "INSERT INTO outbound_logistics (shipment_number, order_id, customer_name, customer_email, delivery_address, total_items, carrier_name, delivery_status, notes, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$shipment_number, $order_id, $customer_name, $customer_email, $delivery_address, $total_items, $carrier_name, $delivery_status, $notes]);

                json_response(['status' => 'success', 'message' => 'Shipment created successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $shipment_number = $input['shipment_number'] ?? '';
            $items_packed = intval($input['items_packed'] ?? 0);
            $items_loaded = intval($input['items_loaded'] ?? 0);
            $delivery_status = $input['delivery_status'] ?? 'Pending';
            $tracking_number = $input['tracking_number'] ?? '';
            $dispatch_staff_name = $input['dispatch_staff_name'] ?? '';
            $notes = $input['notes'] ?? '';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'Shipment ID is required'], 400);
            }

            try {
                $sql = "UPDATE outbound_logistics SET shipment_number=?, items_packed=?, items_loaded=?, delivery_status=?, tracking_number=?, dispatch_staff_name=?, notes=? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$shipment_number, $items_packed, $items_loaded, $delivery_status, $tracking_number, $dispatch_staff_name, $notes, $id]);

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
                $sql = "DELETE FROM outbound_logistics WHERE id = ?";
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
