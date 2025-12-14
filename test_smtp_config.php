<?php
// Test SMTP Configuration
require_once "config.php";

echo "<h2>SMTP Configuration Check</h2>";
echo "<pre>";
echo "SMTP_HOST: " . (SMTP_HOST ?: "NOT SET") . "\n";
echo "SMTP_PORT: " . (SMTP_PORT ?: "NOT SET") . "\n";
echo "SMTP_USER: " . (SMTP_USER ?: "NOT SET") . "\n";
echo "SMTP_PASS: " . (SMTP_PASS ? "***HIDDEN***" : "NOT SET") . "\n";
echo "SMTP_FROM_EMAIL: " . (SMTP_FROM_EMAIL ?: "NOT SET") . "\n";
echo "SMTP_FROM_NAME: " . (SMTP_FROM_NAME ?: "NOT SET") . "\n";
echo "</pre>";

// Try sending test email
if (SMTP_HOST && SMTP_USER) {
    echo "<h3>Attempting test email...</h3>";
    require_once "EmailNotification.php";
    $email = new EmailNotification();
    $result = $email->sendAdminVerificationEmail("josiebanalo977@gmail.com", "Test User", "123456");
    if ($result) {
        echo "<p style='color: green;'>✅ Email sent successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Email send failed</p>";
    }
} else {
    echo "<p style='color: red;'>❌ SMTP variables not configured</p>";
}
?>
