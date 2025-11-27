<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
include 'db.php'; // PDO connection

// Auth guard - require either vendor or user session
if (!isset($_SESSION['vendor_id']) && !isset($_SESSION['user_id']) && !isset($_SESSION['id']) && !isset($_SESSION['customer_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

// Determine user vs vendor from session. Support multiple session key names used across the app.
// e.g. some pages set $_SESSION['user_id'] while others set $_SESSION['id'] for the logged-in user.
$currentUserId = null;
if (isset($_SESSION['user_id'])) $currentUserId = intval($_SESSION['user_id']);
elseif (isset($_SESSION['id'])) $currentUserId = intval($_SESSION['id']);
elseif (isset($_SESSION['customer_id'])) $currentUserId = intval($_SESSION['customer_id']);
$currentVendorId = isset($_SESSION['vendor_id']) ? intval($_SESSION['vendor_id']) : null;

// If vendor session is present, do not treat the generic `id`/`user_id` session keys as a buyer id.
// Some login flows set `id` for vendors; avoid accidental filtering by vendor id.
if ($currentVendorId !== null) {
    $currentUserId = null;
}


switch ($method) {
    case 'GET':
        // We return a compact projection joined with shipments.payment_method when available.
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT d.id, d.delivery_number, d.shipment_id, d.origin, d.destination, d.items_quantity, d.delivery_date, d.status, d.notes, d.created_at, d.user_id, COALESCE(s.payment_method, 'cod') AS payment_method FROM deliveries d LEFT JOIN shipments s ON d.shipment_id = s.id WHERE d.id = ?");
            $stmt->execute([$_GET['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) echo json_encode($row);
            else { http_response_code(404); echo json_encode(["error"=>"Delivery not found"]); }
        } else {
            $where = [];
            $params = [];
            // Filter by status if provided
            if (isset($_GET['status']) && $_GET['status'] !== 'all') {
                $where[] = 'd.status = ?'; $params[] = $_GET['status'];
            }
                // User scoping logic
                // If a user_id is explicitly provided, validate it before honoring.
                // Allow when:
                // - requester is the same user (self-view), OR
                // - requester is a logged-in vendor (they may view a customer's deliveries), OR
                // - requester is an admin (session key 'is_admin' truthy)
                if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
                    $requestedUid = intval($_GET['user_id']);
                    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
                    if ($currentUserId !== null && $currentUserId === $requestedUid) {
                        $where[] = 'd.user_id = ?'; $params[] = $requestedUid;
                    } elseif ($currentVendorId !== null) {
                        $where[] = 'd.user_id = ?'; $params[] = $requestedUid;
                    } elseif ($isAdmin) {
                        $where[] = 'd.user_id = ?'; $params[] = $requestedUid;
                    } else {
                        // ignore malicious/unauthorized user_id parameter
                    }
                } else {
                    // If no explicit user_id was requested, enforce session scoping:
                    if ($currentUserId !== null && !isset($_GET['all_users'])) {
                        // normal users always only see their own deliveries
                        $where[] = 'd.user_id = ?'; $params[] = $currentUserId;
                    } elseif ($currentVendorId !== null) {
                        // vendor: fetch deliveries where the buyer user_id equals the vendor_id
                        // (per request: treat vendor_id as the user_id to filter by)
                        $where[] = 'd.user_id = ?'; $params[] = $currentVendorId;
                    } else {
                        // No session and no explicit allowed user filter -> block to prevent leaking records
                        http_response_code(401);
                        echo json_encode([]);
                        exit;
                    }
                }

            $sql = 'SELECT d.id, d.delivery_number, d.shipment_id, d.origin, d.destination, d.items_quantity, d.delivery_date, d.status, d.notes, d.created_at, d.user_id, COALESCE(s.payment_method, \'cod\') AS payment_method FROM deliveries d LEFT JOIN shipments s ON d.shipment_id = s.id';
            if (!empty($where)) $sql .= ' WHERE ' . implode(' AND ', $where);
            $sql .= ' ORDER BY d.created_at DESC';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rows);
        }
        break;

    case 'POST':
        $stmt = $conn->prepare("INSERT INTO deliveries (delivery_number, shipment_id, origin, destination, items_quantity, delivery_date, status, notes, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([
            $data['delivery_number'] ?? null,
            $data['shipment_id'] ?? null,
            $data['origin'] ?? null,
            $data['destination'] ?? null,
            $data['items_quantity'] ?? 0,
            $data['delivery_date'] ?? null,
            $data['status'] ?? 'Pending',
            $data['notes'] ?? null,
            // prefer explicit user_id from payload (admin-created), otherwise use session user id
            $data['user_id'] ?? $currentUserId
        ])) {
            http_response_code(201);
            echo json_encode(["message"=>"Delivery created","id"=>$conn->lastInsertId()]);
        } else {
            http_response_code(400);
            echo json_encode(["error"=>"Failed to create delivery"]);
        }
        break;

    case 'PUT':
        if (!isset($_GET['id'])) { http_response_code(400); echo json_encode(["error"=>"ID required"]); exit; }
        $id = intval($_GET['id']);

        // fetch existing delivery to determine current shipment_id (if any)
        $existingShipmentId = null;
        try {
            $sFetch = $conn->prepare("SELECT * FROM deliveries WHERE id = ?");
            $sFetch->execute([$id]);
            $existing = $sFetch->fetch(PDO::FETCH_ASSOC);
            if ($existing) $existingShipmentId = $existing['shipment_id'];
        } catch (Exception $e) {
            // ignore fetch errors, we'll attempt the update anyway
        }

        $stmt = $conn->prepare("UPDATE deliveries SET delivery_number=?, shipment_id=?, origin=?, destination=?, items_quantity=?, delivery_date=?, status=?, notes=? WHERE id=?");
        if ($stmt->execute([
            $data['delivery_number'] ?? null,
            $data['shipment_id'] ?? $existingShipmentId,
            $data['origin'] ?? null,
            $data['destination'] ?? null,
            $data['items_quantity'] ?? 0,
            $data['delivery_date'] ?? null,
            $data['status'] ?? 'Pending',
            $data['notes'] ?? null,
            $id
        ])) {
            // propagate status to the linked shipment (use new shipment_id if provided, otherwise existing)
            try {
                $targetShipmentId = isset($data['shipment_id']) ? $data['shipment_id'] : $existingShipmentId;
                if (!empty($data['status']) && !empty($targetShipmentId)) {
                    $shipStatus = $data['status'] === 'Complete' ? 'Delivered' : ($data['status'] === 'Cancelled' ? 'Cancelled' : $data['status']);
                    $s2 = $conn->prepare("UPDATE shipments SET status = ? WHERE id = ?");
                    $s2->execute([$shipStatus, $targetShipmentId]);
                }
            } catch (Exception $e) { /* ignore propagation errors */ }

            echo json_encode(["message"=>"Delivery updated"]);
        } else {
            http_response_code(400);
            echo json_encode(["error"=>"Failed to update delivery"]);
        }
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) { http_response_code(400); echo json_encode(["error"=>"ID required"]); exit; }
        $id = intval($_GET['id']);

        // fetch the delivery first to know if it referenced a shipment
        $shipmentToCancel = null;
        try {
            $sFetch = $conn->prepare("SELECT shipment_id FROM deliveries WHERE id = ?");
            $sFetch->execute([$id]);
            $row = $sFetch->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['shipment_id'])) $shipmentToCancel = $row['shipment_id'];
        } catch (Exception $e) { /* ignore */ }

        $stmt = $conn->prepare("DELETE FROM deliveries WHERE id=?");
        if ($stmt->execute([$id])) {
            // also mark shipment cancelled if delivery referenced a shipment
            try {
                if (!empty($shipmentToCancel)) {
                    $upd = $conn->prepare("UPDATE shipments SET status = ? WHERE id = ?");
                    $upd->execute(['Cancelled', $shipmentToCancel]);
                }
            } catch (Exception $e) { /* ignore */ }

            echo json_encode(["message"=>"Delivery deleted"]);
        } else {
            http_response_code(400);
            echo json_encode(["error"=>"Failed to delete delivery"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error"=>"Method not allowed"]);
}

?>