<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

initSecureSession();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "database.php";
require_once "appliance.php";

$applianceObj = new Appliance();

if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["id"])) {
        $aid = trim(htmlspecialchars($_GET["id"]));
        $appliance = $applianceObj->fetchAppliance($aid);
        
        if(!$appliance) {
            echo "<a href='viewappliance.php'>View Appliance</a>";
            exit("Appliance not found");
        }
    } else {
        echo "<a href='viewappliance.php'>View Appliance</a>";
        exit("Appliance not found");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aid = trim(htmlspecialchars($_POST["id"] ?? ""));
    
    if ($applianceObj->deleteAppliance($aid)) {
        header("Location: viewappliance.php");
        exit;
    } else {
        echo "Failed to delete appliance.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Appliance</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Delete Appliance</h1>
            <a href="viewappliance.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <div class="warning-box">
            <i class="fas fa-exclamation-triangle"></i>
            <p><strong>Warning:</strong> This action cannot be undone. All data related to this appliance will be permanently deleted from the system.</p>
        </div>

        <div class="info-section">
            <h3>
                <i class="fas fa-laptop"></i>
                Appliance Details
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Appliance Name</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["appliance_name"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Model Number</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["model_number"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Serial Number</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["serial_number"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Purchase Date</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["purchase_date"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Warranty Period</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["warranty_period"] ?? '') ?> Years</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Warranty End Date</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["warranty_end_date"] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value"><?= htmlspecialchars($appliance["status"] ?? '') ?></span>
                </div>
            </div>
        </div>

        <form action="" method="post" onsubmit="return confirm('Are you absolutely sure you want to delete this appliance? This action cannot be undone.');">
            <input type="hidden" name="id" value="<?= htmlspecialchars($appliance["id"] ?? '') ?>">
            
            <div class="button-group" style="display: flex; flex-direction: column; gap: 10px; align-items: center;">
                <button type="submit" class="btn-danger" style="width: 100%; max-width: 300px;">
                    <i class="fas fa-trash-alt"></i>
                    Delete Appliance
                </button>
                <a href="viewappliance.php" class="btn btn-danger" style="width: 100%; max-width: 300px; text-align: center;">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>