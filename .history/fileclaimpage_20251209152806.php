working have error
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
</head>
<body>
    <div class="container">
        <h1>File Warranty Claim</h1>
        <a href="viewclaim.php">← Back to Claims</a>

        <form action="" method="post">
            <label>Fields with <span>*</span> are required</label>

            <label for="appliance_id">Select Appliance <span>*</span></label>
            <select name="appliance_id" id="appliance_id">
                <option value="">--Select--</option>
                <?php
                foreach ($appliances as $appliance) {
                    $selected = $claim["appliance_id"] == $appliance["id"] ? "selected" : "";
                    echo "<option value='" . $appliance["id"] . "' $selected>" . 
                         htmlspecialchars($appliance["appliance_name"]) . " (SN: " . 
                         htmlspecialchars($appliance["serial_number"]) . ")</option>";
                }
                ?>
            </select>
            <span class="error"><?= $errors["appliance_id"] ?></span>

            <label for="claim_date">Claim Date <span>*</span></label>
            <input type="date" name="claim_date" id="claim_date" value="<?= $claim["claim_date"] ?>">
            <span class="error"><?= $errors["claim_date"] ?></span>

            <label for="claim_description">Description of Issue <span>*</span></label>
            <textarea name="claim_description" id="claim_description" placeholder="Describe the issue with your appliance..."><?= htmlspecialchars($claim["claim_description"]) ?></textarea>
            <span class="error"><?= $errors["claim_description"] ?></span>

            <button type="submit">File Claim</button>
        </form>
    </div>
</body>
</html>