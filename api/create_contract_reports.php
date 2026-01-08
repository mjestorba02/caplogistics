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
    // Ensure procurement_contracts table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS procurement_contracts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contract_title VARCHAR(255) NOT NULL,
        supplier_name VARCHAR(255) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        contract_value DECIMAL(15,2),
        details TEXT,
        status ENUM('Active', 'Expired', 'Terminated') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_supplier_name (supplier_name),
        INDEX idx_status (status),
        INDEX idx_start_date (start_date),
        INDEX idx_end_date (end_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            if (isset($_GET['action']) && $_GET['action'] === 'report') {
                // Generate report
                $dateFrom = $_GET['date_from'] ?? '';
                $dateTo = $_GET['date_to'] ?? '';

                $conditions = [];
                $params = [];

                if ($dateFrom !== '') {
                    $conditions[] = "start_date >= ?";
                    $params[] = $dateFrom;
                }

                if ($dateTo !== '') {
                    $conditions[] = "end_date <= ?";
                    $params[] = $dateTo;
                }

                $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

                $sql = "SELECT * FROM procurement_contracts $where ORDER BY start_date DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Simple CSV export
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="contracts_report.csv"');
                $output = fopen('php://output', 'w');
                fputcsv($output, ['ID', 'Contract Title', 'Supplier', 'Start Date', 'End Date', 'Value', 'Status']);
                foreach ($contracts as $c) {
                    fputcsv($output, [$c['id'], $c['contract_title'], $c['supplier_name'], $c['start_date'], $c['end_date'], $c['contract_value'], $c['status']]);
                }
                fclose($output);
                exit;
            }

            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';

            $conditions = [];
            $params = [];

            if ($dateFrom !== '') {
                $conditions[] = "start_date >= ?";
                $params[] = $dateFrom;
            }

            if ($dateTo !== '') {
                $conditions[] = "end_date <= ?";
                $params[] = $dateTo;
            }

            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

            $sql = "SELECT * FROM procurement_contracts $where ORDER BY start_date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response(['status' => 'success', 'contracts' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'POST':
            $contract_title = $input['contract_title'] ?? '';
            $supplier_name = $input['supplier_name'] ?? '';
            $start_date = $input['start_date'] ?? '';
            $end_date = $input['end_date'] ?? '';
            $contract_value = !empty($input['contract_value']) ? floatval($input['contract_value']) : null;
            $details = $input['details'] ?? '';

            if (empty($contract_title) || empty($supplier_name) || empty($start_date) || empty($end_date)) {
                json_response(['status' => 'error', 'message' => 'Required fields are missing'], 400);
            }

            $sql = "INSERT INTO procurement_contracts (contract_title, supplier_name, start_date, end_date, contract_value, details) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$contract_title, $supplier_name, $start_date, $end_date, $contract_value, $details]);

            json_response(['status' => 'success', 'message' => 'Contract created successfully']);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $contract_title = $input['contract_title'] ?? '';
            $supplier_name = $input['supplier_name'] ?? '';
            $start_date = $input['start_date'] ?? '';
            $end_date = $input['end_date'] ?? '';
            $contract_value = !empty($input['contract_value']) ? floatval($input['contract_value']) : null;
            $details = $input['details'] ?? '';

            if (empty($id) || empty($contract_title) || empty($supplier_name) || empty($start_date) || empty($end_date)) {
                json_response(['status' => 'error', 'message' => 'ID and required fields are missing'], 400);
            }

            $sql = "UPDATE procurement_contracts SET contract_title=?, supplier_name=?, start_date=?, end_date=?, contract_value=?, details=? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$contract_title, $supplier_name, $start_date, $end_date, $contract_value, $details, $id]);

            json_response(['status' => 'success', 'message' => 'Contract updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            $sql = "DELETE FROM procurement_contracts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);

            json_response(['status' => 'success', 'message' => 'Contract deleted successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>