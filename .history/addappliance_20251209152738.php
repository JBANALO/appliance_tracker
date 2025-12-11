<?php
require_once "database.php";
require_once "Appliance.php";

$appliance = [
    "appliance_name" => "", "model_number" => "", "serial_number" => "",
    "purchase_date" => "", "warranty_period" => "", "warranty_end_date" => "",
    "status" => "", "owner" => ""
];

$errors = [
    "appliance_name" => "", "model_number" => "", "serial_number" => "",
    "purchase_date" => "", "warranty_period" => "", "warranty_end_date" => "",
    "status" => "", "owner" => ""
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appliance["appliance_name"] = trim(htmlspecialchars($_POST["appliance_name"] ?? ""));
    $appliance["model_number"] = trim(htmlspecialchars($_POST["model_number"] ?? ""));
    $appliance["serial_number"] = trim(htmlspecialchars($_POST["serial_number"] ?? ""));
    $appliance["purchase_date"] = trim(htmlspecialchars($_POST["purchase_date"] ?? ""));
    $appliance["warranty_period"] = trim(htmlspecialchars($_POST["warranty_period"] ?? ""));
    $appliance["warranty_end_date"] = trim(htmlspecialchars($_POST["warranty_end_date"] ?? ""));
    $appliance["status"] = trim(htmlspecialchars($_POST["status"] ?? ""));
    $appliance["owner"] = trim(htmlspecialchars($_POST["owner"] ?? ""));

    if (empty($appliance["appliance_name"])) {
        $errors["appliance_name"] = "Appliance name is required";
    }
    if (empty($appliance["model_number"])) {
        $errors["model_number"] = "Model number is required";
    }
    if (empty($appliance["serial_number"])) {
        $errors["serial_number"] = "Serial number is required";
    }
    if (empty($appliance["purchase_date"])) {
        $errors["purchase_date"] = "Purchase date is required";
    }
    if (empty($appliance["warranty_period"])) {
        $errors["warranty_period"] = "Warranty period is required";
    } elseif (!is_numeric($appliance["warranty_period"]) || $appliance["warranty_period"] <= 0) {
        $errors["warranty_period"] = "Warranty period must be a number greater than zero";
    }
    if (empty($appliance["warranty_end_date"])) {
        $errors["warranty_end_date"] = "Warranty end date is required";
    }
    if (empty($appliance["status"])) {
        $errors["status"] = "Please select a status";
    }
    if (empty($appliance["owner"])) {
        $errors["owner"] = "Owner is required";
    }

    if (!array_filter($errors)) {
        $applianceObj = new Appliance();
        $applianceObj->appliance_name = $appliance["appliance_name"];
        $applianceObj->model_number = $appliance["model_number"];
        $applianceObj->serial_number = $appliance["serial_number"];
        $applianceObj->purchase_date = $appliance["purchase_date"];
        $applianceObj->warranty_period = $appliance["warranty_period"];
        $applianceObj->warranty_end_date = $appliance["warranty_end_date"];
        $applianceObj->status = $appliance["status"];
        $applianceObj->owner_id = $appliance["owner"]; // Fixed: use owner_id

        if ($applianceObj->addAppliance()) {
            header("Location: viewappliance.php");
            exit;
        } else {
            echo "Failed to add appliance.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Appliance</title>
</head>
<body>
    <h1>Add Appliance</h1>
    <a href="viewappliance.php">Back to List</a>

    <form action="" method="post">
        <label>Fields with <span>*</span> are required</label>

        <label for="appliance_name">Appliance Name <span>*</span></label>
        <input type="text" name="appliance_name" id="appliance_name" value="<?= $appliance["appliance_name"] ?>">
        <p class="error"><?= $errors["appliance_name"] ?></p>

        <label for="model_number">Model Number <span>*</span></label>
        <input type="text" name="model_number" id="model_number" value="<?= $appliance["model_number"] ?>">
        <p class="error"><?= $errors["model_number"] ?></p>

        <label for="serial_number">Serial Number <span>*</span></label>
        <input type="text" name="serial_number" id="serial_number" value="<?= $appliance["serial_number"] ?>">
        <p class="error"><?= $errors["serial_number"] ?></p>

        <label for="purchase_date">Purchase Date <span>*</span></label>
       <input type="text" name="purchase_date" placeholder="mm/dd/yyyy">

        <p class="error"><?= $errors["purchase_date"] ?></p>

        <label for="warranty_period">Warranty Period (Years) <span>*</span></label>
        <input type="text" name="warranty_period" placeholder="Enter warranty period (e.g. 1 Year, 2 Years)">

        <p class="error"><?= $errors["warranty_period"] ?></p>

        <label for="warranty_end_date">Warranty End Date <span>*</span></label>
        <input type="text" name="warranty_end_date" placeholder="mm/dd/yyyy">
        <p class="error"><?= $errors["warranty_end_date"] ?></p>

        <label for="status">Status <span>*</span></label>
        <select name="status" id="status">
            <option value="">--Select--</option>
            <option value="Active" <?= $appliance["status"]=="Active" ? "selected" : "" ?>>Active</option>
            <option value="Expired" <?= $appliance["status"]=="Expired" ? "selected" : "" ?>>Expired</option>
            <option value="Pending" <?= $appliance["status"]=="Pending" ? "selected" : "" ?>>Pending</option>
        </select>
        <p class="error"><?= $errors["status"] ?></p>

        <label for="owner">Owner <span>*</span></label>
        <input type="text" name="owner" id="owner" value="<?= $appliance["owner"] ?>">
        <p class="error"><?= $errors["owner"] ?></p>

        <button type="submit">Add Appliance</button>
    </form>
</body>
</html>