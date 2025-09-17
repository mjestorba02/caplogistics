<?php
header("Content-Type: application/json");
include 'db.php';

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Get ID from query if available
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch($method) {
    case "GET":
        if ($id) {
            // GET ONE
            $stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user ?: ["message" => "User not found"]);
        } else {
            // GET ALL
            $stmt = $conn->query("SELECT id, name, email, created_at FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
        break;

    case "POST":
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['name'], $data['email'], $data['password'])) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Email already exists"]);
            break;
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$data['name'], $data['email'], $passwordHash])) {
            echo json_encode(["message" => "User created successfully", "id" => $conn->lastInsertId()]);
        } else {
            echo json_encode(["message" => "Failed to create user"]);
        }
    } else {
        echo json_encode(["message" => "Invalid data"]);
    }
    break;


    case "PUT":
    if (!$id) {
        echo json_encode(["message" => "User ID required"]);
        break;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    $fields = [];
    $values = [];

    if (isset($data['name'])) {
        $fields[] = "name = ?";
        $values[] = $data['name'];
    }
    if (isset($data['email'])) {
        // Check if email already exists for another user
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtCheck->execute([$data['email'], $id]);
        if ($stmtCheck->rowCount() > 0) {
            echo json_encode(["message" => "Email already exists"]);
            break;
        }
        $fields[] = "email = ?";
        $values[] = $data['email'];
    }
    if (isset($data['password'])) {
        $fields[] = "password = ?";
        $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (!empty($fields)) {
        $values[] = $id;
        $stmt = $conn->prepare("UPDATE users SET " . implode(",", $fields) . " WHERE id = ?");
        if ($stmt->execute($values)) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "User updated successfully"]);
            } else {
                echo json_encode(["message" => "User already updated or not found"]);
            }
        } else {
            echo json_encode(["message" => "Failed to update user"]);
        }
    } else {
        echo json_encode(["message" => "No data to update"]);
    }
    break;

case "DELETE":
    if (!$id) {
        echo json_encode(["message" => "User ID required"]);
        break;
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(["message" => "User deleted successfully"]);
    } else {
        echo json_encode(["message" => "User already deleted or not found"]);
    }
    break;


    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
}
?>
