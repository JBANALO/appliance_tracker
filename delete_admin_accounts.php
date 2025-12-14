<?php
require_once 'config.php';
require_once 'database.php';

echo "<h2>🗑️ Delete All Admin Accounts (Except Josie)</h2>";

$db = new Database();
$conn = $db->connect();

// Delete all accounts EXCEPT josie
try {
    $sql = "DELETE FROM admin WHERE username != :josie AND email != :josie_email";
    $query = $conn->prepare($sql);
    $query->bindParam(':josie', 'jossie');
    $query->bindParam(':josie_email', 'josiebanalo977@gmail.com');
    
    if ($query->execute()) {
        $deleted_count = $query->rowCount();
        echo "<p>✅ Deleted <strong>$deleted_count</strong> admin account(s)</p>";
        echo "<p>✅ Kept: <strong>jossie</strong> (josiebanalo977@gmail.com)</p>";
    } else {
        echo "<p>❌ Failed to delete accounts</p>";
    }
} catch (Exception $e) {
    echo "<p>⚠️ Error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Done!</strong> All accounts deleted except josie.</p>";
?>
