<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "owner.php";

$ownerObj = new Owner();
$search = "";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $search = isset($_GET["search"]) ? trim(htmlspecialchars($_GET["search"])) : "";
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Owners</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Owners</h1>
        
        <div class="nav-links">
            <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
            <a href="addowner.php">Add Owner</a>
            <a href="viewappliance.php">View Appliances</a>
        </div>
        
        <form action="" method="get" style="margin: 20px 0;">
            <label for="search">Search:</label>
            <input type="search" name="search" id="search" value="<?= $search ?>">
            <input type="submit" value="Search">
        </form>

        <table>
            <tr>
                <th>No.</th>
                <th>Owner Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>City</th>
                <th>State</th>
                <th>Actions</th>
            </tr>
            <?php
            $no = 1;
            $owners = $ownerObj->viewOwner($search);
            foreach ($owners as $owner) {
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($owner["owner_name"]) ?></td>
                <td><?= htmlspecialchars($owner["email"]) ?></td>
                <td><?= htmlspecialchars($owner["phone"]) ?></td>
                <td><?= htmlspecialchars($owner["city"]) ?></td>
                <td><?= htmlspecialchars($owner["state"]) ?></td>
                <td>
                    <button class="action-btn view-btn">
                        <a href="viewownerdetails.php?id=<?= $owner['id'] ?>">View</a>
                    </button>
                    <button class="action-btn edit-btn">
                        <a href="editowner.php?id=<?= $owner['id'] ?>">Edit</a>
                    </button>
                    <button class="action-btn delete-btn">
                        <a href="deleteowner.php?id=<?= $owner['id'] ?>">Delete</a>
                    </button>
                </td>
            </tr>
            <?php
            }
            if (empty($owners)) {
                echo "<tr><td colspan='7' style='text-align:center;'>No owners found.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>