<?php
/**
 * SMTP Configuration Diagnostic
 * Verify that Gmail SMTP credentials are loaded correctly
 */

require_once __DIR__ . '/config.php';

echo "<h1>📧 SMTP Configuration Diagnostic</h1>";
echo "<hr>";

echo "<h2>1. Loaded Configuration Values</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #667eea; color: white;'><th>Setting</th><th>Value</th><th>Status</th></tr>";

$configs = [
    'SMTP_HOST' => ['expected' => 'smtp.gmail.com', 'sensitive' => false],
    'SMTP_PORT' => ['expected' => '587', 'sensitive' => false],
    'SMTP_USER' => ['expected' => 'josiebanalo977@gmail.com', 'sensitive' => false],
    'SMTP_PASS' => ['expected' => 'hipoyasscbekuscir', 'sensitive' => true],
    'SMTP_FROM_EMAIL' => ['expected' => 'josiebanalo977@gmail.com', 'sensitive' => false],
    'SMTP_FROM_NAME' => ['expected' => 'Warranty Tracker', 'sensitive' => false],
];

foreach ($configs as $key => $info) {
    $value = constant($key);
    $expected = $info['expected'];
    $sensitive = $info['sensitive'];
    $display = $sensitive ? (empty($value) ? '(empty)' : '***' . substr($value, -4)) : $value;
    $status = ($value === $expected) ? '<span style="color: green;">✓ OK</span>' : '<span style="color: orange;">⚠ CHECK</span>';
    
    echo "<tr>";
    echo "<td><strong>{$key}</strong></td>";
    echo "<td><code>{$display}</code></td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>2. .env File Contents</h2>";
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    echo "<p style='color: green;'><strong>✓ .env file found</strong></p>";
    $env_content = file_get_contents($env_path);
    
    // Show only SMTP and DB lines
    $lines = explode("\n", $env_content);
    echo "<pre style='background: #f0f0f0; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    foreach ($lines as $line) {
        if (preg_match('/^(SMTP_|DB_)/i', trim($line)) && !preg_match('/#/', $line)) {
            // Hide password
            if (strpos($line, 'SMTP_PASS') !== false) {
                echo "SMTP_PASS=***hidden***\n";
            } else {
                echo htmlspecialchars($line) . "\n";
            }
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color: red;'><strong>✗ .env file NOT found</strong></p>";
}

echo "<h2>3. Test Email Connection</h2>";
echo "<form method='POST'>";
echo "<label for='test_email'><strong>Send test email to:</strong></label><br>";
echo "<input type='email' name='test_email' id='test_email' placeholder='your.email@example.com' style='padding: 8px; width: 300px;'>";
echo "<button type='submit' name='test_smtp' style='padding: 8px 20px; margin-left: 10px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;'>Test SMTP</button>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_smtp'])) {
    $test_email = $_POST['test_email'];
    
    echo "<h2>4. Test Result</h2>";
    
    if (empty($test_email)) {
        echo "<p style='color: red;'><strong>✗ Email address is required</strong></p>";
    } elseif (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color: red;'><strong>✗ Invalid email address</strong></p>";
    } else {
        require_once __DIR__ . '/EmailNotification.php';
        
        $emailObj = new EmailNotification();
        
        try {
            $result = $emailObj->sendEmail(
                $test_email,
                'Test User',
                'SMTP Configuration Test',
                '<h2>Test Email</h2><p>If you received this, your SMTP configuration is working correctly!</p>'
            );
            
            if ($result) {
                echo "<p style='color: green; font-size: 18px;'><strong>✓ Email sent successfully!</strong></p>";
                echo "<p>Check <strong>" . htmlspecialchars($test_email) . "</strong> for the test email.</p>";
            } else {
                echo "<p style='color: red;'><strong>✗ Email sending failed</strong></p>";
                echo "<p>Check SMTP credentials and Gmail account settings.</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'><strong>✗ Exception occurred:</strong></p>";
            echo "<p><code>" . htmlspecialchars($e->getMessage()) . "</code></p>";
        }
    }
}

echo "<hr>";
echo "<p><a href='forgot_password.php'>← Back to Forgot Password</a></p>";
?>
