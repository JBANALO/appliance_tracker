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
require_once "owner.php";

$appliance = [
    "appliance_name" => "", "model_number" => "", "serial_number" => "",
    "purchase_date" => "", "warranty_period" => "", "warranty_end_date" => "",
    "status" => "", "owner" => "", "owner_name" => ""
];

$errors = [
    "appliance_name" => "", "model_number" => "", "serial_number" => "",
    "purchase_date" => "", "warranty_period" => "", "warranty_end_date" => "",
    "status" => "", "owner" => ""
];

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    header("Location: viewappliance.php");
    exit;
}

$applianceObj = new Appliance();
$existingAppliance = $applianceObj->fetchAppliance($id);

if (!$existingAppliance) {
    header("Location: viewappliance.php");
    exit;
}

$db = new Database();
$sql_get_owner = "SELECT owner_name FROM owner WHERE id = :owner_id";
$query_get_owner = $db->connect()->prepare($sql_get_owner);
$query_get_owner->bindParam(':owner_id', $existingAppliance['owner_id']);
$query_get_owner->execute();
$owner_data = $query_get_owner->fetch();

// Fetch all owners for dropdown
$ownerObj = new Owner();
$owners = $ownerObj->viewOwner();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appliance["appliance_name"] = trim(htmlspecialchars($_POST["appliance_name"] ?? ""));
    $appliance["model_number"] = trim(htmlspecialchars($_POST["model_number"] ?? ""));
    $appliance["serial_number"] = trim(htmlspecialchars($_POST["serial_number"] ?? ""));
    $appliance["purchase_date"] = trim(htmlspecialchars($_POST["purchase_date"] ?? ""));
    $appliance["warranty_period"] = trim(htmlspecialchars($_POST["warranty_period"] ?? ""));
    $appliance["warranty_end_date"] = trim(htmlspecialchars($_POST["warranty_end_date"] ?? ""));
    $appliance["status"] = trim(htmlspecialchars($_POST["status"] ?? ""));
    $appliance["owner_name"] = trim(htmlspecialchars($_POST["owner_name"] ?? ""));

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
    if (empty($appliance["owner_name"])) {
        $errors["owner"] = "Owner is required";
    }

    if (!array_filter($errors)) {
        $sql_find_owner = "SELECT id FROM owner WHERE owner_name = :owner_name";
        $query_find_owner = $db->connect()->prepare($sql_find_owner);
        $query_find_owner->bindParam(':owner_name', $appliance["owner_name"]);
        $query_find_owner->execute();
        $owner_result = $query_find_owner->fetch();

        if ($owner_result) {
            $owner_id = $owner_result['id'];
        } else {
            $sql_create_owner = "INSERT INTO owner (owner_name) VALUES (:owner_name)";
            $query_create_owner = $db->connect()->prepare($sql_create_owner);
            $query_create_owner->bindParam(':owner_name', $appliance["owner_name"]);
            $query_create_owner->execute();
            $owner_id = $db->connect()->lastInsertId();
        }

        $applianceObj->appliance_name = $appliance["appliance_name"];
        $applianceObj->model_number = $appliance["model_number"];
        $applianceObj->serial_number = $appliance["serial_number"];
        $applianceObj->purchase_date = $appliance["purchase_date"];
        $applianceObj->warranty_period = $appliance["warranty_period"];
        $applianceObj->warranty_end_date = $appliance["warranty_end_date"];
        $applianceObj->status = $appliance["status"];
        $applianceObj->owner_id = $owner_id;

        if ($applianceObj->updateAppliance($id)) {
            header("Location: viewappliance.php");
            exit;
        } else {
            echo "Failed to update appliance.";
        }
    }
} else {
    $appliance["appliance_name"] = $existingAppliance["appliance_name"];
    $appliance["model_number"] = $existingAppliance["model_number"];
    $appliance["serial_number"] = $existingAppliance["serial_number"];
    $appliance["purchase_date"] = $existingAppliance["purchase_date"];
    $appliance["warranty_period"] = $existingAppliance["warranty_period"];
    $appliance["warranty_end_date"] = $existingAppliance["warranty_end_date"];
    $appliance["status"] = $existingAppliance["status"];
    $appliance["owner_name"] = $owner_data ? $owner_data['owner_name'] : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appliance</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="viewappliance.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <h1 style="font-size: 28px; color: #667eea; margin: 20px 0 0 0;">
                <i class="fas fa-edit"></i> Edit Appliance
            </h1>
        </div>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            Fields marked with <span class="required">*</span> are required
        </div>

        <form action="" method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="appliance_name">
                        Appliance Name <span class="required">*</span>
                    </label>
                    <input type="text" name="appliance_name" id="appliance_name" value="<?= htmlspecialchars($appliance["appliance_name"]) ?>" required>
                    <?php if ($errors["appliance_name"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["appliance_name"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="model_number">
                        Model Number <span class="required">*</span>
                    </label>
                    <input type="text" name="model_number" id="model_number" value="<?= htmlspecialchars($appliance["model_number"]) ?>" required>
                    <?php if ($errors["model_number"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["model_number"] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="serial_number">
                        Serial Number <span class="required">*</span>
                    </label>
                    <input type="text" name="serial_number" id="serial_number" value="<?= htmlspecialchars($appliance["serial_number"]) ?>" required>
                    <?php if ($errors["serial_number"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["serial_number"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="purchase_date">
                        Purchase Date <span class="required">*</span>
                    </label>
                    <input type="date" name="purchase_date" id="purchase_date" value="<?= htmlspecialchars($appliance["purchase_date"]) ?>" required>
                    <?php if ($errors["purchase_date"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["purchase_date"] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="warranty_period">
                        Warranty Period (Years) <span class="required">*</span>
                    </label>
                    <input type="number" name="warranty_period" id="warranty_period" value="<?= htmlspecialchars($appliance["warranty_period"]) ?>" min="1" required>
                    <?php if ($errors["warranty_period"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["warranty_period"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warranty_end_date">
                        Warranty End Date <span class="required">*</span>
                    </label>
                    <input type="date" name="warranty_end_date" id="warranty_end_date" value="<?= htmlspecialchars($appliance["warranty_end_date"]) ?>" required>
                    <?php if ($errors["warranty_end_date"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["warranty_end_date"] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">
                        Status <span class="required">*</span>
                    </label>
                    <select name="status" id="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="Active" <?= $appliance["status"] == "Active" ? "selected" : "" ?>>Active</option>
                        <option value="Expired" <?= $appliance["status"] == "Expired" ? "selected" : "" ?>>Expired</option>
                        <option value="Pending" <?= $appliance["status"] == "Pending" ? "selected" : "" ?>>Pending</option>
                    </select>
                    <?php if ($errors["status"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["status"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="owner_search">
                        Owner <span class="required">*</span>
                    </label>
                    <input type="text" name="owner_search" id="owner_search" list="owner-list" value="<?= htmlspecialchars($appliance["owner_name"]) ?>" placeholder="Search owner by name or email..." required autocomplete="off">
                    <input type="hidden" name="owner_name" id="owner_name" value="<?= htmlspecialchars($appliance["owner_name"]) ?>">
                    <datalist id="owner-list">
                        <?php 
                        if ($owners && count($owners) > 0) {
                            foreach ($owners as $owner) {
                                echo "<option value='{$owner["owner_name"]}' data-email='{$owner["email"]}'>{$owner["owner_name"]} - {$owner["email"]}</option>";
                            }
                        }
                        ?>
                    </datalist>
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                        <i class="fas fa-info-circle"></i> Type to search by name or email
                    </small>
                    <?php if ($errors["owner"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["owner"] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="button-group" style="display: grid; grid-template-columns: 1fr; gap: 10px; max-width: 400px; margin: 0 auto;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Appliance
                </button>
                <a href="viewappliance.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // Auto-calculate warranty end date
        function calculateWarrantyEndDate() {
            const purchaseDate = document.getElementById('purchase_date').value;
            const warrantyPeriod = document.getElementById('warranty_period').value;
            
            if (purchaseDate && warrantyPeriod) {
                const date = new Date(purchaseDate);
                date.setFullYear(date.getFullYear() + parseInt(warrantyPeriod));
                
             
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                
                document.getElementById('warranty_end_date').value = `${year}-${month}-${day}`;
            }
        }
        

        document.getElementById('purchase_date').addEventListener('change', calculateWarrantyEndDate);
        document.getElementById('warranty_period').addEventListener('input', calculateWarrantyEndDate);

       
        const ownerSearch = document.getElementById('owner_search');
        const ownerHidden = document.getElementById('owner_name');
        
        ownerSearch.addEventListener('input', function(e) {
            ownerHidden.value = e.target.value;
        });


        document.querySelector('form').addEventListener('submit', function(e) {
            ownerHidden.value = ownerSearch.value;
        });
    </script>
</body>
</html>