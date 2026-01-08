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
            // MODE: inventory selector for outbound
            if (isset($_GET['mode']) && $_GET['mode'] === 'outbound') {

                $stmt = $conn->query("
                    SELECT id, sku, product_name, available_stock
                    FROM storage_inventory
                    WHERE available_stock > 0
                    ORDER BY product_name
                ");

                json_response([
                    'status' => 'success',
                    'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)
                ]);
            }

            // DEFAULT inventory listing (existing behavior)
            $search = trim($_GET['search'] ?? '');
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo   = $_GET['date_to'] ?? '';

            $conditions = [];
            $params = [];

            if ($search !== '') {
                $conditions[] = "(sku LIKE ? OR product_name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($dateFrom !== '') {
                $conditions[] = "DATE(created_at) >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo !== '') {
                $conditions[] = "DATE(created_at) <= ?";
                $params[] = $dateTo;
            }

            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

            $sql = "
                SELECT *
                FROM storage_inventory
                $where
                ORDER BY created_at DESC
            ";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response([
                'status' => 'success',
                'items'  => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
            break;

        case 'POST':
            $sku = $input['sku'] ?? '';
            $product_name = $input['product_name'] ?? '';
            $category = $input['category'] ?? '';
            $bin_location = $input['bin_location'] ?? '';
            $warehouse_zone = $input['warehouse_zone'] ?? '';
            $current_stock = intval($input['current_stock'] ?? 0);
            $min_stock = intval($input['min_stock'] ?? 10);
            $max_stock = intval($input['max_stock'] ?? 1000);
            $movement_frequency = $input['movement_frequency'] ?? 'Medium';
            $supplier_name = $input['supplier_name'] ?? '';

            if (empty($sku) || empty($product_name)) {
                json_response(['status' => 'error', 'message' => 'SKU and Product Name are required'], 400);
            }

            try {
                $available_stock = $current_stock - 0; // reserved_stock default 0
                $sql = "INSERT INTO storage_inventory (sku, product_name, category, bin_location, warehouse_zone, current_stock, min_stock, max_stock, reserved_stock, available_stock, movement_frequency, supplier_name, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$sku, $product_name, $category, $bin_location, $warehouse_zone, $current_stock, $min_stock, $max_stock, $available_stock, $movement_frequency, $supplier_name]);

                json_response(['status' => 'success', 'message' => 'Item added to inventory successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $sku = $input['sku'] ?? '';
            $product_name = $input['product_name'] ?? '';
            $current_stock = intval($input['current_stock'] ?? 0);
            $reserved_stock = intval($input['reserved_stock'] ?? 0);
            $bin_location = $input['bin_location'] ?? '';
            $warehouse_zone = $input['warehouse_zone'] ?? '';
            $movement_frequency = $input['movement_frequency'] ?? 'Medium';
            $stock_status = $input['stock_status'] ?? 'Optimal';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'Item ID is required'], 400);
            }

            try {
                $available_stock = $current_stock - $reserved_stock;
                $sql = "UPDATE storage_inventory SET sku=?, product_name=?, current_stock=?, reserved_stock=?, available_stock=?, bin_location=?, warehouse_zone=?, movement_frequency=?, stock_status=? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$sku, $product_name, $current_stock, $reserved_stock, $available_stock, $bin_location, $warehouse_zone, $movement_frequency, $stock_status, $id]);

                json_response(['status' => 'success', 'message' => 'Item updated successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';
            
            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'Item ID is required'], 400);
            }

            try {
                $sql = "DELETE FROM storage_inventory WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);

                json_response(['status' => 'success', 'message' => 'Item deleted successfully']);
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
