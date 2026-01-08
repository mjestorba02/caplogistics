<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

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
    // Ensure outbound_items table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS outbound_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        outbound_id INT NOT NULL,
        inventory_id INT NOT NULL,
        sku VARCHAR(255),
        quantity INT NOT NULL,
        department VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (outbound_id) REFERENCES outbound_logistics(id) ON DELETE CASCADE,
        FOREIGN KEY (inventory_id) REFERENCES storage_inventory(id),
        INDEX idx_outbound_id (outbound_id),
        INDEX idx_inventory_id (inventory_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {

        /* ===================== GET ===================== */
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single shipment with items
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("SELECT * FROM outbound_logistics WHERE id = ?");
                $stmt->execute([$id]);
                $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$shipment) {
                    json_response(['status' => 'error', 'message' => 'Shipment not found'], 404);
                }
                $stmt = $conn->prepare("SELECT * FROM outbound_items WHERE outbound_id = ?");
                $stmt->execute([$id]);
                $shipment['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'shipment' => $shipment]);
            }

            $search = trim($_GET['search'] ?? '');

            $sql = "SELECT * FROM outbound_logistics ORDER BY created_at DESC";
            $stmt = $conn->query($sql);
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($search) {
                $shipments = array_filter($shipments, fn($s) =>
                    stripos($s['shipment_number'], $search) !== false ||
                    stripos($s['customer_name'], $search) !== false
                );
            }

            json_response(['status' => 'success', 'shipments' => array_values($shipments)]);
            break;

        /* ===================== POST ===================== */
        case 'POST':
            $items = $input['items'] ?? [];

            if (empty($items)) {
                json_response(['status'=>'error','message'=>'No outbound items'],400);
            }

            $conn->beginTransaction();

            $stmt = $conn->prepare("
                INSERT INTO outbound_logistics
                (shipment_number, order_id, customer_name, customer_email, delivery_address,
                 total_items, carrier_name, delivery_status, notes, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,NOW())
            ");

            $stmt->execute([
                $input['shipment_number'],
                $input['order_id'],
                $input['customer_name'],
                $input['customer_email'],
                $input['delivery_address'],
                count($items),
                $input['carrier_name'],
                $input['delivery_status'],
                $input['notes']
            ]);

            $outboundId = $conn->lastInsertId();

            foreach ($items as $item) {
                $inv = $conn->prepare("SELECT * FROM storage_inventory WHERE id=? FOR UPDATE");
                $inv->execute([$item['inventory_id']]);
                $inventory = $inv->fetch(PDO::FETCH_ASSOC);

                if (!$inventory || $inventory['available_stock'] < $item['quantity']) {
                    $conn->rollBack();
                    json_response(['status'=>'error','message'=>'Insufficient stock'],400);
                }

                $conn->prepare("
                    UPDATE storage_inventory
                    SET current_stock = current_stock - ?,
                        available_stock = available_stock - ?
                    WHERE id = ?
                ")->execute([
                    $item['quantity'],
                    $item['quantity'],
                    $item['inventory_id']
                ]);

                $conn->prepare("
                    INSERT INTO outbound_items
                    (outbound_id, inventory_id, sku, quantity, department)
                    VALUES (?,?,?,?,?)
                ")->execute([
                    $outboundId,
                    $item['inventory_id'],
                    $inventory['sku'],
                    $item['quantity'],
                    $item['department']
                ]);
            }

            $conn->commit();
            json_response(['status'=>'success','message'=>'Shipment created successfully']);
            break;

        /* ===================== DELETE ===================== */
        case 'DELETE':
            $id = $input['id'] ?? null;
            if (!$id) json_response(['status'=>'error','message'=>'ID required'],400);

            $stmt = $conn->prepare("DELETE FROM outbound_logistics WHERE id=?");
            $stmt->execute([$id]);
            json_response(['status'=>'success','message'=>'Shipment deleted']);
            break;

        default:
            json_response(['status'=>'error','message'=>'Method not allowed'],405);
    }

} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    json_response(['status'=>'error','message'=>$e->getMessage()],500);
}