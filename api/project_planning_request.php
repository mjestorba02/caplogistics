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
    // Ensure tables exist
    $conn->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_name VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('Planning', 'InProgress', 'Completed', 'Cancelled') DEFAULT 'Planning',
        start_date DATE,
        end_date DATE,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_created_by (created_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conn->exec("CREATE TABLE IF NOT EXISTS project_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT,
        requester_id INT NOT NULL,
        requester_name VARCHAR(255) NOT NULL,
        request_type VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_project_id (project_id),
        INDEX idx_requester_id (requester_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            // Get projects and requests
            $projects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            $requests = $conn->query("SELECT pr.*, p.project_name FROM project_requests pr LEFT JOIN projects p ON pr.project_id = p.id ORDER BY pr.request_date DESC")->fetchAll(PDO::FETCH_ASSOC);
            json_response(['status' => 'success', 'projects' => $projects, 'requests' => $requests]);
            break;

        case 'POST':
            if (isset($input['project_name'])) {
                // Create project
                $sql = "INSERT INTO projects (project_name, description, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$input['project_name'], $input['description'], $input['start_date'], $input['end_date'], $_SESSION['id']]);
                json_response(['status' => 'success', 'message' => 'Project created successfully']);
            } elseif (isset($input['request_type'])) {
                // Create request
                $sql = "INSERT INTO project_requests (project_id, requester_id, requester_name, request_type, description) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$input['project_id'], $_SESSION['id'], $_SESSION['name'], $input['request_type'], $input['description']]);
                json_response(['status' => 'success', 'message' => 'Request submitted successfully']);
            }
            break;

        case 'PUT':
            if (isset($input['project_id'])) {
                // Update project
                $sql = "UPDATE projects SET project_name=?, description=?, status=?, start_date=?, end_date=? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$input['project_name'], $input['description'], $input['status'], $input['start_date'], $input['end_date'], $input['project_id']]);
                json_response(['status' => 'success', 'message' => 'Project updated successfully']);
            } elseif (isset($input['request_id'])) {
                // Update request status
                $sql = "UPDATE project_requests SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$input['status'], $input['request_id']]);
                json_response(['status' => 'success', 'message' => 'Request status updated successfully']);
            }
            break;

        case 'DELETE':
            if (isset($input['project_id'])) {
                $sql = "DELETE FROM projects WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$input['project_id']]);
                json_response(['status' => 'success', 'message' => 'Project deleted successfully']);
            } elseif (isset($input['request_id'])) {
                $sql = "DELETE FROM project_requests WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$input['request_id']]);
                json_response(['status' => 'success', 'message' => 'Request deleted successfully']);
            }
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>