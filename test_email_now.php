<?php
require_once 'config.php';
require_once 'EmailNotification.php';

echo "<h2>📧 Test Email Sending</h2>";

echo "<h3>Configuration:</h3>";
echo "<p><strong>SMTP_HOST:</strong> " . SMTP_HOST . "</p>";
echo "<p><strong>SMTP_PORT:</strong> " . SMTP_PORT . "</p>";
echo "<p><strong>SMTP_USER:</strong> " . SMTP_USER . "</p>";
echo "<p><strong>SMTP_FROM_EMAIL:</strong> " . SMTP_FROM_EMAIL . "</p>";

echo "<h3>Sending Test Email...</h3>";

try {
    $emailNotif = new EmailNotification();
    
    $result = $emailNotif->sendClaimConfirmationEmail(
        SMTP_USER,  // Send to the same email
        "Test User",
        "Test Appliance",
        12345,
        date('F d, Y')
    );
    
    if ($result) {
        echo "<p>✅ <strong>Email sent successfully!</strong></p>";
        echo "<p>Check your inbox at: <strong>" . SMTP_USER . "</strong></p>";
    } else {
        echo "<p>❌ <strong>Email sending failed.</strong></p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='admin_dashboard.php'>👈 Back to Dashboard</a></p>";
?>
