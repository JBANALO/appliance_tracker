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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 style="font-size: 28px; color: #667eea; margin: 20px 0 0 0;">
                <i class="fas fa-laptop"></i> Appliances
            </h1>
        </div>
        
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #667eea;"></i>
                Search & Filter
            </div>
            
            <div style="padding: 20px;">
                <form action="" method="get" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: flex-end;">
                    <div class="form-group" style="margin: 0;">
                        <label for="search" style="margin-bottom: 8px; display: block;"><i class="fas fa-edit"></i> Search</label>
                        <input type="search" name="search" id="search" class="form-control" value="<?= $search ?>" placeholder="Search by name, model, or serial...">
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label for="status" style="margin-bottom: 8px; display: block;"><i class="fas fa-filter"></i> Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Active" <?= ($status == "Active") ? "selected" : "" ?>>Active</option>
                            <option value="Expired" <?= ($status == "Expired") ? "selected" : "" ?>>Expired</option>
                            <option value="Pending" <?= ($status == "Pending") ? "selected" : "" ?>>Pending</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin: 0;">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
        
        <div class="action-buttons" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 20px;">
            <a href="addappliance.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Appliance
            </a>
            <a href="viewowner.php" class="btn btn-secondary">
                <i class="fas fa-users"></i> Manage Owners
            </a>
            <a href="viewclaim.php" class="btn btn-secondary">
                <i class="fas fa-file-alt"></i> View Claims
            </a>
        </div>

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