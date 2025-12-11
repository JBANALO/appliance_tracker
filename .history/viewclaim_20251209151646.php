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
</head>
<body>
    <div class="container">
        <h1>Warranty Claims</h1>
        
        <?php if (isset($_GET["success"])): ?>
            <div class="success-message">
                ✓ Claim filed successfully! Our team will review it soon.
            </div>
        <?php endif; ?>

        <div class="nav-links">
            <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
            <a href="fileclaim.php">+ File New Claim</a>
            <a href="viewappliance.php">View Appliances</a>
        </div>

        <div class="controls" style="margin: 20px 0;">
            <form action="" method="get">
                <input type="search" name="search" placeholder="Search by appliance name..." value="<?= $search ?>">
                
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Pending" <?= ($status == "Pending") ? "selected" : "" ?>>Pending</option>
                    <option value="Approved" <?= ($status == "Approved") ? "selected" : "" ?>>Approved</option>
                    <option value="Rejected" <?= ($status == "Rejected") ? "selected" : "" ?>>Rejected</option>
                    <option value="Completed" <?= ($status == "Completed") ? "selected" : "" ?>>Completed</option>
                </select>
                
                <button type="submit">Search</button>
            </form>
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