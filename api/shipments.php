<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Your PDO connection

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

switch ($method) {

    // GET all shipments or single shipment
    case "GET":
        if(isset($_GET['id'])){
            $stmt = $conn->prepare("SELECT * FROM shipments WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
            if($shipment){
                echo json_encode($shipment);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Shipment not found"]);
            }
        } else {
            $stmt = $conn->query("SELECT * FROM shipments ORDER BY created_at DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    // CREATE shipment
    case "POST":
        $stmt = $conn->prepare("INSERT INTO shipments (shipment_number, origin, destination, items_quantity, dispatch_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if($stmt->execute([
            $data['shipment_number'],
            $data['origin'],
            $data['destination'],
            $data['items_quantity'],
            $data['dispatch_date'],
            $data['status'] ?? 'Pending',
            $data['notes'] ?? ''
        ])){
            http_response_code(201);
            echo json_encode(["message" => "Shipment added successfully", "id" => $conn->lastInsertId()]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Failed to add shipment"]);
        }
        break;

    // UPDATE shipment
    case "PUT":
        if(!isset($_GET['id'])){
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE shipments SET shipment_number=?, origin=?, destination=?, items_quantity=?, dispatch_date=?, status=?, notes=? WHERE id=?");
        if($stmt->execute([
            $data['shipment_number'],
            $data['origin'],
            $data['destination'],
            $data['items_quantity'],
            $data['dispatch_date'],
            $data['status'] ?? 'Pending',
            $data['notes'] ?? '',
            $id
        ])){
            echo json_encode(["message" => "Shipment updated successfully"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Failed to update shipment"]);
        }
        break;

    // DELETE shipment
    case "DELETE":
        if(!isset($_GET['id'])){
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("DELETE FROM shipments WHERE id=?");
        if($stmt->execute([$id])){
            echo json_encode(["message" => "Shipment deleted successfully"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Failed to delete shipment"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

?>
