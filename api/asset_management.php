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
$input = [];

// Handle FormData (file uploads) and JSON
if ($method === 'POST' || $method === 'PUT') {
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false) {
        // FormData upload
        $input = $_POST;
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $uploadPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $input['image'] = '/caplog1/uploads/' . $fileName;
            }
        }
    } else {
        // JSON data
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }
} else {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

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
        image VARCHAR(255) NULL,
        type_of_asset VARCHAR(100) NULL,
        item_code VARCHAR(100) NULL,
        item_name VARCHAR(255) NULL,
        status ENUM('Active', 'Inactive', 'Maintenance', 'Retired') DEFAULT 'Active',
        purchase_date DATE NULL,
        lifespan_years INT DEFAULT 5,
        date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_item_number (item_number),
        INDEX idx_status (status)
    )");

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

            $sql = "
                SELECT *,
                    CASE 
                        WHEN purchase_date IS NULL THEN 100
                        ELSE GREATEST(
                            0,
                            ROUND(
                                100 - (
                                    (TIMESTAMPDIFF(DAY, purchase_date, CURDATE()) 
                                    / (lifespan_years * 365)) * 100
                                )
                            )
                        )
                    END AS quality_percent
                FROM asset_management
                $where
                ORDER BY date DESC
                ";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            json_response(['status' => 'success', 'assets' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'POST':
            $item_number = trim($input['item_number'] ?? '');
            $image = trim($input['image'] ?? '');
            $type_of_asset = trim($input['type_of_asset'] ?? '');
            $item_code = trim($input['item_code'] ?? '');
            $item_name = trim($input['item_name'] ?? '');
            $status = $input['status'] ?? 'Active';
            $purchase_date = $input['purchase_date'] ?? null;
            $lifespan_years = $input['lifespan_years'] ?? 5;

            if (empty($item_number)) {
                json_response(['status' => 'error', 'message' => 'Item number is required'], 400);
            }

            $sql = "INSERT INTO asset_management 
                (item_number, image, type_of_asset, item_code, item_name, status, purchase_date, lifespan_years)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $item_number,
                $image,
                $type_of_asset,
                $item_code,
                $item_name,
                $status,
                $purchase_date,
                $lifespan_years
            ]);

            json_response(['status' => 'success', 'message' => 'Asset added successfully']);
            break;

        case 'PUT':
            $id = $input['id'] ?? '';
            $item_number = trim($input['item_number'] ?? '');
            $image = trim($input['image'] ?? '');
            $type_of_asset = trim($input['type_of_asset'] ?? '');
            $item_code = trim($input['item_code'] ?? '');
            $item_name = trim($input['item_name'] ?? '');
            $status = $input['status'] ?? 'Active';

            if (empty($id) || empty($item_number)) {
                json_response(['status' => 'error', 'message' => 'ID and item number are required'], 400);
            }

            $sql = "UPDATE asset_management 
                SET item_number=?, image=?, type_of_asset=?, item_code=?, item_name=?, status=?, purchase_date=?, lifespan_years=?
                WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$item_number, $image, $type_of_asset, $item_code, $item_name, $status, $id]);

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