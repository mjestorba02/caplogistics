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
    // Ensure contracts table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS contracts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT,
        contract_title VARCHAR(255) NOT NULL,
        supplier_name VARCHAR(255) NOT NULL,
        contract_value DECIMAL(15,2),
        status ENUM('Requested', 'Approved', 'Signed', 'Completed') DEFAULT 'Requested',
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        approved_date TIMESTAMP NULL,
        INDEX idx_project_id (project_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            $contracts = $conn->query("SELECT c.*, p.project_name FROM contracts c LEFT JOIN projects p ON c.project_id = p.id ORDER BY c.request_date DESC")->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'contracts' => $contracts]);
            break;

        case 'POST':
            $sql = "INSERT INTO contracts (project_id, contract_title, supplier_name, contract_value) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['project_id'], $input['contract_title'], $input['supplier_name'], $input['contract_value']]);
            json_response(['status' => 'success', 'message' => 'Contract requested successfully']);
            break;

        case 'PUT':
            $sql = "UPDATE contracts SET status = ?, approved_date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['status'], $input['contract_id']]);
            json_response(['status' => 'success', 'message' => 'Contract status updated successfully']);
            break;

        case 'DELETE':
            $sql = "DELETE FROM contracts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$input['contract_id']]);
            json_response(['status' => 'success', 'message' => 'Contract deleted successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>