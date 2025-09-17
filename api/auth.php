<?php
session_start();
header("Content-Type: application/json");
include 'db.php'; // Your database connection

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

// Validate input
if (!$email || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit;
}

// Fetch user from database
$stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
    exit;
}

// Login successful, store session
$_SESSION['id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];

// Return success
echo json_encode([
    'status' => 'success',
    'message' => 'Login successful.',
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email']
]);
?>
