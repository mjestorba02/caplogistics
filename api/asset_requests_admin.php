<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_helpers.php';

if (!$conn) {
    json_response(['status' => 'error', 'message' => 'Database connection failed'], 500);
}

// Require authentication
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Require admin access for this endpoint
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Admin access required. Only administrators can approve/deny requests.'
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$user_id = $_SESSION['id'];
$user_name = $_SESSION['name'] ?? 'Unknown';

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    switch ($method) {
        // ============================================================================
        // GET - RETRIEVE REQUESTS FOR ADMIN
        // ============================================================================
        case 'GET':
            $action = $_GET['action'] ?? '';

            if ($action === 'all') {
                // Get all requests filtered by status
                $status = $_GET['status'] ?? 'Pending Approval';

                $sql = "SELECT * FROM asset_requests 
                        WHERE status = :status 
                        ORDER BY request_date DESC";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute(['status' => $status]);
                $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                json_response(['status' => 'success', 'requests' => $requests]);

            } elseif ($action === 'details') {
                // Get request details with items
                $id = $_GET['id'] ?? null;

                if (!$id) {
                    json_response(['status' => 'error', 'message' => 'ID required'], 400);
                }

                $sql = "SELECT * FROM asset_requests WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['id' => $id]);
                $request = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$request) {
                    json_response(['status' => 'error', 'message' => 'Request not found'], 404);
                }

                $sql = "SELECT * FROM asset_request_items WHERE asset_request_id = :id ORDER BY item_sequence ASC";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['id' => $id]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                json_response([
                    'status' => 'success',
                    'request' => $request,
                    'items' => $items
                ]);

            } else {
                json_response(['status' => 'error', 'message' => 'Invalid action'], 400);
            }
            break;

        // ============================================================================
        // POST - UPDATE REQUEST STATUS
        // ============================================================================
        case 'POST':
            $action = $input['action'] ?? '';
            $request_id = $input['id'] ?? null;

            if (!$request_id) {
                json_response(['status' => 'error', 'message' => 'Request ID required'], 400);
            }

            // Get current request
            $sql = "SELECT * FROM asset_requests WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $request_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                json_response(['status' => 'error', 'message' => 'Request not found'], 404);
            }

            if ($action === 'approve') {
                if ($request['status'] !== 'Pending Approval') {
                    json_response(['status' => 'error', 'message' => 'Only pending requests can be approved'], 400);
                }

                $notes = $input['notes'] ?? '';

                // Update status
                $sql = "UPDATE asset_requests SET status = 'Approved', approved_by = :approved_by, approved_date = NOW() WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['approved_by' => $user_name, 'id' => $request_id]);

                // Log audit
                logAudit($request_id, 'APPROVED', $user_name, 'Pending Approval', 'Approved', $notes);

                json_response(['status' => 'success', 'message' => 'Request approved']);

            } elseif ($action === 'reject') {
                if ($request['status'] !== 'Pending Approval') {
                    json_response(['status' => 'error', 'message' => 'Only pending requests can be rejected'], 400);
                }

                $reason = $input['reason'] ?? '';

                if (!$reason) {
                    json_response(['status' => 'error', 'message' => 'Rejection reason required'], 400);
                }

                // Update status
                $sql = "UPDATE asset_requests SET status = 'Rejected', rejected_by = :rejected_by, rejected_date = NOW(), rejection_reason = :reason WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['rejected_by' => $user_name, 'reason' => $reason, 'id' => $request_id]);

                // Log audit
                logAudit($request_id, 'REJECTED', $user_name, 'Pending Approval', 'Rejected', $reason);

                json_response(['status' => 'success', 'message' => 'Request rejected']);

            } else {
                json_response(['status' => 'error', 'message' => 'Invalid action'], 400);
            }
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }

} catch (PDOException $e) {
    error_log('Asset Request Admin API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Database error'], 500);
} catch (Exception $e) {
    error_log('Asset Request Admin API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Server error'], 500);
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function logAudit($request_id, $action, $action_by, $old_value = null, $new_value = null, $notes = null) {
    global $conn;

    $sql = "INSERT INTO asset_request_audit_log 
            (asset_request_id, action, action_by, old_value, new_value) 
            VALUES (:request_id, :action, :action_by, :old_value, :new_value)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'request_id' => $request_id,
        'action' => $action,
        'action_by' => $action_by,
        'old_value' => $old_value,
        'new_value' => $new_value
    ]);
}
?>
