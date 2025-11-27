<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { 
    $input = []; 
}

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            // Query actual supplier_identification table from database
            $sql = "SELECT * FROM supplier_identification ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($search) {
                $suppliers = array_filter($suppliers, function($s) use ($search) {
                    return stripos($s['supplier_name'] ?? '', $search) !== false || 
                           stripos($s['certifications'] ?? '', $search) !== false;
                });
            }
            
            json_response(['status' => 'success', 'suppliers' => array_values($suppliers)]);
            break;

        case 'POST':
            $supplier_name = $input['supplier_name'] ?? '';
            $contact_email = $input['contact_email'] ?? '';
            $certifications = isset($input['certifications']) ? implode(', ', $input['certifications']) : 'None';
            $risk_level = $input['risk_level'] ?? 'Medium';
            $phone = $input['phone'] ?? '';
            $notes = $input['notes'] ?? '';

            if (empty($supplier_name) || empty($contact_email)) {
                json_response(['status' => 'error', 'message' => 'Supplier name and email are required'], 400);
            }

            if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
                json_response(['status' => 'error', 'message' => 'Invalid email format'], 400);
            }

            try {
                // Insert into supplier_identification table
                $sql = "INSERT INTO supplier_identification (supplier_name, contact_email, certifications, risk_level, phone, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$supplier_name, $contact_email, $certifications, $risk_level, $phone, $notes]);

                json_response(['status' => 'success', 'message' => 'Supplier added successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'PUT':
            $supplier_id = $input['id'] ?? '';
            $supplier_name = $input['supplier_name'] ?? '';
            $contact_email = $input['contact_email'] ?? '';
            $certifications = isset($input['certifications']) ? implode(', ', $input['certifications']) : 'None';
            $risk_level = $input['risk_level'] ?? 'Medium';
            $phone = $input['phone'] ?? '';
            $notes = $input['notes'] ?? '';

            if (empty($supplier_id)) {
                json_response(['status' => 'error', 'message' => 'Supplier ID is required'], 400);
            }

            if (empty($supplier_name) || empty($contact_email)) {
                json_response(['status' => 'error', 'message' => 'Supplier name and email are required'], 400);
            }

            try {
                // Update supplier_identification table
                $sql = "UPDATE supplier_identification SET supplier_name = ?, contact_email = ?, certifications = ?, risk_level = ?, phone = ?, notes = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$supplier_name, $contact_email, $certifications, $risk_level, $phone, $notes, $supplier_id]);

                json_response(['status' => 'success', 'message' => 'Supplier updated successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        case 'DELETE':
            $supplier_id = $input['id'] ?? '';
            
            if (empty($supplier_id)) {
                json_response(['status' => 'error', 'message' => 'Supplier ID is required'], 400);
            }

            try {
                // Delete from supplier_identification table
                $sql = "DELETE FROM supplier_identification WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$supplier_id]);

                json_response(['status' => 'success', 'message' => 'Supplier deleted successfully']);
            } catch (Exception $e) {
                json_response(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
            }
            break;

        default:
            json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
