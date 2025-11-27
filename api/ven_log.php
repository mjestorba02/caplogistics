<?php
// http://localhost/logistics1_ecommerce/api/ven_log.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database connection
include 'db.php';

// Check if database connection was successful
if (!isset($conn) || $conn === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // If JSON parsing fails, try form data
    $input = $_POST;
}

// Get action from input data
$action = isset($input['action']) ? $input['action'] : '';

// Process the request based on method and action
if ($method === 'POST') {
    if ($action === 'register') {
        handleVendorRegistration($input);
    } elseif ($action === 'login') {
        handleVendorLogin($input);
    } else {
        // Default to login if no action specified
        handleVendorLogin($input);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

// Handle vendor registration
function handleVendorRegistration($data) {
    global $conn;
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }
    
    // Validate password strength
    if (strlen($data['password']) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters long']);
        return;
    }
    
    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM user_vendor WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already registered']);
            return;
        }
        
        // Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert new vendor
        $stmt = $conn->prepare("
            INSERT INTO user_vendor 
            (first_name, last_name, email, password, company_name) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $hashedPassword,
            $data['company_name'] ?? ''
        ]);
        
        $userId = $conn->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Vendor registered successfully.',
            'user_id' => $userId
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

// Handle vendor login
function handleVendorLogin($data) {
    global $conn;
    
    // Validate required fields
    if (empty($data['email']) || empty($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        return;
    }
    
    try {
        // Find vendor by email
        $stmt = $conn->prepare("
            SELECT id, first_name, last_name, email, password, is_active
            FROM user_vendor 
            WHERE email = ?
        ");
        
        $stmt->execute([$data['email']]);
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vendor) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
            return;
        }
        
        // Check if account is active
        if (!$vendor['is_active']) {
            http_response_code(401);
            echo json_encode(['error' => 'Account is deactivated. Please contact support.']);
            return;
        }
        
        // Verify password
        if (!password_verify($data['password'], $vendor['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
            return;
        }
        
        // Remove password from response
        unset($vendor['password']);
        
        // Start session
        session_start();
        $_SESSION['vendor_id'] = $vendor['id'];
        $_SESSION['vendor_email'] = $vendor['email'];
        $_SESSION['vendor_name'] = $vendor['first_name'] . ' ' . $vendor['last_name'];
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'vendor' => $vendor
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}