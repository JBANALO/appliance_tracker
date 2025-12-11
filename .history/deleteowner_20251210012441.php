<?php
require_once "database.php";
require_once "Owner.php";

$ownerObj = new Owner();

if (isset($_GET["id"])) {
    $owner_id = trim(htmlspecialchars($_GET["id"]));
    $ownerData = $ownerObj->getOwnerById($owner_id);
    
    if ($ownerData) {
        $owner_name = $ownerData["owner_name"];
    } else {
        echo "Owner not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_id = trim(htmlspecialchars($_POST["id"] ?? ""));
    
    if ($ownerObj->deleteOwner($owner_id)) {
        header("Location: viewowner.php");
        exit;
    } else {
        echo "Failed to delete owner.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Owner</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-times"></i> Delete Owner</h1>
            <a href="viewowner.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <div class="warning-box" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
            <i class="fas fa-exclamation-triangle" style="color: #ffc107; font-size: 24px; margin-bottom: 10px;"></i>
            <p style="margin: 10px 0;"><strong>Warning:</strong> This action cannot be undone.</p>
            <p style="margin: 10px 0; font-size: 18px; color: #333;"><strong><?= htmlspecialchars($owner_name) ?></strong></p>
        </div>

        <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this owner?');">
            <input type="hidden" name="id" value="<?= $owner_id ?>">
            <div class="button-group">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i>
                    Yes, Delete Owner
                </button>
                <a href="viewowner.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>