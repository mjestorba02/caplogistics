<?php
/**
 * Authorization Helper Functions
 * Used to check user permissions throughout the application
 */

/**
 * Check if current user is an admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['account_type']) && $_SESSION['account_type'] == 1;
}

/**
 * Check if current user is a regular user
 * @return bool
 */
function isRegularUser() {
    return isset($_SESSION['account_type']) && $_SESSION['account_type'] == 0;
}

/**
 * Require admin access - returns JSON error if not admin
 * @return void
 */
function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'You do not have permission to perform this action. Admin access required.'
        ]);
        exit;
    }
}

/**
 * Require authentication
 * @return void
 */
function requireAuth() {
    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized. Please login first.'
        ]);
        exit;
    }
}

/**
 * Get user account type label
 * @param int $type
 * @return string
 */
function getAccountTypeLabel($type) {
    switch($type) {
        case 1:
            return 'Admin';
        case 0:
            return 'User';
        default:
            return 'Unknown';
    }
}

/**
 * Check if user can approve/deny requests
 * @return bool
 */
function canApprove() {
    return isAdmin();
}

/**
 * Check if user can delete/archive items
 * @return bool
 */
function canArchive() {
    return isAdmin();
}

/**
 * Check if user can download reports
 * @return bool
 */
function canDownloadReports() {
    return isAdmin() || isRegularUser(); // Both can download, but need password verification
}
?>
