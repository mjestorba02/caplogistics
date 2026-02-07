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
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            if ($action === 'all') {
                // Get all maintenance records
                $sql = "SELECT * FROM asset_maintenance ORDER BY maintenance_date DESC, created_at DESC";
                $stmt = $conn->query($sql);
                $records = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                json_response(['status' => 'success', 'records' => $records]);
                
            } elseif ($action === 'by_asset') {
                // Get maintenance records for specific asset
                $asset_id = $_GET['asset_id'] ?? '';
                $sql = "SELECT * FROM asset_maintenance WHERE asset_id = ? ORDER BY created_at DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$asset_id]);
                json_response(['status' => 'success', 'records' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
                
            } elseif ($action === 'pending') {
                // Get pending/scheduled maintenance
                $sql = "SELECT * FROM asset_maintenance WHERE status IN ('Scheduled', 'Completed') ORDER BY maintenance_date ASC";
                $stmt = $conn->query($sql);
                $records = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                json_response(['status' => 'success', 'records' => $records]);
                
            } else {
                json_response(['status' => 'error', 'message' => 'Invalid action'], 400);
            }
            break;

        case 'POST':
            $asset_id = $input['asset_id'] ?? '';
            $asset_name = $input['asset_name'] ?? '';
            $maintenance_type = $input['maintenance_type'] ?? 'Preventive';
            $maintenance_date = $input['maintenance_date'] ?? date('Y-m-d');
            $notes = $input['notes'] ?? '';

            if (empty($asset_id) || empty($asset_name)) {
                json_response(['status' => 'error', 'message' => 'Asset ID and Asset Name are required'], 400);
            }

            $sql = "INSERT INTO asset_maintenance (asset_id, asset_name, type, maintenance_date, notes, status) 
                    VALUES (?, ?, ?, ?, ?, 'Scheduled')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$asset_id, $asset_name, $maintenance_type, $maintenance_date, $notes]);

            json_response(['status' => 'success', 'message' => 'Maintenance record created', 'id' => $conn->lastInsertId()]);
            break;

        case 'PUT':
            $id = $input['id'] ?? 0;
            $status = $input['status'] ?? 'Scheduled';
            $notes = $input['notes'] ?? '';

            if ($id <= 0) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            // Get the maintenance record to find the asset_id
            $getMaintenance = $conn->prepare("SELECT asset_id FROM asset_maintenance WHERE id = ?");
            $getMaintenance->execute([$id]);
            $maintenance = $getMaintenance->fetch(PDO::FETCH_ASSOC);

            $sql = "UPDATE asset_maintenance SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$status, $notes, $id]);

            // If status is Completed, improve the asset's quality
            if ($status === 'Completed' && $maintenance) {
                // Find the asset in asset_management by asset_id
                $assetStmt = $conn->prepare("SELECT id, quality_multiplier FROM asset_management WHERE id = ?");
                $assetStmt->execute([$maintenance['asset_id']]);
                $asset = $assetStmt->fetch(PDO::FETCH_ASSOC);

                if ($asset) {
                    // Improve quality multiplier (cap at 1.0 = 100%)
                    $newMultiplier = min(1.0, ($asset['quality_multiplier'] ?? 1.0) + 0.1);
                    $updateAsset = $conn->prepare("UPDATE asset_management SET quality_multiplier = ?, last_maintenance_date = NOW() WHERE id = ?");
                    $updateAsset->execute([$newMultiplier, $asset['id']]);
                }
            }

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
    json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log('Maintenance API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()], 500);
}
?>
