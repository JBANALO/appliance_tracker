<?php
require_once 'config.php';
require_once 'database.php';

echo "<h2>🔍 Detailed Account Diagnostic</h2>";

$db = new Database();
$conn = $db->connect();

$email = 'heidilynnrubia09@gmail.com';

try {
    $sql = "SELECT * FROM admin WHERE email = :email";
    $query = $conn->prepare($sql);
    $query->bindParam(':email', $email);
    $query->execute();
    
    $account = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($account) {
        echo "<h3>✅ Account Found!</h3>";
        echo "<pre>";
        print_r($account);
        echo "</pre>";
        
        // Test password
        $test_password = "heidilynn123"; // You said you know the password
        $password_match = password_verify($test_password, $account['password']);
        
        echo "<h3>Password Test:</h3>";
        echo "<p>Test password: <strong>heidilynn123</strong></p>";
        echo "<p>Hash from DB: <strong>" . substr($account['password'], 0, 20) . "...</strong></p>";
        echo "<p>Match: " . ($password_match ? "✅ YES" : "❌ NO") . "</p>";
        
        echo "<h3>Verification Status:</h3>";
        echo "<p>is_verified: <strong>" . (isset($account['is_verified']) ? $account['is_verified'] : "NOT SET") . "</strong></p>";
        
    } else {
        echo "<h3>❌ Account Not Found!</h3>";
        echo "<p>Email: $email not in database</p>";
        
        // List all accounts
        echo "<h3>All Accounts in Database:</h3>";
        $sql = "SELECT id, username, email, is_verified FROM admin";
        $result = $conn->query($sql);
        $all_accounts = $result->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<pre>";
        print_r($all_accounts);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
