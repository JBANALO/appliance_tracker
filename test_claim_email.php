<?php
/**
 * Test Warranty Claim Email Notification
 * This file helps verify that warranty claim confirmation emails are being sent
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/EmailNotification.php';

echo "<h1>Warranty Claim Email Test</h1>";
echo "<hr>";

// 1. Check Database Configuration
echo "<h2>1. System Status</h2>";
echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
echo "<p><strong>SMTP User:</strong> " . SMTP_USER . "</p>";

// 2. Test Database Connection
echo "<h2>2. Recent Claims</h2>";
try {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("
        SELECT c.id, c.claim_date, c.claim_description, c.claim_status,
               a.appliance_name, o.owner_name, o.email
        FROM claim c
        JOIN appliance a ON c.appliance_id = a.id
        JOIN owner o ON a.owner_id = o.id
        ORDER BY c.id DESC
        LIMIT 5
    ");
    
    if ($stmt->execute()) {
        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($claims)) {
            echo "<table border='1' cellpadding='10' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr style='background: #667eea; color: white;'>";
            echo "<th>Claim ID</th>";
            echo "<th>Owner</th>";
            echo "<th>Email</th>";
            echo "<th>Appliance</th>";
            echo "<th>Status</th>";
            echo "<th>Action</th>";
            echo "</tr>";
            
            foreach ($claims as $claim) {
                echo "<tr>";
                echo "<td>#{$claim['id']}</td>";
                echo "<td>" . htmlspecialchars($claim['owner_name']) . "</td>";
                echo "<td>" . htmlspecialchars($claim['email']) . "</td>";
                echo "<td>" . htmlspecialchars($claim['appliance_name']) . "</td>";
                echo "<td><strong>{$claim['claim_status']}</strong></td>";
                echo "<td>";
                echo "<form method='POST' style='display: inline;'>";
                echo "<input type='hidden' name='claim_id' value='{$claim['id']}'>";
                echo "<input type='hidden' name='owner_email' value='" . htmlspecialchars($claim['email']) . "'>";
                echo "<input type='hidden' name='owner_name' value='" . htmlspecialchars($claim['owner_name']) . "'>";
                echo "<input type='hidden' name='appliance_name' value='" . htmlspecialchars($claim['appliance_name']) . "'>";
                echo "<input type='hidden' name='claim_date' value='" . htmlspecialchars($claim['claim_date']) . "'>";
                echo "<button type='submit' name='resend_email' style='padding: 5px 10px; background: #667eea; color: white; border: none; border-radius: 3px; cursor: pointer;'>📧 Resend Email</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'><strong>⚠ No claims found in the database.</strong></p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>✗ Database error:</strong> " . $e->getMessage() . "</p>";
}

// 3. Resend Email Test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_email'])) {
    $claim_id = $_POST['claim_id'];
    $owner_email = $_POST['owner_email'];
    $owner_name = $_POST['owner_name'];
    $appliance_name = $_POST['appliance_name'];
    $claim_date = $_POST['claim_date'];
    
    echo "<h2>3. Email Resend Result</h2>";
    
    $emailObj = new EmailNotification();
    
    try {
        $result = $emailObj->sendClaimConfirmationEmail(
            $owner_email,
            $owner_name,
            $appliance_name,
            $claim_id,
            date('F d, Y', strtotime($claim_date))
        );
        
        if ($result) {
            echo "<p style='color: green;'><strong>✓ Email sent successfully!</strong></p>";
            echo "<p><strong>Sent to:</strong> " . htmlspecialchars($owner_email) . "</p>";
            echo "<p><strong>Owner:</strong> " . htmlspecialchars($owner_name) . "</p>";
            echo "<p><strong>Claim ID:</strong> #{$claim_id}</p>";
        } else {
            echo "<p style='color: red;'><strong>✗ Email sending failed.</strong></p>";
            echo "<p>Check your SMTP configuration in the .env file.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>✗ Email error:</strong> " . $e->getMessage() . "</p>";
    }
}

// 4. Manual Email Test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test_email'])) {
    $test_email = $_POST['test_email'];
    $test_name = $_POST['test_name'];
    
    echo "<h2>3. Test Email Result</h2>";
    
    $emailObj = new EmailNotification();
    
    try {
        $result = $emailObj->sendClaimConfirmationEmail(
            $test_email,
            $test_name,
            "Test Appliance",
            "TEST123",
            date('F d, Y')
        );
        
        if ($result) {
            echo "<p style='color: green;'><strong>✓ Test email sent successfully to " . htmlspecialchars($test_email) . "!</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>✗ Test email sending failed.</strong></p>";
            echo "<p>Check your SMTP configuration in the .env file.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>✗ Email error:</strong> " . $e->getMessage() . "</p>";
    }
}
?>

<div style="margin-top: 40px; padding: 20px; background: #f0f0f0; border-radius: 5px;">
    <h2>Send Test Email Manually</h2>
    <form method="POST" style="max-width: 400px;">
        <div style="margin-bottom: 15px;">
            <label for="test_email"><strong>Email Address:</strong></label><br>
            <input type="email" name="test_email" id="test_email" style="width: 100%; padding: 8px; margin-top: 5px;" required>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="test_name"><strong>Owner Name:</strong></label><br>
            <input type="text" name="test_name" id="test_name" placeholder="e.g., John Doe" style="width: 100%; padding: 8px; margin-top: 5px;" required>
        </div>
        
        <button type="submit" name="send_test_email" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; width: 100%;">
            📧 Send Test Email
        </button>
    </form>
</div>

<hr style="margin-top: 40px;">
<p><a href="customer_warranty_tracker.php">← Back to Warranty Tracker</a></p>
