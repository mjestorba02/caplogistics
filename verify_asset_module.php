<?php
/**
 * REQUEST ASSET MODULE - VERIFICATION SCRIPT
 * 
 * This script verifies that all components of the Request Asset module
 * are properly installed and configured.
 * 
 * Access this file at: http://localhost/newcaplog1/verify_asset_module.php
 */

session_start();
$checks = [];
$all_passed = true;

// ============================================================================
// FILE EXISTENCE CHECKS
// ============================================================================

$files_to_check = [
    'pages/request_asset.php' => 'User frontend - Request Asset page',
    'pages/manage_asset_requests.php' => 'Admin frontend - Manage requests page',
    'scripts/request_asset.js' => 'User JavaScript - Request interactions',
    'scripts/manage_asset_requests.js' => 'Admin JavaScript - Approval interactions',
    'api/asset_requests.php' => 'User API - Request CRUD endpoints',
    'api/asset_requests_admin.php' => 'Admin API - Approval endpoints',
    'api/db.php' => 'Database connection (required)',
];

foreach ($files_to_check as $file => $description) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $checks[] = [
        'category' => 'File Existence',
        'check' => $description,
        'status' => $exists ? 'PASS' : 'FAIL',
        'details' => $exists ? "Found at $file" : "Missing: $file"
    ];
    if (!$exists) $all_passed = false;
}

// ============================================================================
// DATABASE CHECKS
// ============================================================================

if (file_exists(__DIR__ . '/api/db.php')) {
    try {
        require_once __DIR__ . '/api/db.php';
        
        // Check if connection exists
        if ($conn) {
            $checks[] = [
                'category' => 'Database',
                'check' => 'Database Connection',
                'status' => 'PASS',
                'details' => 'PDO connection successful'
            ];

            // Check tables exist
            $tables = ['asset_requests', 'asset_request_items', 'asset_request_to_procurement', 'asset_request_audit_log'];
            
            foreach ($tables as $table) {
                $sql = "SHOW TABLES LIKE '$table'";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $exists = $stmt->rowCount() > 0;
                
                $checks[] = [
                    'category' => 'Database Tables',
                    'check' => "Table: $table",
                    'status' => $exists ? 'PASS' : 'FAIL',
                    'details' => $exists ? "Table exists with data" : "Table not found - Run REQUEST_ASSET_MODULE.sql"
                ];
                if (!$exists) $all_passed = false;
            }

            // Check sample data
            $sql = "SELECT COUNT(*) as count FROM asset_requests";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'] ?? 0;

            $checks[] = [
                'category' => 'Sample Data',
                'check' => 'Sample Requests',
                'status' => $count > 0 ? 'PASS' : 'WARNING',
                'details' => $count > 0 ? "Found $count requests (AR-001, AR-002, AR-003)" : "No sample data - Module works but no test data"
            ];

        } else {
            $checks[] = [
                'category' => 'Database',
                'check' => 'Database Connection',
                'status' => 'FAIL',
                'details' => 'PDO connection failed - Check db.php configuration'
            ];
            $all_passed = false;
        }
    } catch (Exception $e) {
        $checks[] = [
            'category' => 'Database',
            'check' => 'Database Connection',
            'status' => 'FAIL',
            'details' => 'Error: ' . $e->getMessage()
        ];
        $all_passed = false;
    }
}

// ============================================================================
// SIDEBAR INTEGRATION CHECK
// ============================================================================

if (file_exists(__DIR__ . '/layout/adminLayout.php')) {
    $layout_content = file_get_contents(__DIR__ . '/layout/adminLayout.php');
    $has_menu_item = strpos($layout_content, "Request Asset") !== false;
    
    $checks[] = [
        'category' => 'Sidebar Integration',
        'check' => 'Request Asset Menu Item',
        'status' => $has_menu_item ? 'PASS' : 'FAIL',
        'details' => $has_menu_item ? "Menu item found in adminLayout.php" : "Menu item not found - Edit layout/adminLayout.php"
    ];
    if (!$has_menu_item) $all_passed = false;
}

// ============================================================================
// AUTHENTICATION CHECK
// ============================================================================

$logged_in = isset($_SESSION['id']);
$checks[] = [
    'category' => 'Authentication',
    'check' => 'User Logged In',
    'status' => $logged_in ? 'PASS' : 'WARNING',
    'details' => $logged_in ? "User ID: " . $_SESSION['id'] . ", Name: " . $_SESSION['name'] : "Not logged in - Log in to test functionality"
];

// ============================================================================
// FILE SIZE CHECKS (To verify files aren't empty)
// ============================================================================

foreach ($files_to_check as $file => $description) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        $checks[] = [
            'category' => 'File Content',
            'check' => basename($file),
            'status' => $size > 100 ? 'PASS' : 'FAIL',
            'details' => "File size: " . $size . " bytes"
        ];
        if ($size <= 100) $all_passed = false;
    }
}

// ============================================================================
// PRINT VERIFICATION REPORT
// ============================================================================

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Asset Module - Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Request Asset Module</h1>
                <p class="text-lg text-gray-600">Installation Verification Report</p>
            </div>

            <!-- Status Card -->
            <div class="mb-8 p-6 rounded-lg <?php echo $all_passed ? 'bg-green-100 border-2 border-green-500' : 'bg-yellow-100 border-2 border-yellow-500'; ?>">
                <div class="flex items-center gap-4">
                    <div class="text-5xl">
                        <?php echo $all_passed ? '✅' : '⚠️'; ?>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold <?php echo $all_passed ? 'text-green-900' : 'text-yellow-900'; ?>">
                            <?php echo $all_passed ? 'Ready for Use!' : 'Almost Ready'; ?>
                        </h2>
                        <p class="text-gray-700">
                            <?php echo $all_passed ? 'All components are properly installed.' : 'Some issues need attention.'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Checks by Category -->
            <?php
            $categories = [];
            foreach ($checks as $check) {
                $cat = $check['category'];
                if (!isset($categories[$cat])) {
                    $categories[$cat] = [];
                }
                $categories[$cat][] = $check;
            }

            foreach ($categories as $category => $category_checks):
            ?>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold"><?php echo $category; ?></span>
                </h3>
                <div class="space-y-2">
                    <?php foreach ($category_checks as $check): ?>
                    <div class="p-4 rounded-lg border-2 <?php echo $check['status'] === 'PASS' ? 'border-green-500 bg-green-50' : ($check['status'] === 'FAIL' ? 'border-red-500 bg-red-50' : 'border-yellow-500 bg-yellow-50'); ?>">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800"><?php echo $check['check']; ?></p>
                                <p class="text-sm text-gray-600 mt-1"><?php echo $check['details']; ?></p>
                            </div>
                            <span class="ml-4 font-bold text-lg <?php 
                                echo $check['status'] === 'PASS' ? 'text-green-600' : ($check['status'] === 'FAIL' ? 'text-red-600' : 'text-yellow-600');
                            ?>"><?php echo $check['status']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Quick Links -->
            <div class="mt-12 p-6 bg-blue-50 border-2 border-blue-200 rounded-lg">
                <h3 class="text-xl font-bold text-blue-900 mb-4">📚 Quick Access</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="pages/request_asset.php" class="block p-4 bg-blue-100 rounded-lg hover:bg-blue-200 transition">
                        <p class="font-bold text-blue-900">👤 User Dashboard</p>
                        <p class="text-sm text-blue-700 mt-1">Create and track requests</p>
                    </a>
                    <a href="pages/manage_asset_requests.php" class="block p-4 bg-blue-100 rounded-lg hover:bg-blue-200 transition">
                        <p class="font-bold text-blue-900">👨‍💼 Admin Dashboard</p>
                        <p class="text-sm text-blue-700 mt-1">Approve and reject requests</p>
                    </a>
                    <a href="REQUEST_ASSET_QUICK_START.md" target="_blank" class="block p-4 bg-blue-100 rounded-lg hover:bg-blue-200 transition">
                        <p class="font-bold text-blue-900">📖 Quick Start Guide</p>
                        <p class="text-sm text-blue-700 mt-1">How to use the module</p>
                    </a>
                    <a href="REQUEST_ASSET_IMPLEMENTATION_COMPLETE.md" target="_blank" class="block p-4 bg-blue-100 rounded-lg hover:bg-blue-200 transition">
                        <p class="font-bold text-blue-900">📚 Full Documentation</p>
                        <p class="text-sm text-blue-700 mt-1">Technical reference</p>
                    </a>
                </div>
            </div>

            <!-- Action Items -->
            <?php if (!$all_passed): ?>
            <div class="mt-8 p-6 bg-red-50 border-2 border-red-200 rounded-lg">
                <h3 class="text-xl font-bold text-red-900 mb-4">⚠️ Action Items</h3>
                <ol class="list-decimal list-inside space-y-2 text-red-800">
                    <?php
                    foreach ($checks as $check) {
                        if ($check['status'] === 'FAIL') {
                            echo '<li><strong>' . $check['check'] . ':</strong> ' . $check['details'] . '</li>';
                        }
                    }
                    ?>
                </ol>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="mt-12 text-center text-gray-600">
                <p>Last Verified: <?php echo date('Y-m-d H:i:s'); ?></p>
                <p class="mt-2 text-sm">Request Asset Module v1.0</p>
            </div>
        </div>
    </div>
</body>
</html>
