<?php
// This should be in a separate file: api/inventory.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include 'db.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle different content types
if ($method === "POST" || $method === "PUT") {
    // Check if we're receiving form data with files
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    
    if (strpos($contentType, 'multipart/form-data') !== false || !empty($_FILES)) {
        $data = $_POST;
        
        // Handle file upload
        if (isset($_FILES['product_photo']) && $_FILES['product_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate a safe filename
            $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($_FILES['product_photo']['name']));
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['product_photo']['tmp_name'], $uploadFile)) {
                $data['product_photo'] = $fileName;
            }
        }
    } else {
        // Handle JSON data
        $input = file_get_contents("php://input");
        if (!empty($input)) {
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON decoding fails, try to parse as form data
                parse_str($input, $data);
            }
        } else {
            $data = [];
        }
    }
} else {
    // For GET and DELETE, use query parameters
    $data = $_GET;
}

// Helper function to get data with defaults
function getDataValue($data, $key, $default = null) {
    return isset($data[$key]) && $data[$key] !== '' ? $data[$key] : $default;
}

// Helper function to handle NULL values for database
function dbValue($value) {
    return $value === null || $value === '' ? null : $value;
}

switch ($method) {
    // GET ALL or GET ONE
    case "GET":
        if (isset($data['id'])) {
            $id = intval($data['id']);
            $stmt = $conn->prepare("SELECT *, CONCAT('http://localhost/caplog1/uploads/products/', product_photo) as product_photo_url FROM inventory_items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item) {
                echo json_encode($item);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Item not found"]);
            }
        } else {
            $stmt = $conn->query("SELECT *, CONCAT('http://localhost/caplog1/uploads/products/', product_photo) as product_photo_url FROM inventory_items ORDER BY created_at DESC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($items);
        }
        break;

    // CREATE or UPDATE (using POST for both)
    case "POST":
        $id = getDataValue($data, 'id', null);
        
        if ($id) {
            // This is an UPDATE
            $id = intval($id);
            
            // Get current item data
            $currentStmt = $conn->prepare("SELECT * FROM inventory_items WHERE id = ?");
            $currentStmt->execute([$id]);
            $current = $currentStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current) {
                http_response_code(404);
                echo json_encode(["error" => "Item not found"]);
                exit;
            }
            
            // Handle file upload if present
            $product_photo = $current['product_photo'];
            if (isset($data['product_photo']) && !empty($data['product_photo'])) {
                $product_photo = $data['product_photo'];
                
                // Delete old photo if it exists and a new one is uploaded
                if ($current['product_photo'] && $current['product_photo'] !== $product_photo) {
                    $oldFile = '../uploads/products/' . $current['product_photo'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
            }
            
            // Update the item
            $stmt = $conn->prepare("UPDATE inventory_items SET item_name=?, sku=?, category=?, stock_level=?, reorder_level=?, supplier=?, price=?, last_restocked=?, notes=?, product_photo=? WHERE id=?");
            
            try {
                $stmt->execute([
                    getDataValue($data, 'item_name', $current['item_name']),
                    getDataValue($data, 'sku', $current['sku']),
                    getDataValue($data, 'category', $current['category']),
                    getDataValue($data, 'stock_level', $current['stock_level']),
                    getDataValue($data, 'reorder_level', $current['reorder_level']),
                    getDataValue($data, 'supplier', $current['supplier']),
                    getDataValue($data, 'price', $current['price']),
                    getDataValue($data, 'last_restocked', $current['last_restocked']),
                    getDataValue($data, 'notes', $current['notes']),
                    $product_photo,
                    $id
                ]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(["message" => "Item updated successfully"]);
                } else {
                    echo json_encode(["message" => "No changes were made to the item"]);
                }
                
            } catch (PDOException $e) {
                http_response_code(400);
                echo json_encode(["error" => $e->getMessage()]);
            }
            
        } else {
            // This is a CREATE
            $stmt = $conn->prepare("INSERT INTO inventory_items (item_name, sku, category, stock_level, reorder_level, supplier, price, last_restocked, notes, product_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            try {
                $stmt->execute([
                    getDataValue($data, 'item_name', ''),
                    getDataValue($data, 'sku', ''),
                    getDataValue($data, 'category', ''),
                    getDataValue($data, 'stock_level', 0),
                    getDataValue($data, 'reorder_level', 0),
                    getDataValue($data, 'supplier', ''),
                    getDataValue($data, 'price', 0.00),
                    getDataValue($data, 'last_restocked', date('Y-m-d')),
                    getDataValue($data, 'notes', ''),
                    getDataValue($data, 'product_photo', null)
                ]);
                http_response_code(201);
                echo json_encode(["message" => "Item added successfully", "id" => $conn->lastInsertId()]);
            } catch (PDOException $e) {
                http_response_code(400);
                echo json_encode(["error" => $e->getMessage()]);
            }
        }
        break;

    // DELETE
    case "DELETE":
        $id = getDataValue($data, 'id', null);
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }
        
        // First get the photo to delete it
        $currentStmt = $conn->prepare("SELECT product_photo FROM inventory_items WHERE id = ?");
        $currentStmt->execute([$id]);
        $current = $currentStmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the photo file if it exists
        if ($current && $current['product_photo']) {
            $file = '../uploads/products/' . $current['product_photo'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        $stmt = $conn->prepare("DELETE FROM inventory_items WHERE id=?");
        try {
            $stmt->execute([$id]);
            echo json_encode(["message" => "Item deleted successfully"]);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>