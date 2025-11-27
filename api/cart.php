<?php


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

function ensure_cart_table(PDO $conn) {
    $sql = "CREATE TABLE IF NOT EXISTS cart_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        quantity INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_vendor_product (vendor_id, product_id),
        INDEX idx_vendor (vendor_id),
        INDEX idx_product (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->exec($sql);
}

function get_json_input(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

try {
    ensure_cart_table($conn);

    switch ($method) {
        case 'GET': {
            $countOnly = isset($_GET['count']) && $_GET['count'] == '1';
            if ($countOnly) {
                $stmt = $conn->prepare('SELECT COALESCE(SUM(quantity),0) as total_quantity, COUNT(*) as distinct_items FROM cart_items WHERE vendor_id = ?');
                $stmt->execute([$vendor_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_quantity' => 0, 'distinct_items' => 0];
                json_response(['status' => 'success', 'data' => $row]);
            }
            // list items
            $stmt = $conn->prepare('SELECT id, product_id, quantity, created_at, updated_at FROM cart_items WHERE vendor_id = ? ORDER BY created_at DESC');
            $stmt->execute([$vendor_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Remove cart entries that reference deleted/non-existent products
            if (!empty($items)) {
                $productIds = array_values(array_unique(array_map(function($it){ return (int)$it['product_id']; }, $items)));
                if (!empty($productIds)) {
                    // build placeholders
                    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                    $checkStmt = $conn->prepare("SELECT id FROM inventory_items WHERE id IN ($placeholders)");
                    $checkStmt->execute($productIds);
                    $existing = $checkStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                    $existingMap = array_flip($existing ?: []);

                    $filtered = [];
                    foreach ($items as $it) {
                        if (isset($existingMap[(int)$it['product_id']])) {
                            $filtered[] = $it;
                        } else {
                            // product no longer exists, remove this cart entry
                            $del = $conn->prepare('DELETE FROM cart_items WHERE id = ?');
                            try { $del->execute([$it['id']]); } catch (Throwable $e) { /* ignore */ }
                        }
                    }
                    $items = $filtered;
                }
            }

            json_response(['status' => 'success', 'data' => $items]);
        }

        case 'POST': {
            // Accept JSON or form-data
            $input = $_POST;
            if (empty($input)) $input = get_json_input();

            $product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
            $quantity   = isset($input['quantity']) ? (int)$input['quantity'] : 1;
            if ($product_id <= 0 || $quantity <= 0) {
                json_response(['status' => 'error', 'message' => 'product_id and positive quantity are required'], 400);
            }

            // Validate product exists in inventory
            $prodCheck = $conn->prepare('SELECT id FROM inventory_items WHERE id = ?');
            $prodCheck->execute([$product_id]);
            if (!$prodCheck->fetch(PDO::FETCH_ASSOC)) {
                json_response(['status' => 'error', 'message' => 'Product not found in inventory'], 404);
            }

            // Upsert: increment if exists
            $conn->beginTransaction();
            try {
                $stmt = $conn->prepare('SELECT id, quantity FROM cart_items WHERE vendor_id = ? AND product_id = ? FOR UPDATE');
                $stmt->execute([$vendor_id, $product_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $newQty = (int)$row['quantity'] + $quantity;
                    $upd = $conn->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
                    $upd->execute([$newQty, $row['id']]);
                } else {
                    $ins = $conn->prepare('INSERT INTO cart_items (vendor_id, product_id, quantity) VALUES (?, ?, ?)');
                    $ins->execute([$vendor_id, $product_id, $quantity]);
                }
                $conn->commit();
            } catch (Throwable $e) {
                $conn->rollBack();
                throw $e;
            }

            json_response(['status' => 'success', 'message' => 'Item added to cart']);
        }

        case 'PUT': {
            $input = get_json_input();
            $product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
            $quantity   = isset($input['quantity']) ? (int)$input['quantity'] : -1;
            if ($product_id <= 0 || $quantity < 0) {
                json_response(['status' => 'error', 'message' => 'product_id and non-negative quantity are required'], 400);
            }

            // Validate product exists in inventory
            $prodCheck = $conn->prepare('SELECT id FROM inventory_items WHERE id = ?');
            $prodCheck->execute([$product_id]);
            if (!$prodCheck->fetch(PDO::FETCH_ASSOC)) {
                json_response(['status' => 'error', 'message' => 'Product not found in inventory'], 404);
            }

            if ($quantity === 0) {
                $stmt = $conn->prepare('DELETE FROM cart_items WHERE vendor_id = ? AND product_id = ?');
                $stmt->execute([$vendor_id, $product_id]);
                json_response(['status' => 'success', 'message' => 'Item removed']);
            }
            $stmt = $conn->prepare('UPDATE cart_items SET quantity = ? WHERE vendor_id = ? AND product_id = ?');
            $stmt->execute([$quantity, $vendor_id, $product_id]);
            if ($stmt->rowCount() === 0) {
                json_response(['status' => 'error', 'message' => 'Item not in cart'], 404);
            }
            json_response(['status' => 'success', 'message' => 'Quantity updated']);
        }

        case 'DELETE': {
            $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
            if ($product_id > 0) {
                $stmt = $conn->prepare('DELETE FROM cart_items WHERE vendor_id = ? AND product_id = ?');
                $stmt->execute([$vendor_id, $product_id]);
                json_response(['status' => 'success', 'message' => 'Item removed']);
            }
            // Clear all
            $stmt = $conn->prepare('DELETE FROM cart_items WHERE vendor_id = ?');
            $stmt->execute([$vendor_id]);
            json_response(['status' => 'success', 'message' => 'Cart cleared']);
        }

        default: {
            header('Allow: GET, POST, PUT, DELETE');
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }
} catch (Throwable $e) {
    error_log('Cart API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()], 500);
}
