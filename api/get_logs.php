<?php
// API endpoint for reading and clearing logs
header('Content-Type: application/json');

$logsDir = __DIR__ . '/../logs';
$authLogFile = $logsDir . '/auth.log';
$mailLogFile = $logsDir . '/mail.log';

$action = $_GET['action'] ?? 'read';

if ($action === 'clear') {
    @unlink($authLogFile);
    @unlink($mailLogFile);
    echo json_encode(['status' => 'cleared']);
    exit;
}

// Default: read logs
$authLog = file_exists($authLogFile) ? file_get_contents($authLogFile) : '';
$mailLog = file_exists($mailLogFile) ? file_get_contents($mailLogFile) : '';

// Return last 50 lines of each
$authLines = array_slice(explode("\n", $authLog), -50);
$mailLines = array_slice(explode("\n", $mailLog), -50);

echo json_encode([
    'auth' => implode("\n", $authLines),
    'mail' => implode("\n", $mailLines),
    'timestamp' => date('Y-m-d H:i:s')
]);

?>
