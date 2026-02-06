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

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    // Ensure table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS asset_maintenance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        asset_id INT NOT NULL,
        item_number VARCHAR(100) NOT NULL,
        maintenance_type ENUM('Preventive', 'Corrective', 'Emergency') DEFAULT 'Preventive',
        description TEXT NOT NULL,
        scheduled_date DATE,
        completed_date DATE,
        technician_name VARCHAR(255),
        status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
        cost DECIMAL(12, 2),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT,
        INDEX idx_asset_id (asset_id),
        INDEX idx_item_number (item_number),
        INDEX idx_status (status),
        INDEX idx_scheduled_date (scheduled_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            if ($action === 'all') {
                // Get all maintenance records
                $sql = "SELECT * FROM asset_maintenance ORDER BY scheduled_date DESC";
                $stmt = $conn->query($sql);
                json_response(['status' => 'success', 'records' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                
            } elseif ($action === 'by_asset') {
                // Get maintenance records for specific asset
                $asset_id = $_GET['asset_id'] ?? 0;
                $sql = "SELECT * FROM asset_maintenance WHERE asset_id = ? ORDER BY created_at DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$asset_id]);
                json_response(['status' => 'success', 'records' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                
            } elseif ($action === 'pending') {
                // Get pending maintenance
                $sql = "SELECT * FROM asset_maintenance WHERE status IN ('Pending', 'In Progress') ORDER BY scheduled_date ASC";
                $stmt = $conn->query($sql);
                json_response(['status' => 'success', 'records' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                
            } else {
                json_response(['status' => 'error', 'message' => 'Invalid action'], 400);
            }
            break;

        case 'POST':
            $asset_id = $input['asset_id'] ?? 0;
            $item_number = $input['item_number'] ?? '';
            $maintenance_type = $input['maintenance_type'] ?? 'Preventive';
            $description = $input['description'] ?? '';
            $scheduled_date = $input['scheduled_date'] ?? null;
            $technician_name = $input['technician_name'] ?? '';
            $cost = floatval($input['cost'] ?? 0);
            $notes = $input['notes'] ?? '';

            if ($asset_id <= 0 || empty($description)) {
                json_response(['status' => 'error', 'message' => 'Asset ID and description are required'], 400);
            }

            $sql = "INSERT INTO asset_maintenance (asset_id, item_number, maintenance_type, description, scheduled_date, technician_name, cost, notes, created_by, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$asset_id, $item_number, $maintenance_type, $description, $scheduled_date, $technician_name, $cost, $notes, $_SESSION['id']]);

            json_response(['status' => 'success', 'message' => 'Maintenance record created', 'id' => $conn->lastInsertId()]);
            break;

        case 'PUT':
            $id = $input['id'] ?? 0;
            $status = $input['status'] ?? '';
            $completed_date = $input['completed_date'] ?? null;
            $technician_name = $input['technician_name'] ?? '';
            $notes = $input['notes'] ?? '';
            $cost = isset($input['cost']) ? floatval($input['cost']) : null;

            if ($id <= 0) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            $sql = "UPDATE asset_maintenance SET status = ?, completed_date = ?, technician_name = ?, notes = ?";
            $params = [$status, $completed_date, $technician_name, $notes];
            
            if ($cost !== null) {
                $sql .= ", cost = ?";
                $params[] = $cost;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response(['status' => 'success', 'message' => 'Maintenance record updated']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? 0;

            if ($id <= 0) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            $sql = "DELETE FROM asset_maintenance WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);

            json_response(['status' => 'success', 'message' => 'Maintenance record deleted']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }

} catch (PDOException $e) {
    error_log('Maintenance API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Database error'], 500);
} catch (Exception $e) {
    error_log('Maintenance API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Server error'], 500);
}
?>
