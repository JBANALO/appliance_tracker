<?php
/**
 * Test Forgot Password Functionality
 * This file helps diagnose issues with the password reset email system
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/EmailNotification.php';

echo "<h1>Forgot Password System Test</h1>";
echo "<hr>";

// 1. Check Database Configuration
echo "<h2>1. Database Configuration</h2>";
echo "<p><strong>DB_HOST:</strong> " . DB_HOST . "</p>";
echo "<p><strong>DB_NAME:</strong> " . DB_NAME . "</p>";
echo "<p><strong>DB_USER:</strong> " . DB_USER . "</p>";

// 2. Test Database Connection
echo "<h2>2. Database Connection Test</h2>";
try {
    $db = new Database();
    $conn = $db->connect();
    echo "<p style='color: green;'><strong>✓ Database connection successful!</strong></p>";
    
    // Check if admin table exists and has data
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM admin");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Admin accounts in database:</strong> " . $result['count'] . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>✗ Database connection failed!</strong></p>";
    echo "<p>" . $e->getMessage() . "</p>";
}

// 3. Check SMTP Configuration
echo "<h2>3. SMTP Configuration</h2>";
echo "<p><strong>SMTP_HOST:</strong> " . SMTP_HOST . "</p>";
echo "<p><strong>SMTP_PORT:</strong> " . SMTP_PORT . "</p>";
echo "<p><strong>SMTP_USER:</strong> " . (SMTP_USER ?: "NOT CONFIGURED") . "</p>";
echo "<p><strong>SMTP_PASS:</strong> " . (SMTP_PASS ? "***configured***" : "NOT CONFIGURED") . "</p>";
echo "<p><strong>SMTP_FROM_EMAIL:</strong> " . SMTP_FROM_EMAIL . "</p>";

if (!SMTP_USER || !SMTP_PASS) {
    echo "<p style='color: orange;'><strong>⚠ WARNING: SMTP credentials not fully configured!</strong></p>";
    echo "<p>To fix this:</p>";
    echo "<ol>";
    echo "<li>Edit the <strong>.env</strong> file in the root folder</li>";
    echo "<li>Update SMTP_USER and SMTP_PASS with your Gmail credentials</li>";
    echo "<li>For Gmail, use an <a href='https://myaccount.google.com/apppasswords' target='_blank'>App Password</a> (not your regular Gmail password)</li>";
    echo "</ol>";
}

// 4. Test Email Sending (if credentials are configured)
echo "<h2>4. Email Sending Test</h2>";
if (SMTP_USER && SMTP_PASS && SMTP_USER !== 'your_email@gmail.com') {
    echo "<form method='POST'>";
    echo "<label for='test_email'>Test Email Address:</label>";
    echo "<input type='email' name='test_email' id='test_email' required>";
    echo "<button type='submit' name='send_test'>Send Test Email</button>";
    echo "</form>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
        $test_email = htmlspecialchars($_POST['test_email']);
        $emailNotif = new EmailNotification();
        
        $test_message = "
            <h2>Test Email</h2>
            <p>This is a test email to verify your SMTP configuration is working correctly.</p>
            <p><strong>If you received this, your email system is working!</strong></p>
        ";
        
        if ($emailNotif->sendEmail($test_email, "Test User", "Password Reset Test - Warranty Tracker", $test_message)) {
            echo "<p style='color: green;'><strong>✓ Test email sent successfully to " . htmlspecialchars($test_email) . "!</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>✗ Failed to send test email.</strong></p>";
            echo "<p>Check your SMTP credentials and try again.</p>";
        }
    }
} else {
    echo "<p style='color: orange;'><strong>⚠ SMTP not configured. Cannot test email sending.</strong></p>";
    echo "<p>Please configure SMTP credentials in the .env file first.</p>";
}

// 5. System Status Summary
echo "<h2>5. Summary</h2>";
$issues = [];
if (!SMTP_USER || !SMTP_PASS || SMTP_USER === 'your_email@gmail.com') {
    $issues[] = "SMTP credentials not configured";
}
if (DB_NAME !== 'warranty_trackerr') {
    $issues[] = "Database name should be 'warranty_trackerr'";
}

if (empty($issues)) {
    echo "<p style='color: green;'><strong>✓ All systems configured correctly!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>Issues found:</strong></p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>" . $issue . "</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='forgot_password.php'>← Back to Forgot Password</a></p>";
?>
