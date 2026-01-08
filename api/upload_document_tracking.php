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
    // Ensure document_uploads table exists
    $conn->exec("CREATE TABLE IF NOT EXISTS document_uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NULL,
        uploader_id INT NOT NULL,
        uploader_name VARCHAR(255) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        status ENUM('Uploaded', 'Verified', 'Rejected') DEFAULT 'Uploaded',
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_request_id (request_id),
        INDEX idx_uploader_id (uploader_id),
        INDEX idx_status (status),
        INDEX idx_upload_date (upload_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    switch ($method) {
        case 'GET':
            $status = $_GET['status'] ?? '';

            $conditions = [];
            $params = [];

            if ($status !== '') {
                $conditions[] = "status = ?";
                $params[] = $status;
            }

            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

            $sql = "SELECT * FROM document_uploads $where ORDER BY upload_date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response(['status' => 'success', 'uploads' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'POST':
            // Handle file upload
            if (!isset($_FILES['document'])) {
                json_response(['status' => 'error', 'message' => 'No file uploaded'], 400);
            }

            $file = $_FILES['document'];
            $request_id = $_POST['request_id'] ?? null;
            $status = $_POST['status'] ?? 'Uploaded';

            if ($file['error'] !== UPLOAD_ERR_OK) {
                json_response(['status' => 'error', 'message' => 'File upload error'], 400);
            }

            $uploadDir = __DIR__ . '/../uploads/documents/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = basename($file['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $sql = "INSERT INTO document_uploads (request_id, uploader_id, uploader_name, file_name, file_path, status) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$request_id, $_SESSION['id'], $_SESSION['name'], $fileName, $filePath, $status]);

                json_response(['status' => 'success', 'message' => 'Document uploaded successfully']);
            } else {
                json_response(['status' => 'error', 'message' => 'Failed to save file'], 500);
            }
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $status = $input['status'] ?? '';

            if (empty($id) || empty($status)) {
                json_response(['status' => 'error', 'message' => 'ID and status are required'], 400);
            }

            $sql = "UPDATE document_uploads SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$status, $id]);

            json_response(['status' => 'success', 'message' => 'Upload status updated successfully']);
            break;

        case 'DELETE':
            $id = $input['id'] ?? '';

            if (empty($id)) {
                json_response(['status' => 'error', 'message' => 'ID is required'], 400);
            }

            // Get file path to delete file
            $sql = "SELECT file_path FROM document_uploads WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $upload = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($upload && file_exists($upload['file_path'])) {
                unlink($upload['file_path']);
            }

            $sql = "DELETE FROM document_uploads WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);

            json_response(['status' => 'success', 'message' => 'Upload deleted successfully']);
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>