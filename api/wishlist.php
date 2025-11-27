<?php
// Wishlist API (RESTful)
// Methods:
//  GET:    List items or return count (?count=1)
//  POST:   Add item { product_id }
//  DELETE: Remove item (?product_id=ID) or clear all (no product_id)

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

function ensure_wishlist_table(PDO $conn) {
    $sql = "CREATE TABLE IF NOT EXISTS wishlist_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        vendor_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    ensure_wishlist_table($conn);

    switch ($method) {
        case 'GET': {
            $countOnly = isset($_GET['count']) && $_GET['count'] == '1';
            if ($countOnly) {
                $stmt = $conn->prepare('SELECT COUNT(*) as total FROM wishlist_items WHERE vendor_id = ?');
                $stmt->execute([$vendor_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0];
                json_response(['status' => 'success', 'data' => $row]);
            }
            $stmt = $conn->prepare('SELECT id, product_id, created_at FROM wishlist_items WHERE vendor_id = ? ORDER BY created_at DESC');
            $stmt->execute([$vendor_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Remove wishlist entries that reference deleted/non-existent products
            if (!empty($items)) {
                $productIds = array_values(array_unique(array_map(function($it){ return (int)$it['product_id']; }, $items)));
                if (!empty($productIds)) {
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
                            // product no longer exists, remove this wishlist entry
                            $del = $conn->prepare('DELETE FROM wishlist_items WHERE id = ?');
                            try { $del->execute([$it['id']]); } catch (Throwable $e) { /* ignore */ }
                        }
                    }
                    $items = $filtered;
                }
            }

            json_response(['status' => 'success', 'data' => $items]);
        }

        case 'POST': {
            $input = $_POST;
            if (empty($input)) $input = get_json_input();

            $product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
            if ($product_id <= 0) {
                json_response(['status' => 'error', 'message' => 'product_id is required'], 400);
            }

            try {
                $stmt = $conn->prepare('INSERT IGNORE INTO wishlist_items (vendor_id, product_id) VALUES (?, ?)');
                $stmt->execute([$vendor_id, $product_id]);
            } catch (Throwable $e) {
                json_response(['status' => 'error', 'message' => 'Failed to add to wishlist'], 500);
            }

            json_response(['status' => 'success', 'message' => 'Item added to wishlist']);
        }

        case 'DELETE': {
            $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
            if ($product_id > 0) {
                $stmt = $conn->prepare('DELETE FROM wishlist_items WHERE vendor_id = ? AND product_id = ?');
                $stmt->execute([$vendor_id, $product_id]);
                json_response(['status' => 'success', 'message' => 'Item removed from wishlist']);
            }
            $stmt = $conn->prepare('DELETE FROM wishlist_items WHERE vendor_id = ?');
            $stmt->execute([$vendor_id]);
            json_response(['status' => 'success', 'message' => 'Wishlist cleared']);
        }

        default: {
            header('Allow: GET, POST, DELETE');
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }
    }
} catch (Throwable $e) {
    error_log('Wishlist API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()], 500);
}
