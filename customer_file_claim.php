<?php
require_once "database.php";
require_once "appliance.php";
require_once "claim.php";
require_once "Notification.php";
require_once "EmailNotification.php";

$claim = [
    "appliance_id" => "", 
    "claim_date" => "", 
    "claim_description" => ""
];

$errors = [
    "appliance_id" => "", 
    "claim_date" => "", 
    "claim_description" => ""
];

$appliance = null;
$error_message = "";

if (isset($_GET['appliance_id']) && !empty($_GET['appliance_id'])) {
    $appliance_id = trim($_GET['appliance_id']);
    $serial = isset($_GET['serial']) ? trim($_GET['serial']) : '';
    
    $db = new Database();
    try {
        $sql = "SELECT a.*, o.owner_name, o.email, o.phone,
                CASE 
                    WHEN a.warranty_end_date < CURDATE() THEN 'Expired'
                    ELSE 'Active'
                END as calculated_status
                FROM appliance a 
                LEFT JOIN owner o ON a.owner_id = o.id 
                WHERE a.id = :id";
        $query = $db->connect()->prepare($sql);
        $query->bindParam(":id", $appliance_id);
        
        if ($query->execute()) {
            $appliance = $query->fetch(PDO::FETCH_ASSOC);
            if (!$appliance) {
                $error_message = "Appliance not found.";
            } elseif ($appliance['calculated_status'] != 'Active') {
                $error_message = "Warranty is not active or has expired. Cannot file claim.";
                $appliance = null;
            } else {
                $claim["appliance_id"] = $appliance_id;
            }
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
} else {
    $error_message = "No appliance selected. Please search for your warranty first.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $appliance) {
    $claim["appliance_id"] = trim(htmlspecialchars($_POST["appliance_id"] ?? ""));
    $claim["claim_date"] = trim(htmlspecialchars($_POST["claim_date"] ?? ""));
    $claim["claim_description"] = trim(htmlspecialchars($_POST["claim_description"] ?? ""));

    if (empty($claim["appliance_id"])) {
        $errors["appliance_id"] = "Appliance information is missing";
    }
    if (empty($claim["claim_date"])) {
        $errors["claim_date"] = "Claim date is required";
    }
    if (empty($claim["claim_description"])) {
        $errors["claim_description"] = "Claim description is required";
    } elseif (strlen($claim["claim_description"]) < 10) {
        $errors["claim_description"] = "Description must be at least 10 characters";
    }

    if (!array_filter($errors)) {
        $claimObj = new Claim();
        $claimObj->appliance_id = $claim["appliance_id"];
        $claimObj->claim_date = $claim["claim_date"];
        $claimObj->claim_description = $claim["claim_description"];
        $claimObj->claim_status = "Pending";
        $claimObj->resolution_notes = "";

        if ($claimObj->addClaim()) {
            
            $db = new Database();
            $conn = $db->connect();
            
            $notificationObj = new Notification();
            
            $claim_id = $conn->lastInsertId();
            
            $appliance_name = $appliance['appliance_name'];
            $owner_name = $appliance['owner_name'];
            $owner_email = $appliance['email'];
           
            $notificationObj->addNotification(
                'claim',
                'New Warranty Claim Filed',
                "A new claim has been filed for {$appliance_name} by {$owner_name}",
                "viewclaim.php"
            );
            
            // Redirect immediately
            header("Location: customer_warranty_tracker.php?claim_success=1");
            exit;
        } else {
            $error_message = "Failed to file claim. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Warranty Claim</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="customer_warranty_tracker.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Warranty Tracker
            </a>
            <h1 style="font-size: 28px; color: #667eea; margin: 20px 0 0 0;">
                <i class="fas fa-file-contract"></i> File Warranty Claim
            </h1>
        </div>

        <?php if ($error_message): ?>
            <div class="alert-danger" style="margin-bottom: 20px;">
                <strong><i class="fas fa-exclamation-circle"></i> Error:</strong> <?= htmlspecialchars($error_message) ?>
                <br><br>
                <a href="customer_warranty_tracker.php" class="btn btn-secondary" style="margin-top: 10px; display: inline-block;">
                    <i class="fas fa-arrow-left"></i> Return to Warranty Tracker
                </a>
            </div>
        <?php elseif ($appliance): ?>
           
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header" style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-laptop" style="color: #667eea;"></i>
                    Warranty Information
                </div>
                
                <div style="padding: 20px;">
                    <div class="info-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-box"></i> Appliance</span>
                            <span class="info-value"><?= htmlspecialchars($appliance["appliance_name"]) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-barcode"></i> Model</span>
                            <span class="info-value"><?= htmlspecialchars($appliance["model_number"]) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-key"></i> Serial</span>
                            <span class="info-value"><?= htmlspecialchars($appliance["serial_number"]) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-calendar"></i> Purchase Date</span>
                            <span class="info-value"><?= htmlspecialchars($appliance["purchase_date"]) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-calendar-alt"></i> Warranty Ends</span>
                            <span class="info-value"><?= htmlspecialchars($appliance["warranty_end_date"]) ?></span>
                        </div>
                        <?php if ($appliance["owner_name"]): ?>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-user"></i> Owner</span>
                            <span class="info-value"><?= htmlspecialchars($appliance["owner_name"]) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        
            <div class="card">
                <div class="card-header" style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-edit" style="color: #667eea;"></i>
                    Claim Details
                </div>
                
                <div style="padding: 20px;">
                    <div class="alert-info" style="margin-bottom: 20px;">
                        <strong><i class="fas fa-info-circle"></i> Please note:</strong>
                        Our team will review your claim and respond within 3-5 business days.
                    </div>

                    <form action="" method="post">
                        <input type="hidden" name="appliance_id" value="<?= htmlspecialchars($claim["appliance_id"]) ?>">

                        <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                            <div class="form-group">
                                <label for="claim_date">
                                    <i class="fas fa-calendar"></i> Date of Issue <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="date" name="claim_date" id="claim_date" class="form-control" value="<?= $claim["claim_date"] ?>" max="<?= date('Y-m-d') ?>" required>
                                <?php if($errors["claim_date"]): ?>
                                    <span class="error-text" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">
                                        <i class="fas fa-exclamation"></i> <?= $errors["claim_date"] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="claim_description">
                                <i class="fas fa-align-left"></i> Description of Issue <span style="color: #dc3545;">*</span>
                            </label>
                            <textarea name="claim_description" id="claim_description" class="form-control" rows="8" placeholder="Please describe the problem with your appliance in detail. Include any error messages, unusual sounds, or performance issues you've noticed. (Minimum 10 characters)" required><?= htmlspecialchars($claim["claim_description"]) ?></textarea>
                            <?php if($errors["claim_description"]): ?>
                                <span class="error-text" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">
                                    <i class="fas fa-exclamation"></i> <?= $errors["claim_description"] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 25px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Warranty Claim
                            </button>
                            <a href="customer_warranty_tracker.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>