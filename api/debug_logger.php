<?php
/**
 * Simple debug logger for auth and OTP flow
 * Logs to logs/auth.log
 */

function debugLog($message, $context = []) {
    $logsDir = __DIR__ . '/../logs';
    if (!is_dir($logsDir)) {
        @mkdir($logsDir, 0755, true);
    }
    
    $logFile = $logsDir . '/auth.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $contextStr = '';
    if (!empty($context)) {
        $contextStr = ' | ' . json_encode($context);
    }
    
    $logMsg = "[$timestamp] [$ip] $message$contextStr\n";
    
    @file_put_contents($logFile, $logMsg, FILE_APPEND);
}

function debugLogRequest() {
    $input = json_decode(file_get_contents("php://input"), true);
    debugLog('REQUEST', [
        'action' => $input['action'] ?? null,
        'email' => isset($input['email']) ? substr($input['email'], 0, 5) . '***' : null,
        'has_otp' => !empty($input['otp_code']),
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
    ]);
}

?>
