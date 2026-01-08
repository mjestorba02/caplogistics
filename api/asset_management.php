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
    // Ensure asset_management table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS asset_management (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_number VARCHAR(255) NOT NULL UNIQUE,
        qr_code VARCHAR(255) NULL,
        status ENUM('Release', 'InTransit', 'Pending') DEFAULT 'Pending',
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_item_number (item_number),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            $searchItem = trim($_GET['item_number'] ?? '');
            $searchStatus = $_GET['status'] ?? '';

            $conditions = [];
            $params = [];

            if ($searchItem !== '') {
                $conditions[] = "item_number LIKE ?";
                $params[] = "%$searchItem%";
            }

            if ($searchStatus !== '') {
                $conditions[] = "status = ?";
                $params[] = $searchStatus;
            }

            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

            $sql = "SELECT * FROM asset_management $where ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response(['status' => 'success', 'assets' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'POST':
            $item_number = trim($input['item_number'] ?? '');
            $qr_code = trim($input['qr_code'] ?? '');
            $status = $input['status'] ?? 'Pending';
            $description = $input['description'] ?? '';

            if (empty($item_number)) {
                json_response(['status' => 'error', 'message' => 'Item number is required'], 400);
            }

            $sql = "INSERT INTO asset_management (item_number, qr_code, status, description) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$item_number, $qr_code, $status, $description]);

            json_response(['status' => 'success', 'message' => 'Asset added successfully']);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $item_number = trim($input['item_number'] ?? '');
            $qr_code = trim($input['qr_code'] ?? '');
            $status = $input['status'] ?? 'Pending';
            $description = $input['description'] ?? '';

            if (empty($id) || empty($item_number)) {
                json_response(['status' => 'error', 'message' => 'ID and item number are required'], 400);
            }

            $sql = "UPDATE asset_management SET item_number=?, qr_code=?, status=?, description=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$item_number, $qr_code, $status, $description, $id]);

            json_response(['status' => 'success', 'message' => 'Asset updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            $sql = "DELETE FROM asset_management WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);

            json_response(['status' => 'success', 'message' => 'Asset deleted successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>