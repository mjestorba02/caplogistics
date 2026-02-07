<?php
session_start();
/**
 * Centralized permission helpers
 */

function isAdmin() {
    return isset($_SESSION['account_type']) && intval($_SESSION['account_type']) === 1;
}

function isRegularUser() {
    return isset($_SESSION['account_type']) && intval($_SESSION['account_type']) === 0;
}

function requireAdminJson() {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin access required.']);
        exit;
    }
}

function requireAuthJson() {
    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please login.']);
        exit;
    }
}

// Use this to check in UI pages
function enforceAdminUI() {
    if (!isAdmin()) {
        echo "<div class='p-6 bg-red-100 text-red-800 rounded-lg m-6'>";
        echo "<h2 class='text-xl font-bold mb-2'>Access Denied</h2>";
        echo "<p>Only administrators can access this page.</p>";
        echo "</div>";
        exit;
    }
}

?>
