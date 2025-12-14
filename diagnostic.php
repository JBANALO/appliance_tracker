<?php
require_once 'config.php';

echo "<h2>🔍 SMTP Configuration Diagnostic</h2>";
echo "<pre>";
echo "Environment: " . APP_ENV . "\n";
echo "Debug Mode: " . (APP_DEBUG ? "ON" : "OFF") . "\n\n";

echo "=== SMTP Configuration ===\n";
echo "SMTP_HOST: [" . SMTP_HOST . "]\n";
echo "SMTP_PORT: [" . SMTP_PORT . "]\n";
echo "SMTP_USER: [" . SMTP_USER . "]\n";
echo "SMTP_PASS: [" . (SMTP_PASS ? "***SET***" : "***EMPTY***") . "]\n";
echo "SMTP_FROM_EMAIL: [" . SMTP_FROM_EMAIL . "]\n";
echo "SMTP_FROM_NAME: [" . SMTP_FROM_NAME . "]\n\n";

echo "=== Environment Variables (raw) ===\n";
echo "getenv('SMTP_HOST'): [" . getenv('SMTP_HOST') . "]\n";
echo "getenv('SMTP_USER'): [" . getenv('SMTP_USER') . "]\n";
echo "getenv('SMTP_PASS'): [" . getenv('SMTP_PASS') . "]\n\n";

echo "=== Network Test ===\n";
if (function_exists('gethostbyname')) {
    $ip = gethostbyname('smtp.gmail.com');
    echo "DNS Lookup (smtp.gmail.com): " . $ip . "\n";
}

if (function_exists('fsockopen')) {
    echo "Attempting socket connection to smtp.gmail.com:587...\n";
    $socket = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 5);
    if ($socket) {
        echo "✅ Socket connection SUCCESSFUL\n";
        fclose($socket);
    } else {
        echo "❌ Socket connection FAILED\n";
        echo "Error: $errstr ($errno)\n";
    }
}

echo "</pre>";
?>
