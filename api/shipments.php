<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
include 'db.php'; // Your PDO connection

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

// distinguish between logged-in user and vendor
$currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$currentVendorId = isset($_SESSION['vendor_id']) ? intval($_SESSION['vendor_id']) : null;

switch ($method) {

    // GET all shipments or single shipment
    case "GET":
        if(isset($_GET['id'])){
            $stmt = $conn->prepare("SELECT * FROM shipments WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
            if($shipment){
                echo json_encode($shipment);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Shipment not found"]);
            }
        } else {
            // Build query with filters
            $where = [];
            $params = [];
            
            // Status filter
            if(isset($_GET['status']) && $_GET['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $_GET['status'];
            }
            
            // Archive filter
            if(isset($_GET['archived'])) {
                if($_GET['archived'] === '1') {
                    $where[] = "status = 'Archived'";
                } elseif($_GET['archived'] === '0') {
                    $where[] = "status != 'Archived'";
                }
            }
            // If a regular user is logged in, show only their shipments (their order history)
            if ($currentUserId !== null && !isset($_GET['all_users'])) {
                $where[] = "user_id = ?";
                $params[] = $currentUserId;
            }
            
            $sql = "SELECT * FROM shipments";
            if(!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // attach invoice info for each shipment where available
            foreach ($shipments as &$r) {
                try {
                    $s = $conn->prepare('SELECT id AS invoice_id, invoice_number, subtotal, status FROM invoices WHERE shipment_id = ? LIMIT 1');
                    $s->execute([$r['id']]);
                    $inv = $s->fetch(PDO::FETCH_ASSOC);
                    $r['invoice'] = $inv ? $inv : null;
                } catch (Exception $e) {
                    $r['invoice'] = null;
                }
            }

            echo json_encode($shipments);
        }
        break;

    // CREATE shipment
    case "POST":
        // ensure table has user_id and payment_method columns (best-effort)
        try { $conn->exec("ALTER TABLE shipments ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL"); } catch (Exception $e) { /* ignore */ }
        try { $conn->exec("ALTER TABLE shipments ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'cod'"); } catch (Exception $e) { /* ignore */ }

        $stmt = $conn->prepare("INSERT INTO shipments (shipment_number, origin, destination, items_quantity, dispatch_date, status, notes, user_id, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // We'll attempt shipment insert and invoice creation inside a transaction to stay atomic where possible
        try {
            $conn->beginTransaction();
            $ok = $stmt->execute([
                $data['shipment_number'],
                $data['origin'],
                $data['destination'],
                $data['items_quantity'],
                $data['dispatch_date'],
                $data['status'] ?? 'Pending',
                $data['notes'] ?? '',
                // prefer explicit user_id from payload (admin creating on behalf), otherwise current session user id
                $data['user_id'] ?? $currentUserId,
                // payment method (default to cod)
                $data['payment_method'] ?? 'cod'
            ]);

            if (!$ok) {
                $conn->rollBack();
                http_response_code(400);
                echo json_encode(["error" => "Failed to add shipment"]);
                break;
            }

            $newId = $conn->lastInsertId();
            $invoiceId = null; $invoiceNumber = null;

            // check if an invoice already exists for this shipment (avoid duplicates)
            try {
                $chk = $conn->prepare('SELECT id, invoice_number FROM invoices WHERE shipment_id = ? LIMIT 1');
                $chk->execute([$newId]);
                $existingInv = $chk->fetch(PDO::FETCH_ASSOC);
                if ($existingInv) {
                    $invoiceId = $existingInv['id'];
                    $invoiceNumber = $existingInv['invoice_number'];
                } else {
                    // create invoice (best-effort) - get user details and store cart items
                    $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($newId, 6, '0', STR_PAD_LEFT);
                    $userForInvoice = isset($data['user_id']) ? $data['user_id'] : $currentUserId;
                    
                    // Get user name - try users table first, then user_vendor
                    $userName = '';
                    try {
                        $userStmt = $conn->prepare('SELECT name FROM users WHERE id = ?');
                        $userStmt->execute([$userForInvoice]);
                        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                        if ($userData) {
                            $userName = $userData['name'];
                        } else {
                            // Try user_vendor table
                            $vendorStmt = $conn->prepare('SELECT CONCAT(first_name, " ", last_name) as full_name FROM user_vendor WHERE id = ?');
                            $vendorStmt->execute([$userForInvoice]);
                            $vendorData = $vendorStmt->fetch(PDO::FETCH_ASSOC);
                            $userName = $vendorData ? $vendorData['full_name'] : '';
                        }
                    } catch (Exception $e) { /* ignore */ }
                    
                    // Get cart items and calculate subtotal
                    $cartItems = [];
                    $subtotal = 0;
                    try {
                        // Check if this is a vendor checkout (cart_items) or user checkout (cart)
                        $isVendor = isset($_SESSION['vendor_id']) && $_SESSION['vendor_id'] == $userForInvoice;
                        $cartTable = $isVendor ? 'cart_items' : 'cart';
                        $idColumn = $isVendor ? 'vendor_id' : 'user_id';
                        $productTable = 'inventory_items'; // Use inventory_items instead of products
                        $nameColumn = 'item_name'; // inventory_items uses item_name
                        
                        $cartStmt = $conn->prepare("
                            SELECT c.quantity, p.{$nameColumn} as name, p.price, (c.quantity * p.price) as total
                            FROM {$cartTable} c 
                            JOIN {$productTable} p ON c.product_id = p.id 
                            WHERE c.{$idColumn} = ?
                        ");
                        $cartStmt->execute([$userForInvoice]);
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
                    
                    $stmtInv = $conn->prepare('INSERT INTO invoices (invoice_number, shipment_id, user_id, user_name, delivery_from, delivery_to, items, date, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmtInv->execute([
                        $invoiceNumber,
                        $newId,
                        $userForInvoice,
                        $userName,
                        $data['origin'] ?? 'Warehouse A',
                        $data['destination'] ?? '',
                        json_encode($cartItems),
                        date('Y-m-d'),
                        $subtotal,
                        'Auto-generated from shipment'
                    ]);
                    $invoiceId = $conn->lastInsertId();
                }
            } catch (Exception $e) {
                // invoice creation failed; continue but keep invoice fields null
                $invoiceId = null; $invoiceNumber = null;
            }

            $conn->commit();
            http_response_code(201);
            echo json_encode(["message" => "Shipment added successfully", "id" => $newId, "invoice_id" => $invoiceId, "invoice_number" => $invoiceNumber]);
        } catch (Exception $e) {
            try { $conn->rollBack(); } catch (Exception $ee) { /* ignore */ }
            http_response_code(500);
            echo json_encode(["error" => "Failed to add shipment", "detail" => $e->getMessage()]);
        }
        break;

    // UPDATE shipment
    case "PUT":
        if(!isset($_GET['id'])){
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }
        $id = intval($_GET['id']);
        // allow updating payment_method as well if provided
        $stmt = $conn->prepare("UPDATE shipments SET shipment_number=?, origin=?, destination=?, items_quantity=?, dispatch_date=?, status=?, notes=?, payment_method=? WHERE id=?");
        if($stmt->execute([
            $data['shipment_number'],
            $data['origin'],
            $data['destination'],
            $data['items_quantity'],
            $data['dispatch_date'],
            $data['status'] ?? 'Pending',
            $data['notes'] ?? '',
            $data['payment_method'] ?? 'cod',
            $id
        ])){
            // propagate status to any deliveries referencing this shipment
            try {
                if (!empty($data['status'])) {
                    $propStatus = $data['status'];
                    // shipment Delivered -> delivery Complete
                    $deliveryStatus = $propStatus === 'Delivered' ? 'Complete' : ($propStatus === 'Cancelled' ? 'Cancelled' : $propStatus);
                    $stmt2 = $conn->prepare("UPDATE deliveries SET status = ? WHERE shipment_id = ?");
                    $stmt2->execute([$deliveryStatus, $id]);
                }
            } catch (Exception $e) { /* ignore propagation errors */ }

            echo json_encode(["message" => "Shipment updated successfully"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Failed to update shipment"]);
        }
        break;

    // DELETE shipment
    case "DELETE":
        if(!isset($_GET['id'])){
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("DELETE FROM shipments WHERE id=?");
        if($stmt->execute([$id])){
            echo json_encode(["message" => "Shipment deleted successfully"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Failed to delete shipment"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>