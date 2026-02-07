<?php
session_start();
header("Content-Type: application/json");
include 'db.php'; // Your database connection
include 'debug_logger.php'; // Debug logging

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

$action = trim($input['action'] ?? '');
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');
$otp_code = $input['otp_code'] ?? ''; // Do NOT trim OTP - preserves leading zeros

// ============================================================================
// HANDLE LOGIN REQUEST (Step 1: Email + Password)
// ============================================================================
if ($action === '' || $action === 'login') {
    // Validate input
    if (!$email || !$password) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
        exit;
    }

    // Fetch user from database
    $stmt = $conn->prepare("SELECT id, name, email, password, account_type, is_otp_enabled FROM users WHERE email = ?");
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

    // Password correct - generate OTP if enabled for this user
    if ($user['is_otp_enabled'] == 1) {
        // Generate 6-digit OTP
        $otp_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Store OTP in database
        $stmt = $conn->prepare("INSERT INTO user_otps (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $otp_code, $expires_at]);
        
        // Send OTP via email (async/non-blocking)
        // Using set_time_limit to prevent timeout
        set_time_limit(5);
        
        $subject = "E-Commerce Logistics Management Portal - Your OTP Code";
        $message = "Your OTP code is: $otp_code\nThis code expires in 10 minutes.\n\n— E-Commerce Logistics Management Portal";
        
        // Attempt to send via mailer wrapper with timeout handling
        try {
            require_once __DIR__ . '/mailer.php';
            $fromName = 'E-Commerce Logistics Management Portal';
            
            // Use async mail sending (don't wait for response)
            // Wrap in try-catch to prevent timeout from blocking login
            $mailResult = @sendMail($email, $subject, $message, $fromName, '');
        } catch (Throwable $e) {
            // Mail sending failed but don't block login flow
            error_log('OTP email send failed: ' . $e->getMessage());
        }
        
        // Store temp session data (not full login yet)
        $_SESSION['temp_user_id'] = $user['id'];
        $_SESSION['temp_user_email'] = $email;
        $_SESSION['otp_attempts'] = 0;
        
        // Return response with redirect URL to OTP verification page
        $response = [
            'status' => 'otp_required',
            'message' => 'OTP has been sent to your email. Redirecting to verification page.',
            'temp_user_id' => $user['id'],
            'otp_sent' => true,
            'sender_name' => 'E-Commerce Logistics Management Portal',
            'redirect_url' => 'pages/otp_verify.php'
        ];
        echo json_encode($response);
        exit;
    } else {
        // OTP disabled for this user, login directly
        $_SESSION['id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['account_type'] = $user['account_type'];

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful.',
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'account_type' => $user['account_type'],
            'otp_required' => false
        ]);
        exit;
    }
}

// ============================================================================
// HANDLE OTP VERIFICATION (Step 2: Verify OTP)
// ============================================================================
else if ($action === 'verify_otp') {
    // Check if user has temporary session
    if (!isset($_SESSION['temp_user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Session expired. Please login again.']);
        exit;
    }

    if (!$otp_code) {
        echo json_encode(['status' => 'error', 'message' => 'OTP code is required.']);
        exit;
    }

    $user_id = $_SESSION['temp_user_id'];
    $email = $_SESSION['temp_user_email'];

    // Fetch the latest OTP for this user
    $stmt = $conn->prepare("
        SELECT id, otp_code, is_used, expires_at FROM user_otps 
        WHERE user_id = ? AND is_used = 0 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $otp_record = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if OTP exists and matches
    if (!$otp_record) {
        $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
        
        if ($_SESSION['otp_attempts'] >= 3) {
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_user_email']);
            unset($_SESSION['otp_attempts']);
            echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Please login again.']);
            exit;
        }
        
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP.']);
        exit;
    }

    // Check if OTP expired
    if (strtotime($otp_record['expires_at']) < time()) {
        echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please login again.']);
        exit;
    }

    // Verify OTP code
    if ($otp_record['otp_code'] !== $otp_code) {
        $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
        
        if ($_SESSION['otp_attempts'] >= 3) {
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_user_email']);
            unset($_SESSION['otp_attempts']);
            echo json_encode(['status' => 'error', 'message' => 'Too many failed attempts. Please login again.']);
            exit;
        }
        
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please try again.']);
        exit;
    }

    // OTP verified! Mark as used and login user
    $stmt = $conn->prepare("UPDATE user_otps SET is_used = 1, used_at = NOW() WHERE id = ?");
    $stmt->execute([$otp_record['id']]);

    // Fetch full user data and establish session
    $stmt = $conn->prepare("SELECT id, name, email, account_type FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['account_type'] = $user['account_type'];
        
        // Clear temporary session data
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_user_email']);
        unset($_SESSION['otp_attempts']);

        echo json_encode([
            'status' => 'success',
            'message' => 'OTP verified. Login successful.',
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'account_type' => $user['account_type']
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit;
    }
}

// ============================================================================
// INVALID ACTION
// ============================================================================
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    exit;
}
?>
