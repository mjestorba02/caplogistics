<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $conn->beginTransaction();

    $shipment_id = $data['shipment_id'];
    $items = $data['items']; // array of inventory_id, qty, department

    foreach ($items as $item) {
        $inventory_id = (int)$item['inventory_id'];
        $qty = (int)$item['quantity'];
        $department = $item['department'];

        // 🔒 lock inventory row
        $stmt = $conn->prepare("SELECT * FROM storage_inventory WHERE id = ? FOR UPDATE");
        $stmt->execute([$inventory_id]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$inv) {
            throw new Exception("Inventory item not found");
        }

        if ($inv['current_stock'] < $qty) {
            throw new Exception("Insufficient stock for {$inv['product_name']}");
        }

        // insert outbound item
        $stmt = $conn->prepare("
            INSERT INTO outbound_items 
            (outbound_id, inventory_id, sku, product_name, quantity, department)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $shipment_id,
            $inventory_id,
            $inv['sku'],
            $inv['product_name'],
            $qty,
            $department
        ]);

        // deduct inventory
        $stmt = $conn->prepare("
            UPDATE storage_inventory
            SET current_stock = current_stock - ?
            WHERE id = ?
        ");
        $stmt->execute([$qty, $inventory_id]);
    }

    $conn->commit();

    echo json_encode(['status'=>'success','message'=>'Outbound processed and inventory updated']);
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}