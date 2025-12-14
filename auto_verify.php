<?php
require_once 'config.php';
require_once 'database.php';

echo "<h2>🔐 Account Verification Status</h2>";

$db = new Database();
$conn = $db->connect();

// Get the latest unverified account
try {
    $sql = "SELECT id, username, email, verification_code, is_verified FROM admin WHERE is_verified = 0 ORDER BY created_at DESC LIMIT 1";
    $result = $conn->query($sql);
    $account = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($account) {
        echo "<h3>Unverified Account Found:</h3>";
        echo "<p><strong>Email:</strong> " . $account['email'] . "</p>";
        echo "<p><strong>Username:</strong> " . $account['username'] . "</p>";
        echo "<p><strong>Verification Code:</strong> <code>" . ($account['verification_code'] ?: 'None') . "</code></p>";
        
        // Auto-verify the account
        echo "<h3>Auto-Verifying Account...</h3>";
        $verify_sql = "UPDATE admin SET is_verified = 1 WHERE id = :id";
        $verify_query = $conn->prepare($verify_sql);
        $verify_query->bindParam(':id', $account['id']);
        
        if ($verify_query->execute()) {
            echo "<p>✅ <strong>Account verified successfully!</strong></p>";
            echo "<p>You can now login with:</p>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> " . $account['email'] . "</li>";
            echo "<li><strong>Password:</strong> The password you used during registration</li>";
            echo "</ul>";
            echo "<p><a href='login.php'>👉 Go to Login Page</a></p>";
        } else {
            echo "<p>❌ Failed to verify account</p>";
        }
    } else {
        echo "<p>✅ No unverified accounts found. All accounts are verified!</p>";
        
        // List all accounts
        echo "<h3>All Admin Accounts:</h3>";
        $all_sql = "SELECT id, username, email, is_verified FROM admin";
        $all_result = $conn->query($all_sql);
        $all_accounts = $all_result->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th style='border: 1px solid #ddd; padding: 8px;'>Username</th><th style='border: 1px solid #ddd; padding: 8px;'>Email</th><th style='border: 1px solid #ddd; padding: 8px;'>Verified</th></tr>";
        
        foreach ($all_accounts as $acc) {
            $verified = $acc['is_verified'] ? '✅' : '❌';
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $acc['username'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $acc['email'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $verified . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
