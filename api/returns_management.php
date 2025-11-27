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
            
            $sql = "SELECT * FROM returns_management ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($search) {
                $returns = array_filter($returns, function($r) use ($search) {
                    return stripos($r['return_id'] ?? '', $search) !== false || 
                           stripos($r['customer_name'] ?? '', $search) !== false;
                });
            }
            
            json_response(['status' => 'success', 'returns' => array_values($returns)]);
            break;

        case 'POST':
            $return_id = $input['return_id'] ?? '';
            $order_id = $input['order_id'] ?? '';
            $customer_name = $input['customer_name'] ?? '';
            $customer_email = $input['customer_email'] ?? '';
            $return_reason = $input['return_reason'] ?? '';
            $item_count = intval($input['item_count'] ?? 0);
            $original_purchase_price = floatval($input['original_purchase_price'] ?? 0);
            $return_status = $input['return_status'] ?? 'Initiated';

            if (empty($return_id) || empty($customer_name)) {
                json_response(['status' => 'error', 'message' => 'Return ID and Customer Name are required'], 400);
            }

            try {
                $sql = "INSERT INTO returns_management (return_id, order_id, customer_name, customer_email, return_reason, item_count, original_purchase_price, return_status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$return_id, $order_id, $customer_name, $customer_email, $return_reason, $item_count, $original_purchase_price, $return_status]);

                json_response(['status' => 'success', 'message' => 'Return created successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $return_id = $input['return_id'] ?? '';
            $item_classification = $input['item_classification'] ?? 'Resellable';
            $inspection_status = $input['inspection_status'] ?? 'Pending';
            $return_status = $input['return_status'] ?? 'Initiated';
            $inspector_name = $input['inspector_name'] ?? '';
            $inspector_notes = $input['inspector_notes'] ?? '';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'Return ID is required'], 400);
            }

            try {
                $sql = "UPDATE returns_management SET return_id=?, item_classification=?, inspection_status=?, return_status=?, inspector_name=?, inspector_notes=? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$return_id, $item_classification, $inspection_status, $return_status, $inspector_name, $inspector_notes, $id]);

                json_response(['status' => 'success', 'message' => 'Return updated successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            
            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'Return ID is required'], 400);
            }

            try {
                $sql = "DELETE FROM returns_management WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);

                json_response(['status' => 'success', 'message' => 'Return deleted successfully']);
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
