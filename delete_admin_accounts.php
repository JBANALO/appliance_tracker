<?php
require_once 'config.php';
require_once 'database.php';

echo "<h2>🗑️ Delete Admin Accounts</h2>";

$db = new Database();
$conn = $db->connect();

// Accounts to delete
$accounts_to_delete = ['heidilynn', 'hz2023'];

foreach ($accounts_to_delete as $username) {
    try {
        $sql = "DELETE FROM admin WHERE username = :username";
        $query = $conn->prepare($sql);
        $query->bindParam(':username', $username);
        
        if ($query->execute()) {
            echo "<p>✅ Deleted: <strong>$username</strong></p>";
        } else {
            echo "<p>❌ Failed to delete: <strong>$username</strong></p>";
        }
    } catch (Exception $e) {
        echo "<p>⚠️ Error deleting $username: " . $e->getMessage() . "</p>";
    }
}

echo "<p><strong>Done!</strong> Refresh Railway admin panel to verify.</p>";
?>
