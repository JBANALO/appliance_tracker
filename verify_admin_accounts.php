<?php
require_once 'config.php';
require_once 'database.php';

echo "<h2>🔧 Account Verification & Status Check</h2>";

$db = new Database();
$conn = $db->connect();

// Check if accounts exist and their verification status
$accounts = ['heidilynn', 'hz2023'];

foreach ($accounts as $username) {
    try {
        $sql = "SELECT id, username, email, is_verified FROM admin WHERE username = :username";
        $query = $conn->prepare($sql);
        $query->bindParam(':username', $username);
        $query->execute();
        
        $account = $query->fetch();
        
        if ($account) {
            echo "<p><strong>$username:</strong> Found in database</p>";
            echo "<ul>";
            echo "<li>Email: " . $account['email'] . "</li>";
            echo "<li>Verified: " . ($account['is_verified'] ? "✅ YES" : "❌ NO") . "</li>";
            echo "</ul>";
            
            // Verify if not already verified
            if (!$account['is_verified']) {
                $verify_sql = "UPDATE admin SET is_verified = 1 WHERE id = :id";
                $verify_query = $conn->prepare($verify_sql);
                $verify_query->bindParam(':id', $account['id']);
                
                if ($verify_query->execute()) {
                    echo "<p>✅ <strong>$username account has been verified!</strong></p>";
                }
            }
        } else {
            echo "<p>❌ <strong>$username:</strong> Not found in database (already deleted?)</p>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Error checking $username: " . $e->getMessage() . "</p>";
    }
}

echo "<p><strong>Done!</strong> Try logging in again.</p>";
?>
