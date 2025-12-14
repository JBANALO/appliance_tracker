<?php
require_once 'config.php';
require_once 'EmailNotification.php';

echo "=== Email Configuration Test ===\n\n";

echo "SMTP_HOST: " . (SMTP_HOST ?: "❌ NOT SET") . "\n";
echo "SMTP_PORT: " . SMTP_PORT . "\n";
echo "SMTP_USER: " . (SMTP_USER ?: "❌ NOT SET") . "\n";
echo "SMTP_PASS: " . (SMTP_PASS ? "✓ SET" : "❌ NOT SET") . "\n";
echo "SMTP_FROM_EMAIL: " . SMTP_FROM_EMAIL . "\n";
echo "SMTP_FROM_NAME: " . SMTP_FROM_NAME . "\n\n";

if (!SMTP_HOST || !SMTP_USER || !SMTP_PASS) {
    echo "❌ ERROR: SMTP credentials are not properly configured!\n";
    exit(1);
}

echo "=== Attempting to send test email ===\n";

$emailNotif = new EmailNotification();

// Test email
$test_email = SMTP_USER; // Send to the same email
$test_name = "Test User";
$test_subject = "Test Email - Warranty Tracker";
$test_message = "<p>This is a test email from your Warranty Tracker application.</p><p>If you received this, email notifications are working!</p>";

$result = $emailNotif->sendEmail($test_email, $test_name, $test_subject, $test_message);

if ($result) {
    echo "✅ Email sent successfully!\n";
    echo "Check your inbox at: " . $test_email . "\n";
} else {
    echo "❌ Failed to send email.\n";
    echo "Check error.log for details.\n";
}
?>
