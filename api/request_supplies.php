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
    // Ensure procurement_requests table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS procurement_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        description TEXT,
        urgency ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
        requester_id INT NOT NULL,
        requester_name VARCHAR(255) NOT NULL,
        date_requested TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        INDEX idx_requester_id (requester_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            $search = trim($_GET['search'] ?? '');
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';

            $conditions = [];
            $params = [];

            if ($search !== '') {
                $conditions[] = "(item_name LIKE ? OR requester_name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($dateFrom !== '') {
                $conditions[] = "DATE(date_requested) >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo !== '') {
                $conditions[] = "DATE(date_requested) <= ?";
                $params[] = $dateTo;
            }

            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

            $sql = "SELECT * FROM procurement_requests $where ORDER BY date_requested DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response(['status' => 'success', 'requests' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'POST':
            $item_name = $input['item_name'] ?? '';
            $quantity = intval($input['quantity'] ?? 0);
            $description = $input['description'] ?? '';
            $urgency = $input['urgency'] ?? 'Medium';
            $requester_id = $input['requester_id'] ?? '';
            $requester_name = $input['requester_name'] ?? '';

            if (empty($item_name) || $quantity <= 0) {
                json_response(['status' => 'error', 'message' => 'Item name and quantity are required'], 400);
            }

            $sql = "INSERT INTO procurement_requests (item_name, quantity, description, urgency, requester_id, requester_name) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$item_name, $quantity, $description, $urgency, $requester_id, $requester_name]);

            json_response(['status' => 'success', 'message' => 'Request submitted successfully']);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $item_name = $input['item_name'] ?? '';
            $quantity = intval($input['quantity'] ?? 0);
            $description = $input['description'] ?? '';
            $urgency = $input['urgency'] ?? 'Medium';

            if (empty($id) || empty($item_name) || $quantity <= 0) {
                json_response(['status' => 'error', 'message' => 'ID, item name and quantity are required'], 400);
            }

            $sql = "UPDATE procurement_requests SET item_name=?, quantity=?, description=?, urgency=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$item_name, $quantity, $description, $urgency, $id]);

            json_response(['status' => 'success', 'message' => 'Request updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            $sql = "DELETE FROM procurement_requests WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);

            json_response(['status' => 'success', 'message' => 'Request deleted successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>