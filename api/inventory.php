<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

switch ($method) {

    // GET ALL or GET ONE
    case "GET":
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM inventory_items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item) {
                echo json_encode($item);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Item not found"]);
            }
        } else {
            $stmt = $conn->query("SELECT * FROM inventory_items ORDER BY created_at DESC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($items);
        }
        break;

    // CREATE
    case "POST":
        $stmt = $conn->prepare("INSERT INTO inventory_items (item_name, sku, category, stock_level, reorder_level, supplier, price, last_restocked, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([
                $data['item_name'],
                $data['sku'],
                $data['category'],
                $data['stock_level'],
                $data['reorder_level'],
                $data['supplier'],
                $data['price'],
                $data['last_restocked'],
                $data['notes']
            ]);
            http_response_code(201);
            echo json_encode(["message" => "Item added successfully", "id" => $conn->lastInsertId()]);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // UPDATE
    case "PUT":
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE inventory_items SET item_name=?, sku=?, category=?, stock_level=?, reorder_level=?, supplier=?, price=?, last_restocked=?, notes=? WHERE id=?");
        try {
            $stmt->execute([
                $data['item_name'],
                $data['sku'],
                $data['category'],
                $data['stock_level'],
                $data['reorder_level'],
                $data['supplier'],
                $data['price'],
                $data['last_restocked'],
                $data['notes'],
                $id
            ]);
            echo json_encode(["message" => "Item updated successfully"]);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    // DELETE
    case "DELETE":
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }
        $id = intval($_GET['id']);
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
