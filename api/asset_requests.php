<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!$conn) {
    json_response(['status' => 'error', 'message' => 'Database connection failed'], 500);
}

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
    // Ensure tables exist
    createTablesIfNotExist();

    switch ($method) {
        // ============================================================================
        // GET REQUESTS
        // ============================================================================
        case 'GET':
            $action = $_GET['action'] ?? '';

            if ($action === 'my_requests') {
                // Get current user's requests
                $sql = "SELECT * FROM asset_requests 
                        WHERE requester_id = :user_id 
                        ORDER BY request_date DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['user_id' => $user_id]);
                $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                json_response(['status' => 'success', 'requests' => $requests]);

            } elseif ($action === 'status_summary') {
                // Get status counts and recent requests
                $sql = "SELECT 
                        (SELECT COUNT(*) FROM asset_requests WHERE requester_id = :user_id AND status = 'Pending Approval') as pending_count,
                        (SELECT COUNT(*) FROM asset_requests WHERE requester_id = :user_id AND status = 'Approved') as approved_count,
                        (SELECT COUNT(*) FROM asset_requests WHERE requester_id = :user_id AND status = 'In Process') as in_process_count,
                        (SELECT COUNT(*) FROM asset_requests WHERE requester_id = :user_id AND status = 'Rejected') as rejected_count";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute(['user_id' => $user_id]);
                $counts = $stmt->fetch(PDO::FETCH_ASSOC);

                // Get recent requests
                $sql = "SELECT * FROM asset_requests 
                        WHERE requester_id = :user_id 
                        ORDER BY request_date DESC 
                        LIMIT 5";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['user_id' => $user_id]);
                $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

                json_response([
                    'status' => 'success',
                    'pending_count' => $counts['pending_count'] ?? 0,
                    'approved_count' => $counts['approved_count'] ?? 0,
                    'in_process_count' => $counts['in_process_count'] ?? 0,
                    'rejected_count' => $counts['rejected_count'] ?? 0,
                    'recent_requests' => $recent
                ]);

            } else {
                json_response(['status' => 'error', 'message' => 'Invalid action'], 400);
            }
            break;

        // ============================================================================
        // POST - CREATE REQUEST
        // ============================================================================
        case 'POST':
            $priority = $input['priority'] ?? 'Medium';
            $department = $input['department'] ?? '';
            $notes = $input['notes'] ?? '';
            $items = $input['items'] ?? [];

            if (empty($items)) {
                json_response(['status' => 'error', 'message' => 'At least one item is required'], 400);
            }

            // Generate request ID
            $sql = "SELECT COALESCE(MAX(CAST(SUBSTRING(request_id, 4) AS UNSIGNED)), 0) + 1 as next_id 
                    FROM asset_requests";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $request_id = 'AR-' . str_pad($result['next_id'], 3, '0', STR_PAD_LEFT);

            // Insert main request
            $sql = "INSERT INTO asset_requests 
                    (request_id, requester_id, requester_name, requester_department, status, priority, total_items, notes) 
                    VALUES (:request_id, :requester_id, :requester_name, :department, 'Pending Approval', :priority, :total_items, :notes)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'request_id' => $request_id,
                'requester_id' => $user_id,
                'requester_name' => $user_name,
                'department' => $department,
                'priority' => $priority,
                'total_items' => count($items),
                'notes' => $notes
            ]);

            $request_key = $conn->lastInsertId();

            // Insert items
            foreach ($items as $index => $item) {
                $sql = "INSERT INTO asset_request_items 
                        (asset_request_id, item_sequence, asset_description, quantity, department, urgency, estimated_cost, notes) 
                        VALUES (:request_id, :sequence, :description, :quantity, :department, :urgency, :cost, :notes)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'request_id' => $request_key,
                    'sequence' => $index + 1,
                    'description' => $item['asset_description'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'department' => $department,
                    'urgency' => $item['urgency'] ?? 'Medium',
                    'cost' => $item['estimated_cost'] ?? 0,
                    'notes' => $item['notes'] ?? ''
                ]);
            }

            // Log audit
            logAudit($request_key, 'CREATED', $user_name, null, 'Request created');

            json_response([
                'status' => 'success',
                'message' => 'Request submitted successfully',
                'request_id' => $request_id
            ]);
            break;

        // ============================================================================
        // DELETE REQUEST
        // ============================================================================
        case 'DELETE':
            $request_id = $input['id'] ?? null;

            if (!$request_id) {
                json_response(['status' => 'error', 'message' => 'Request ID required'], 400);
            }

            // Check if user owns this request
            $sql = "SELECT id, status FROM asset_requests WHERE id = :id AND requester_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $request_id, 'user_id' => $user_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                json_response(['status' => 'error', 'message' => 'Request not found or unauthorized'], 403);
            }

            if ($request['status'] !== 'Pending Approval') {
                json_response(['status' => 'error', 'message' => 'Can only delete pending requests'], 400);
            }

            // Delete request and items (cascade handled by FK)
            $sql = "DELETE FROM asset_requests WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $request_id]);

            json_response(['status' => 'success', 'message' => 'Request deleted']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }

} catch (PDOException $e) {
    error_log('Asset Request API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Database error'], 500);
} catch (Exception $e) {
    error_log('Asset Request API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Server error'], 500);
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function createTablesIfNotExist() {
    global $conn;

    // These tables should already exist from REQUEST_ASSET_MODULE.sql
    // But ensure they exist
    $tables = [
        'asset_requests',
        'asset_request_items',
        'asset_request_to_procurement',
        'asset_request_audit_log'
    ];

    foreach ($tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            throw new Exception("Table $table does not exist. Please run REQUEST_ASSET_MODULE.sql");
        }
    }
}

function logAudit($request_id, $action, $action_by, $old_value = null, $new_value = null) {
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
