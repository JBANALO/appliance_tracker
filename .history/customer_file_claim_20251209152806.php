<?php
require_once "database.php";
require_once "Appliance.php";
require_once "Claim.php";
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
            $emailObj = new EmailNotification();
            
            
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
            
            try {
                $emailObj->sendClaimConfirmationEmail(
                    $owner_email,
                    $owner_name,
                    $appliance_name,
                    $claim_id,
                    date('F d, Y', strtotime($claim["claim_date"]))
                );
            } catch (Exception $e) {
               
            }
            
            
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
</head>
<body>
    <div class="container">
        <h1>File Warranty Claim</h1>
        <a href="customer_warranty_tracker.php" class="back-link">← Back to Warranty Tracker</a>

        <?php if ($error_message): ?>
            <div class="error-box">
                <strong>Error:</strong> <?= htmlspecialchars($error_message) ?>
                <br><br>
                <a href="customer_warranty_tracker.php">Return to Warranty Tracker</a>
            </div>
        <?php elseif ($appliance): ?>
            <div class="appliance-info">
                <h3>📱 Warranty Information</h3>
                <table>
                    <tr>
                        <td>Appliance:</td>
                        <td><strong><?= htmlspecialchars($appliance["appliance_name"]) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Model Number:</td>
                        <td><?= htmlspecialchars($appliance["model_number"]) ?></td>
                    </tr>
                    <tr>
                        <td>Serial Number:</td>
                        <td><?= htmlspecialchars($appliance["serial_number"]) ?></td>
                    </tr>
                    <tr>
                        <td>Purchase Date:</td>
                        <td><?= htmlspecialchars($appliance["purchase_date"]) ?></td>
                    </tr>
                    <tr>
                        <td>Warranty End Date:</td>
                        <td><?= htmlspecialchars($appliance["warranty_end_date"]) ?></td>
                    </tr>
                    <?php if ($appliance["owner_name"]): ?>
                    <tr>
                        <td>Owner:</td>
                        <td><?= htmlspecialchars($appliance["owner_name"]) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="form-section">
                <h2> Claim Details</h2>
                <p>Please provide details about your warranty claim. Our team will review and respond within 3-5 business days.</p>

                <form action="" method="post">
                    <input type="hidden" name="appliance_id" value="<?= htmlspecialchars($claim["appliance_id"]) ?>">

                    <label for="claim_date">Date of Issue <span style="color: red;">*</span></label>
                    <input type="date" name="claim_date" id="claim_date" value="<?= $claim["claim_date"] ?>" max="<?= date('Y-m-d') ?>" required>
                    <?php if($errors["claim_date"]): ?>
                        <span class="error"><?= $errors["claim_date"] ?></span>
                    <?php endif; ?>

                    <label for="claim_description">Description of Issue <span style="color: red;">*</span></label>
                    <textarea name="claim_description" id="claim_description" placeholder="Please describe the problem with your appliance in detail. Include any error messages, unusual sounds, or performance issues you've noticed. (Minimum 10 characters)" required><?= htmlspecialchars($claim["claim_description"]) ?></textarea>
                    <?php if($errors["claim_description"]): ?>
                        <span class="error"><?= $errors["claim_description"] ?></span>
                    <?php endif; ?>

                    <button type="submit"> Submit Warranty Claim</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>