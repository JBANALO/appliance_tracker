<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "claim.php";

$claimObj = new Claim();
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
    <title>Warranty Claims</title>
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
                <i class="fas fa-file-contract"></i> Warranty Claims
            </h1>
        </div>
        
        <?php if (isset($_GET["success"])): ?>
            <div class="alert-success" style="margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> Claim filed successfully! Our team will review it soon.
            </div>
        <?php endif; ?>

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-filter" style="color: #667eea;"></i>
                Search & Filter Claims
            </div>

            <div style="padding: 20px;">
                <form action="" method="get" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: flex-end;">
                    <div class="form-group" style="margin: 0;">
                        <label for="search" style="margin-bottom: 8px; display: block;"><i class="fas fa-edit"></i> Search</label>
                        <input type="search" name="search" class="form-control" placeholder="Search by appliance name..." value="<?= $search ?>">
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label for="status" style="margin-bottom: 8px; display: block;"><i class="fas fa-filter"></i> Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Pending" <?= ($status == "Pending") ? "selected" : "" ?>>Pending</option>
                            <option value="Approved" <?= ($status == "Approved") ? "selected" : "" ?>>Approved</option>
                            <option value="Rejected" <?= ($status == "Rejected") ? "selected" : "" ?>>Rejected</option>
                            <option value="Completed" <?= ($status == "Completed") ? "selected" : "" ?>>Completed</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin: 0;">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
        
        <div class="action-buttons" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 20px;">
            <a href="fileclaimpage.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> File New Claim
            </a>
            <a href="viewappliance.php" class="btn btn-secondary">
                <i class="fas fa-laptop"></i> View Appliances
            </a>
        </div>

        <table>
            <tr>
                <th>No.</th>
                <th>Appliance</th>
                <th>Owner</th>
                <th>Claim Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            $no = 1;
            $claims = $claimObj->viewClaim($search, $status);
            if ($claims && count($claims) > 0) {
                foreach ($claims as $claim) {
                    $statusClass = "status-" . strtolower($claim["claim_status"]);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($claim["appliance_name"]) ?></td>
                    <td><?= htmlspecialchars($claim["owner_name"]) ?></td>
                    <td><?= htmlspecialchars($claim["claim_date"]) ?></td>
                    <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($claim["claim_status"]) ?></span></td>
                    <td>
                        <a href="viewclaimdetails.php?id=<?= $claim['id'] ?>" class="action-btn view-btn">View</a>
                        <a href="deleteclaim.php?id=<?= $claim['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this claim?');">Delete</a>
                    </td>
                </tr>
                <?php
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>No claims found.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>