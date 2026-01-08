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
    // Ensure document_requests table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS document_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        requester_id INT NOT NULL,
        requester_name VARCHAR(255) NOT NULL,
        document_type VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('Pending', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending',
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        approved_date TIMESTAMP NULL,
        INDEX idx_requester_id (requester_id),
        INDEX idx_status (status),
        INDEX idx_request_date (request_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            $status = $_GET['status'] ?? '';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';

            $conditions = [];
            $params = [];

            if ($status !== '') {
                $conditions[] = "status = ?";
                $params[] = $status;
            }

            if ($dateFrom !== '') {
                $conditions[] = "DATE(request_date) >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo !== '') {
                $conditions[] = "DATE(request_date) <= ?";
                $params[] = $dateTo;
            }

            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

            $sql = "SELECT * FROM document_requests $where ORDER BY request_date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response(['status' => 'success', 'requests' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $status = $input['status'] ?? '';

            if (empty($id) || empty($status)) {
                json_response(['status' => 'error', 'message' => 'ID and status are required'], 400);
            }

            $sql = "UPDATE document_requests SET status = ?, approved_date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$status, $id]);

            json_response(['status' => 'success', 'message' => 'Request status updated successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>