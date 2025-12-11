<?php
require_once "database.php";
require_once "Appliance.php";
require_once "Claim.php";

$claim = [
    "appliance_id" => "", "claim_date" => "", "claim_description" => ""
];

$errors = [
    "appliance_id" => "", "claim_date" => "", "claim_description" => ""
];

$applianceObj = new Appliance();
$appliances = $applianceObj->viewAppliance("", "Active");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $claim["appliance_id"] = trim(htmlspecialchars($_POST["appliance_id"] ?? ""));
    $claim["claim_date"] = trim(htmlspecialchars($_POST["claim_date"] ?? ""));
    $claim["claim_description"] = trim(htmlspecialchars($_POST["claim_description"] ?? ""));

    if (empty($claim["appliance_id"])) {
        $errors["appliance_id"] = "Please select an appliance";
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
            header("Location: viewclaim.php?success=1");
            exit;
        } else {
            echo "Failed to file claim.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>File Warranty Claim</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="viewclaim.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Claims
            </a>
            <h1 style="font-size: 28px; color: #667eea; margin: 20px 0 0 0;">
                <i class="fas fa-file-contract"></i> File Warranty Claim
            </h1>
        </div>

        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-edit" style="color: #667eea;"></i>
                Claim Information
            </div>
            
            <div style="padding: 20px;">
                <div class="alert-info" style="margin-bottom: 20px;">
                    <strong><i class="fas fa-info-circle"></i> Required Fields:</strong>
                    All fields marked with <span style="color: #dc3545;">*</span> are required to file a warranty claim.
                </div>

                <form action="" method="post">
                    <div class="form-group">
                        <label for="appliance_id">
                            <i class="fas fa-laptop"></i> Select Appliance <span style="color: #dc3545;">*</span>
                        </label>
                        <select name="appliance_id" id="appliance_id" class="form-control" required>
                            <option value="">-- Select Appliance --</option>
                            <?php
                            foreach ($appliances as $appliance) {
                                $selected = $claim["appliance_id"] == $appliance["id"] ? "selected" : "";
                                echo "<option value='" . $appliance["id"] . "' $selected>" . 
                                     htmlspecialchars($appliance["appliance_name"]) . " (SN: " . 
                                     htmlspecialchars($appliance["serial_number"]) . ")</option>";
                            }
                            ?>
                        </select>
                        <?php if($errors["appliance_id"]): ?>
                            <span class="error-text" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation"></i> <?= $errors["appliance_id"] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                        <div class="form-group">
                            <label for="claim_date">
                                <i class="fas fa-calendar"></i> Claim Date <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="date" name="claim_date" id="claim_date" class="form-control" value="<?= $claim["claim_date"] ?>" required>
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
                        <textarea name="claim_description" id="claim_description" class="form-control" rows="8" placeholder="Describe the issue with your appliance. Provide details about any error messages, unusual sounds, or performance issues. (Minimum 10 characters)" required><?= htmlspecialchars($claim["claim_description"]) ?></textarea>
                        <?php if($errors["claim_description"]): ?>
                            <span class="error-text" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation"></i> <?= $errors["claim_description"] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> File Claim
                        </button>
                        <a href="viewclaim.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>