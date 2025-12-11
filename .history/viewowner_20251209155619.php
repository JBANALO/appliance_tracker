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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 style="font-size: 28px; color: #667eea; margin: 20px 0 0 0;">
                <i class="fas fa-users"></i> Owners
            </h1>
        </div>
        
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-search" style="color: #667eea;"></i>
                Search Owners
            </div>
            
            <div style="padding: 20px;">
                <form action="" method="get" style="display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: flex-end;">
                    <div class="form-group" style="margin: 0;">
                        <label for="search" style="margin-bottom: 8px; display: block;"><i class="fas fa-edit"></i> Search</label>
                        <input type="search" name="search" id="search" class="form-control" value="<?= $search ?>" placeholder="Search by name, email, or phone...">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin: 0;">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
        
        <div class="action-buttons" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 20px;">
            <a href="addowner.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Owner
            </a>
            <a href="viewappliance.php" class="btn btn-secondary">
                <i class="fas fa-laptop"></i> View Appliances
            </a>
        </div>

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