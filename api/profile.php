<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Auth guard - require vendor session
if (!isset($_SESSION['vendor_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function json_response($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Determine if a stored password value is a hash (bcrypt/argon)
function is_password_hashed($value) {
    return is_string($value) && preg_match('/^\$2y\$|^\$argon2id\$|^\$argon2i\$/', $value) === 1;
}

// Backward-compatible password verification. If the stored password is not hashed,
// fall back to a constant-time string comparison.
function verify_password_compat(string $plain, string $stored): bool {
    if (is_password_hashed($stored)) {
        return password_verify($plain, $stored);
    }
    return hash_equals((string)$stored, (string)$plain);
}

function get_profile(PDO $conn, int $user_id) {
    $stmt = $conn->prepare(
        'SELECT id, first_name, last_name, email, company_name, phone, address, profile_image, is_active, 
                COALESCE(bio, "") AS bio, 
                COALESCE(is_email_verified, 0) AS is_email_verified, 
                COALESCE(verification_token, "") AS verification_token
         FROM user_vendor WHERE id = ?'
    );
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_buyer_profile(PDO $conn, int $user_id) {
    // buyer/customer profiles are stored in `users` table
    $stmt = $conn->prepare('SELECT id, name AS first_name, email, NULL AS last_name, NULL AS company_name, NULL AS phone, NULL AS address, NULL AS profile_image, 1 AS is_active, COALESCE("", "") AS bio FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

try {
    $user_id = (int)$_SESSION['vendor_id'];

    switch ($method) {
        case 'GET': {
            // If vendor requests a buyer profile via ?user_id, return that buyer's profile
            if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
                $targetId = intval($_GET['user_id']);
                $buyer = get_buyer_profile($conn, $targetId);
                if (!$buyer) json_response(['status' => 'error', 'message' => 'User not found'], 404);
                json_response(['status' => 'success', 'data' => $buyer]);
            }

            // Default: return vendor's own profile
            $user = get_profile($conn, $user_id);
            if (!$user) {
                json_response(['status' => 'error', 'message' => 'User not found'], 404);
            }
            json_response(['status' => 'success', 'data' => $user]);
        }

        case 'PUT': {
            // Two modes: password change (?action=password) or JSON profile update (text fields only)
            $action = isset($_GET['action']) ? $_GET['action'] : 'profile';

            if ($action === 'password') {
                $input = json_decode(file_get_contents('php://input'), true);
                if (!is_array($input)) {
                    json_response(['status' => 'error', 'message' => 'Invalid JSON input'], 400);
                }
                $current_password = trim($input['current_password'] ?? '');
                $new_password = trim($input['new_password'] ?? '');

                if ($current_password === '' || $new_password === '') {
                    json_response(['status' => 'error', 'message' => 'Current password and new password are required'], 400);
                }

                // Fetch current password hash
                $stmt = $conn->prepare('SELECT password FROM user_vendor WHERE id = ?');
                $stmt->execute([$user_id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    json_response(['status' => 'error', 'message' => 'User not found'], 404);
                }

                if (!verify_password_compat($current_password, $row['password'])) {
                    json_response(['status' => 'error', 'message' => 'Current password is incorrect'], 400);
                }

                $newHash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE user_vendor SET password = ? WHERE id = ?');
                $stmt->execute([$newHash, $user_id]);

                json_response(['status' => 'success', 'message' => 'Password updated successfully']);
            }

            // JSON profile update for text fields (no files here)
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                json_response(['status' => 'error', 'message' => 'Invalid JSON input'], 400);
            }

            $first_name = trim($input['first_name'] ?? '');
            $last_name  = trim($input['last_name'] ?? '');
            $phone      = trim($input['phone'] ?? '');
            $address    = trim($input['address'] ?? '');
            $bio        = trim($input['bio'] ?? '');

            $fields = [];
            $params = [];

            if ($first_name !== '') { $fields[] = 'first_name = ?'; $params[] = $first_name; }
            if ($last_name  !== '') { $fields[] = 'last_name = ?';  $params[] = $last_name; }
            if ($phone      !== '') { $fields[] = 'phone = ?';      $params[] = $phone; }
            if ($address    !== '') { $fields[] = 'address = ?';    $params[] = $address; }
            if ($bio        !== '') { $fields[] = 'bio = ?';        $params[] = $bio; }

            // company_name and email are read-only

            if (empty($fields)) {
                json_response(['status' => 'error', 'message' => 'No fields to update'], 400);
            }

            $params[] = $user_id;
            $sql = 'UPDATE user_vendor SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $user = get_profile($conn, $user_id);
            json_response(['status' => 'success', 'message' => 'Profile updated successfully', 'data' => $user]);
        }

        case 'POST': {
            // Multipart/form-data update including profile_image; also accepts text fields
            // Accept: first_name, last_name, phone, address, bio, profile_image

            $first_name = trim($_POST['first_name'] ?? '');
            $last_name  = trim($_POST['last_name'] ?? '');
            $phone      = trim($_POST['phone'] ?? '');
            $address    = trim($_POST['address'] ?? '');
            $bio        = trim($_POST['bio'] ?? '');

            $fields = [];
            $params = [];

            if ($first_name !== '') { $fields[] = 'first_name = ?'; $params[] = $first_name; }
            if ($last_name  !== '') { $fields[] = 'last_name = ?';  $params[] = $last_name; }
            if ($phone      !== '') { $fields[] = 'phone = ?';      $params[] = $phone; }
            if ($address    !== '') { $fields[] = 'address = ?';    $params[] = $address; }
            if ($bio        !== '') { $fields[] = 'bio = ?';        $params[] = $bio; }

            // Handle file upload
            if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $tmp_name = $_FILES['profile_image']['tmp_name'];
                $mime = @mime_content_type($tmp_name);
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if ($mime === false || !in_array($mime, $allowed_types, true)) {
                    json_response(['status' => 'error', 'message' => 'Only JPG, PNG and GIF files are allowed'], 400);
                }
                if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) { // 2MB
                    json_response(['status' => 'error', 'message' => 'File size must be less than 2MB'], 400);
                }

                $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                $target = $upload_dir . $filename;
                if (!move_uploaded_file($tmp_name, $target)) {
                    json_response(['status' => 'error', 'message' => 'Failed to upload image'], 500);
                }
                // Store only file name; frontend can resolve URL
                $fields[] = 'profile_image = ?';
                $params[] = $filename;
            }

            if (empty($fields)) {
                json_response(['status' => 'error', 'message' => 'No fields to update'], 400);
            }

            $params[] = $user_id;
            $sql = 'UPDATE user_vendor SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $user = get_profile($conn, $user_id);
            json_response(['status' => 'success', 'message' => 'Profile updated successfully', 'data' => $user]);
        }

        case 'DELETE': {
            // Soft delete (deactivate) current vendor account
            $stmt = $conn->prepare('UPDATE user_vendor SET is_active = 0 WHERE id = ?');
            $stmt->execute([$user_id]);
            json_response(['status' => 'success', 'message' => 'Account deactivated']);
        }

        default: {
            header('Allow: GET, POST, PUT, DELETE');
            json_response(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }
    }
} catch (Throwable $e) {
    error_log('Profile API Error: ' . $e->getMessage());
    json_response(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()], 500);
}
