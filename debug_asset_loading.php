<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'user_logged_in' => isset($_SESSION['id']),
    'session_id' => $_SESSION['id'] ?? 'NOT SET',
    'checks' => []
];

// Check 1: Database Connection
try {
    require_once 'api/db.php';
    if ($conn) {
        $debug['checks']['database_connection'] = [
            'status' => 'SUCCESS',
            'message' => 'Connected to database'
        ];
    } else {
        $debug['checks']['database_connection'] = [
            'status' => 'FAILED',
            'message' => 'Database connection is NULL'
        ];
    }
} catch (Exception $e) {
    $debug['checks']['database_connection'] = [
        'status' => 'ERROR',
        'message' => $e->getMessage()
    ];
}

// Check 2: Table Exists
if ($conn) {
    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE 'asset_management'");
        $stmt->execute();
        $tableExists = $stmt->rowCount() > 0;
        
        $debug['checks']['table_exists'] = [
            'status' => $tableExists ? 'EXISTS' : 'NOT FOUND',
            'table_name' => 'asset_management'
        ];
    } catch (Exception $e) {
        $debug['checks']['table_exists'] = [
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ];
    }
    
    // Check 3: Count Assets
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM asset_management");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'] ?? 0;
        
        $debug['checks']['asset_count'] = [
            'status' => 'SUCCESS',
            'count' => intval($count),
            'message' => $count > 0 ? "$count assets found" : 'No assets in table'
        ];
    } catch (Exception $e) {
        $debug['checks']['asset_count'] = [
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ];
    }
    
    // Check 4: Fetch Sample Data
    try {
        $stmt = $conn->prepare("SELECT * FROM asset_management LIMIT 5");
        $stmt->execute();
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $debug['checks']['sample_data'] = [
            'status' => 'SUCCESS',
            'count' => count($assets),
            'data' => $assets
        ];
    } catch (Exception $e) {
        $debug['checks']['sample_data'] = [
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ];
    }
    
    // Check 5: Test API Query
    try {
        $sql = "
            SELECT *,
                CASE 
                    WHEN purchase_date IS NULL THEN ROUND(100 * COALESCE(quality_multiplier, 1.0))
                    ELSE GREATEST(
                        0,
                        ROUND(
                            (100 - (
                                (TIMESTAMPDIFF(DAY, purchase_date, CURDATE()) 
                                / (lifespan_years * 365)) * 100
                            )) * COALESCE(quality_multiplier, 1.0)
                        )
                    )
                END AS quality_percent
            FROM asset_management
            ORDER BY date DESC
            LIMIT 5
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $apiData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $debug['checks']['api_query'] = [
            'status' => 'SUCCESS',
            'count' => count($apiData),
            'data' => $apiData
        ];
    } catch (Exception $e) {
        $debug['checks']['api_query'] = [
            'status' => 'ERROR',
            'message' => $e->getMessage(),
            'sql' => $sql ?? 'Not set'
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .debug-card {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .success { border-left-color: #28a745; }
        .error { border-left-color: #dc3545; }
        .warning { border-left-color: #ffc107; }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 400px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-6 text-gray-800">🔍 Asset Management Debug Report</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Session Info</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600 text-sm">User Logged In:</p>
                    <p class="text-lg font-semibold"><?= $debug['user_logged_in'] ? '✅ YES' : '❌ NO' ?></p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Session ID:</p>
                    <p class="text-lg font-mono font-semibold"><?= $debug['session_id'] ?></p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Timestamp:</p>
                    <p class="text-lg font-semibold"><?= $debug['timestamp'] ?></p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <?php foreach ($debug['checks'] as $checkName => $checkData): ?>
                <?php
                    $statusClass = match($checkData['status']) {
                        'SUCCESS', 'EXISTS' => 'success',
                        'ERROR' => 'error',
                        default => 'warning'
                    };
                ?>
                <div class="debug-card <?= $statusClass ?> bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold text-gray-800">
                            <?= ucwords(str_replace('_', ' ', $checkName)) ?>
                        </h3>
                        <span class="status-badge status-<?= strtolower($statusClass) ?>">
                            <?= $checkData['status'] ?>
                        </span>
                    </div>

                    <?php if (isset($checkData['message'])): ?>
                        <p class="text-gray-700 mb-4"><?= htmlspecialchars($checkData['message']) ?></p>
                    <?php endif; ?>

                    <?php if (isset($checkData['count'])): ?>
                        <p class="text-gray-700 mb-4"><strong>Count:</strong> <?= $checkData['count'] ?></p>
                    <?php endif; ?>

                    <?php if (isset($checkData['table_name'])): ?>
                        <p class="text-gray-700 mb-4"><strong>Table:</strong> <?= $checkData['table_name'] ?></p>
                    <?php endif; ?>

                    <?php if (isset($checkData['data']) && !empty($checkData['data'])): ?>
                        <div class="mt-4">
                            <p class="text-gray-700 font-semibold mb-2">Data Preview:</p>
                            <pre><?= htmlspecialchars(json_encode($checkData['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($checkData['sql'])): ?>
                        <div class="mt-4">
                            <p class="text-gray-700 font-semibold mb-2">SQL:</p>
                            <pre><?= htmlspecialchars($checkData['sql']) ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
            <h3 class="text-lg font-bold text-blue-800 mb-3">📋 API Test</h3>
            <p class="text-blue-700 mb-4">Test the actual API endpoint:</p>
            <button onclick="testAPI()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Test API Now
            </button>
            <div id="apiResult" class="mt-4 hidden">
                <pre id="apiOutput" class="bg-gray-900 text-green-400"></pre>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mt-8">
            <h3 class="text-lg font-bold text-yellow-800 mb-3">⚙️ Browser Console Check</h3>
            <p class="text-yellow-700 mb-4">Open your browser's Developer Tools (F12) and check the Console tab for errors.</p>
            <p class="text-sm text-yellow-600">Look for: network errors, CORS issues, or JavaScript errors</p>
        </div>

        <div class="text-center mt-8">
            <a href="pages/asset_management.php" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 inline-block">
                Go Back to Asset Management
            </a>
        </div>
    </div>

    <script>
        async function testAPI() {
            const resultDiv = document.getElementById('apiResult');
            const output = document.getElementById('apiOutput');
            
            output.textContent = 'Loading...';
            resultDiv.classList.remove('hidden');
            
            try {
                const response = await fetch('api/asset_management.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                console.log('API Response Status:', response.status);
                const data = await response.json();
                console.log('API Response Data:', data);
                
                output.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                console.error('API Test Error:', error);
                output.textContent = `ERROR: ${error.message}\n\nStack: ${error.stack}`;
            }
        }

        // Auto log to console
        console.log('Asset Management Debug Page Loaded');
        console.log('Session Check:', <?= json_encode($debug['session_id'] !== 'NOT SET') ?>);
    </script>
</body>
</html>
