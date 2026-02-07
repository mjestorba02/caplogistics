<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Lightweight mailer wrapper that uses PHPMailer if available, otherwise falls back to mail()
// Usage: require 'api/mailer.php'; $res = sendMail($to, $subject, $body, $fromName, $fromEmail);

function sendMail($to, $subject, $body, $fromName = '', $fromEmail = '') {
    $logFile = __DIR__ . '/../logs/mail.log';
    
    // Try to load config if exists
    $config = [];
    $configPath = __DIR__ . '/email_config.php';
    if (file_exists($configPath)) {
        $config = include $configPath;
    }

    // Check if PHPMailer is available and prefer it
    $usedMethod = 'mail()';
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $usedMethod = 'PHPMailer (SMTP)';
                $mail = new PHPMailer(true);

                // Gmail SMTP Configuration with timeout
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'emjey.estorba.02@gmail.com';
                $mail->Password   = 'xnzhjzscjppafbos';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->SMTPDebug  = 0;
                $mail->Timeout    = 3; // 3 second timeout to prevent hanging

                $fromEmailFinal = 'cranecalimanagementsystem@gmail.com';
                $fromNameFinal = $fromName ?: 'E-Commerce Logistics Management Portal';

                $mail->setFrom($fromEmailFinal, $fromNameFinal);
                $mail->addAddress($to);
                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body = $body;

                $mail->send();
                
                // Log success
                logMail($logFile, "[SUCCESS] PHPMailer sent to $to | Subject: $subject");
                return ['success' => true, 'method' => $usedMethod];
            } catch (Exception $e) {
                $error = $e->getMessage();
                logMail($logFile, "[ERROR] PHPMailer failed: $error | To: $to");
                return ['success' => false, 'error' => $error, 'method' => 'PHPMailer (FAILED)'];
            }
        }
    }

    // Fallback to PHP mail()
    $from = $fromEmail ? $fromEmail : 'cranecalimanagementsystem@gmail.com';
    $fromNameFinal = $fromName ? $fromName : 'E-Commerce Logistics Management Portal';
    $headers = sprintf("From: %s <%s>\r\n", $fromNameFinal, $from);
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $ok = @mail($to, $subject, $body, $headers);
    if ($ok) {
        logMail($logFile, "[SUCCESS] mail() sent to $to | Subject: $subject");
        return ['success' => true, 'method' => $usedMethod];
    }
    
    $error = 'mail() failed or not configured';
    logMail($logFile, "[ERROR] mail() failed to $to: $error");
    return ['success' => false, 'error' => $error, 'method' => $usedMethod];
}

/**
 * Log mail delivery attempts to file for debugging
 */
function logMail($logFile, $message) {
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    $logMsg = "[$timestamp] $message\n";
    @file_put_contents($logFile, $logMsg, FILE_APPEND);
}

?>
