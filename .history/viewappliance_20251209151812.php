<?php
session_start();


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "appliance.php";

$applianceObj = new Appliance();
$search = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $search = isset($_GET["search"]) ? trim(htmlspecialchars($_GET["search"])) : "";
    $status = isset($_GET["status"]) ? trim(htmlspecialchars($_GET["status"])) : "";
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Appliances</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Appliances</h1>
        
        <div class="nav-links">
            <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
            <a href="addappliance.php">Add Appliance</a>
            <a href="viewowner.php">Manage Owners</a>
            <a href="viewclaim.php">View Claims</a>
        </div>
        
        <form action="" method="get" style="margin: 20px 0;">
            <label for="search">Search:</label>
            <input type="search" name="search" id="search" value="<?= $search ?>">
            
            <select name="status" id="status">
                <option value="">All</option>
                <option value="Active" <?= ($status == "Active") ? "selected" : "" ?>>Active</option>
                <option value="Expired" <?= ($status == "Expired") ? "selected" : "" ?>>Expired</option>
                <option value="Pending" <?= ($status == "Pending") ? "selected" : "" ?>>Pending</option>
            </select>
            
            <input type="submit" value="Search">
        </form>

        <table>
            <tr>
                <th>No.</th>
                <th>Appliance Name</th>
                <th>Model Number</th>
                <th>Serial Number</th>
                <th>Purchase Date</th>
                <th>Warranty End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            $no = 1;
            $appliances = $applianceObj->viewAppliance($search, $status);
            foreach ($appliances as $appliance) {
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($appliance["appliance_name"]) ?></td>
                <td><?= htmlspecialchars($appliance["model_number"]) ?></td>
                <td><?= htmlspecialchars($appliance["serial_number"]) ?></td>
                <td><?= htmlspecialchars($appliance["purchase_date"]) ?></td>
                <td><?= htmlspecialchars($appliance["warranty_end_date"]) ?></td>
                <td><?= htmlspecialchars($appliance["status"]) ?></td>
                <td>
                    <button class="action-btn view-btn">
                        <a href="viewdetails.php?id=<?= $appliance['id'] ?>">View</a>
                    </button>
                    <button class="action-btn edit-btn">
                        <a href="editappliance.php?id=<?= $appliance['id'] ?>">Edit</a>
                    </button>
                    <button class="action-btn delete-btn">
                        <a href="deleteappliance.php?id=<?= $appliance['id'] ?>">Delete</a>
                    </button>
                </td>
            </tr>
            <?php
            }
            if (empty($appliances)) {
                echo "<tr><td colspan='8' style='text-align:center;'>No appliances found.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>