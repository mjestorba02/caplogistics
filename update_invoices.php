<?php
include 'api/db.php';

try {
    // Update existing invoices with user names and items
    $stmt = $conn->query('SELECT * FROM invoices');
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($invoices as $inv) {
        $userId = $inv['user_id'];
        $shipmentId = $inv['shipment_id'];

        // Get user name
        $userName = '';
        try {
            $userStmt = $conn->prepare('SELECT name FROM users WHERE id = ?');
            $userStmt->execute([$userId]);
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            if ($userData) {
                $userName = $userData['name'];
            } else {
                // Try user_vendor table
                $vendorStmt = $conn->prepare('SELECT CONCAT(first_name, " ", last_name) as full_name FROM user_vendor WHERE id = ?');
                $vendorStmt->execute([$userId]);
                $vendorData = $vendorStmt->fetch(PDO::FETCH_ASSOC);
                $userName = $vendorData ? $vendorData['full_name'] : '';
            }
        } catch (Exception $e) { /* ignore */ }

        // Get delivery info from shipment
        $deliveryFrom = $deliveryTo = '';
        try {
            $shipStmt = $conn->prepare('SELECT origin, destination FROM shipments WHERE id = ?');
            $shipStmt->execute([$shipmentId]);
            $shipData = $shipStmt->fetch(PDO::FETCH_ASSOC);
            if ($shipData) {
                $deliveryFrom = $shipData['origin'];
                $deliveryTo = $shipData['destination'];
            }
        } catch (Exception $e) { /* ignore */ }

        // Get cart items and calculate subtotal
        $cartItems = [];
        $subtotal = 0;
        try {
            $cartStmt = $conn->prepare('
                SELECT c.quantity, p.name, p.price, (c.quantity * p.price) as total
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ');
            $cartStmt->execute([$userId]);
            $cartData = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cartData as $item) {
                $cartItems[] = [
                    'name' => $item['name'],
                    'quantity' => (int)$item['quantity'],
                    'unit_price' => (float)$item['price'],
                    'total' => (float)$item['total']
                ];
                $subtotal += (float)$item['total'];
            }
        } catch (Exception $e) { /* ignore */ }

        // Update the invoice
        try {
            $updateStmt = $conn->prepare('UPDATE invoices SET user_name = ?, delivery_from = ?, delivery_to = ?, items = ?, subtotal = ? WHERE id = ?');
            $updateStmt->execute([
                $userName,
                $deliveryFrom,
                $deliveryTo,
                json_encode($cartItems),
                $subtotal,
                $inv['id']
            ]);
            echo 'Updated invoice ' . $inv['id'] . "\n";
        } catch (Exception $e) {
            echo 'Error updating invoice ' . $inv['id'] . ': ' . $e->getMessage() . "\n";
        }
    }

    echo 'Invoice updates completed\n';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>