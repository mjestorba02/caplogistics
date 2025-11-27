<?php
// Delivery / Orders API (RESTful)
// Methods:
//  GET:    List orders for current vendor, or get a single order with items (?id=...)
//  POST:   Create order from current cart: requires address, optional notes
//  PUT:    Update order status (?id=... with JSON { status })
//  DELETE: Cancel order (?id=...) => set status='cancelled'

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

if (!isset($_SESSION['vendor_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$vendor_id = (int)$_SESSION['vendor_id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function ensure_tables(PDO $conn) {
    // orders table
    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT UNSIGNED NOT NULL,
        status VARCHAR(32) NOT NULL DEFAULT 'pending',
        address TEXT NOT NULL,
        notes TEXT NULL,
        total DECIMAL(12,2) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_vendor (vendor_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // order_items table
    $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity INT UNSIGNED NOT NULL DEFAULT 1,
        price_snapshot DECIMAL(12,2) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_order (order_id),
        INDEX idx_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // cart_items table (must exist for moving items). Create if missing to avoid failures
    $conn->exec("CREATE TABLE IF NOT EXISTS cart_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_vendor_product (vendor_id, product_id),
        INDEX idx_vendor (vendor_id),
        INDEX idx_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function get_json_input(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

try {
    ensure_tables($conn);

    switch ($method) {
        case 'GET': {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id > 0) {
                // Single order + items; verify ownership
                $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND vendor_id = ?');
                $stmt->execute([$id, $vendor_id]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$order) {
                    json_response(['status' => 'error', 'message' => 'Order not found'], 404);
                }
                $it = $conn->prepare('SELECT product_id, quantity, price_snapshot FROM order_items WHERE order_id = ?');
                $it->execute([$id]);
                $items = $it->fetchAll(PDO::FETCH_ASSOC);
                json_response(['status' => 'success', 'data' => ['order' => $order, 'items' => $items]]);
            }
            // List orders for vendor
            $stmt = $conn->prepare('SELECT id, status, address, notes, total, created_at, updated_at FROM orders WHERE vendor_id = ? ORDER BY created_at DESC');
            $stmt->execute([$vendor_id]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'data' => $orders]);
        }

        case 'POST': {
            // Create order from cart
            $input = $_POST;
            if (empty($input)) $input = get_json_input();
            $address = trim($input['address'] ?? '');
            $notes   = trim($input['notes'] ?? '');
            if ($address === '') {
                json_response(['status' => 'error', 'message' => 'Address is required'], 400);
            }

            // Load cart items
            $stmt = $conn->prepare('SELECT product_id, quantity FROM cart_items WHERE vendor_id = ?');
            $stmt->execute([$vendor_id]);
            $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$cart || count($cart) === 0) {
                json_response(['status' => 'error', 'message' => 'Cart is empty'], 400);
            }

            // Compute total (no price source available -> leave as 0; extend later to snapshot prices)
            $total = 0.00;

            $conn->beginTransaction();
            try {
                // Create order
                $ins = $conn->prepare('INSERT INTO orders (vendor_id, status, address, notes, total) VALUES (?, ?, ?, ?, ?)');
                $ins->execute([$vendor_id, 'pending', $address, $notes !== '' ? $notes : null, $total]);
                $order_id = (int)$conn->lastInsertId();

                // Move cart items -> order_items
                $insItem = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_snapshot) VALUES (?, ?, ?, ?)');
                foreach ($cart as $row) {
                    $pid = (int)$row['product_id'];
                    $qty = (int)$row['quantity'];
                    $insItem->execute([$order_id, $pid, $qty, 0.00]);
                }

                // Clear cart for this vendor
                $del = $conn->prepare('DELETE FROM cart_items WHERE vendor_id = ?');
                $del->execute([$vendor_id]);

                $conn->commit();
            } catch (Throwable $e) {
                $conn->rollBack();
                throw $e;
            }

            json_response(['status' => 'success', 'message' => 'Order created', 'data' => ['order_id' => $order_id]]);
        }

        case 'PUT': {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_response(['status' => 'error', 'message' => 'Order id is required'], 400);
            $input = get_json_input();
            $status = isset($input['status']) ? trim($input['status']) : '';
            $allowed = ['pending','processing','shipped','delivered','cancelled'];
            if ($status === '' || !in_array($status, $allowed, true)) {
                json_response(['status' => 'error', 'message' => 'Invalid status'], 400);
            }
            $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ? AND vendor_id = ?');
            $stmt->execute([$status, $id, $vendor_id]);
            if ($stmt->rowCount() === 0) {
                json_response(['status' => 'error', 'message' => 'Order not found or unchanged'], 404);
            }
            json_response(['status' => 'success', 'message' => 'Order status updated']);
        }

        case 'DELETE': {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) json_response(['status' => 'error', 'message' => 'Order id is required'], 400);
            $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ? AND vendor_id = ?');
            $stmt->execute(['cancelled', $id, $vendor_id]);
            if ($stmt->rowCount() === 0) {
                json_response(['status' => 'error', 'message' => 'Order not found or unchanged'], 404);
            }
            json_response(['status' => 'success', 'message' => 'Order cancelled']);
        }

        default: {
            header('Allow: GET, POST, PUT, DELETE');
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }
} catch (Throwable $e) {
    error_log('Delivery API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()], 500);
}
