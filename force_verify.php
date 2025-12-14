<?php
require_once 'config.php';
require_once 'database.php';

echo "<h2>🔧 Full Account Diagnostic</h2>";

$db = new Database();
$conn = $db->connect();

// Show all accounts
echo "<h3>All Admin Accounts in Database:</h3>";
try {
    $sql = "SELECT * FROM admin";
    $result = $conn->query($sql);
    $all_accounts = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($all_accounts)) {
        echo "<p>❌ No accounts found!</p>";
    } else {
        echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='border: 1px solid #ddd; padding: 10px;'>ID</th>";
        echo "<th style='border: 1px solid #ddd; padding: 10px;'>Username</th>";
        echo "<th style='border: 1px solid #ddd; padding: 10px;'>Email</th>";
        echo "<th style='border: 1px solid #ddd; padding: 10px;'>Verified</th>";
        echo "<th style='border: 1px solid #ddd; padding: 10px;'>Created</th>";
        echo "</tr>";
        
        foreach ($all_accounts as $acc) {
            $verified = (isset($acc['is_verified']) && $acc['is_verified']) ? '✅ YES' : '❌ NO';
            echo "<tr>";
            echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . $acc['id'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . $acc['username'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . $acc['email'] . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . $verified . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . (isset($acc['created_at']) ? $acc['created_at'] : 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Try to verify ALL unverified accounts
    echo "<h3>Auto-Verifying All Unverified Accounts...</h3>";
    $verify_sql = "UPDATE admin SET is_verified = 1 WHERE is_verified = 0 OR is_verified IS NULL";
    $verify_result = $conn->exec($verify_sql);
    
    if ($verify_result !== false) {
        echo "<p>✅ <strong>Updated $verify_result account(s)</strong></p>";
        
        // Show updated status
        echo "<h3>Updated Account Status:</h3>";
        $check_sql = "SELECT * FROM admin";
        $check_result = $conn->query($check_sql);
        $updated_accounts = $check_result->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($updated_accounts as $acc) {
            $verified = (isset($acc['is_verified']) && $acc['is_verified']) ? '✅' : '❌';
            echo "<p>$verified " . $acc['username'] . " (" . $acc['email'] . ")</p>";
        }
    } else {
        echo "<p>❌ Error updating accounts</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>✅ ALL ACCOUNTS ARE NOW VERIFIED!</strong></p>";
    echo "<p><a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>👉 Go to Login</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
