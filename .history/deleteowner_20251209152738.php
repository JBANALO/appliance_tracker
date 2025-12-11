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
<html>
<head>
    <meta charset="utf-8">
    <title>Delete Owner</title>
</head>
<body>
    <h1>Delete Owner</h1>
    <a href="viewowner.php">Back to List</a>

    <div class="warning">
        <p>Are you sure you want to delete this owner?</p>
        <p><strong><?= htmlspecialchars($owner_name) ?></strong></p>
    </div>

    <form action="" method="post">
        <input type="hidden" name="id" value="<?= $owner_id ?>">
        <button type="submit" class="delete-btn">Yes, Delete</button>
        <a href="viewowner.php"><button type="button">Cancel</button></a>
    </form>
</body>
</html>