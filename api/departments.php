<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    // Create departments table if not exists
    $conn->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        department_name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_department_name (department_name)
    )");

    // Create user_departments table if not exists
    $conn->exec("CREATE TABLE IF NOT EXISTS user_departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        department_id INT NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_dept (user_id, department_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_department_id (department_id)
    )");

    switch ($method) {
        // GET - List all departments or get user's department
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            if ($action === 'user_department') {
                $user_id = intval($_GET['user_id'] ?? $_SESSION['id']);
                $stmt = $conn->prepare("
                    SELECT d.id, d.department_name, d.description 
                    FROM departments d
                    JOIN user_departments ud ON d.id = ud.department_id
                    WHERE ud.user_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$user_id]);
                $department = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'department' => $department ?: ['id' => null, 'department_name' => 'Unknown Department']
                ]);
            } else {
                // List all departments
                $stmt = $conn->prepare("SELECT * FROM departments ORDER BY department_name ASC");
                $stmt->execute();
                $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => 'success',
                    'departments' => $departments,
                    'count' => count($departments)
                ]);
            }
            break;

        // POST - Create new department
        case 'POST':
            $department_name = trim($input['department_name'] ?? '');
            $description = trim($input['description'] ?? '');
            
            if (!$department_name) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Department name is required']);
                exit;
            }
            
            try {
                $stmt = $conn->prepare("INSERT INTO departments (department_name, description) VALUES (?, ?)");
                $stmt->execute([$department_name, $description]);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Department created successfully',
                    'id' => $conn->lastInsertId()
                ]);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'UNIQUE') !== false) {
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Department already exists']);
                } else {
                    throw $e;
                }
            }
            break;

        // PUT - Assign user to department
        case 'PUT':
            $user_id = intval($input['user_id'] ?? 0);
            $department_id = intval($input['department_id'] ?? 0);
            
            if (!$user_id || !$department_id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'user_id and department_id are required']);
                exit;
            }
            
            // First, remove existing department assignment
            $stmt = $conn->prepare("DELETE FROM user_departments WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Then, assign new department
            $stmt = $conn->prepare("INSERT INTO user_departments (user_id, department_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $department_id]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'User department updated successfully'
            ]);
            break;

        // DELETE - Remove department
        case 'DELETE':
            $id = intval($input['id'] ?? 0);
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID is required']);
                exit;
            }
            
            $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['status' => 'success', 'message' => 'Department deleted successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
