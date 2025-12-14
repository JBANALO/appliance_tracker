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

// Fetch all owners for dropdown
$ownerObj = new Owner();
$owners = $ownerObj->viewOwner();

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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Appliance</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-laptop"></i> Add Appliance
            </h1>
            <a href="viewappliance.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
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
                    <input type="text" name="appliance_name" id="appliance_name" value="<?= $appliance["appliance_name"] ?>" required>
                    <?php if ($errors["appliance_name"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["appliance_name"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="model_number">
                        Model Number <span class="required">*</span>
                    </label>
                    <input type="text" name="model_number" id="model_number" value="<?= $appliance["model_number"] ?>" required>
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
                    <input type="text" name="serial_number" id="serial_number" value="<?= $appliance["serial_number"] ?>" required>
                    <?php if ($errors["serial_number"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["serial_number"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="purchase_date">
                        Purchase Date <span class="required">*</span>
                    </label>
                    <input type="date" name="purchase_date" id="purchase_date" value="<?= $appliance["purchase_date"] ?>" required>
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
                    <input type="number" name="warranty_period" id="warranty_period" value="<?= $appliance["warranty_period"] ?>" placeholder="e.g., 1, 2, 3" min="1" required>
                    <?php if ($errors["warranty_period"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["warranty_period"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="warranty_end_date">
                        Warranty End Date <span class="required">*</span>
                    </label>
                    <input type="date" name="warranty_end_date" id="warranty_end_date" value="<?= $appliance["warranty_end_date"] ?>" required>
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
                        <option value="">--Select--</option>
                        <option value="Active" <?= $appliance["status"]=="Active" ? "selected" : "" ?>>Active</option>
                        <option value="Expired" <?= $appliance["status"]=="Expired" ? "selected" : "" ?>>Expired</option>
                        <option value="Pending" <?= $appliance["status"]=="Pending" ? "selected" : "" ?>>Pending</option>
                    </select>
                    <?php if ($errors["status"]): ?>
                        <p class="error"><i class="fas fa-exclamation-circle"></i> <?= $errors["status"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="owner">
                        Owner <span class="required">*</span>
                    </label>
                    <input type="text" name="owner" id="owner" list="owner-list" value="<?= $appliance["owner"] ?>" placeholder="Search owner by name or email..." required autocomplete="off">
                    <datalist id="owner-list">
                        <?php 
                        if ($owners && count($owners) > 0) {
                            foreach ($owners as $owner) {
                                echo "<option value='{$owner["id"]}' data-name='{$owner["owner_name"]}' data-email='{$owner["email"]}'>{$owner["owner_name"]} - {$owner["email"]}</option>";
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

            <div class="button-group">
                <button type="submit">
                    <i class="fas fa-plus-circle"></i>
                    Add Appliance
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
                
                // Format date as YYYY-MM-DD for input type="date"
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                
                document.getElementById('warranty_end_date').value = `${year}-${month}-${day}`;
            }
        }
        
        // Add event listeners
        document.getElementById('purchase_date').addEventListener('change', calculateWarrantyEndDate);
        document.getElementById('warranty_period').addEventListener('input', calculateWarrantyEndDate);

        // Enhanced owner search functionality
        const ownerInput = document.getElementById('owner');
        const ownerDatalist = document.getElementById('owner-list');
        
        ownerInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const options = ownerDatalist.querySelectorAll('option');
            
            // If user selects from datalist, set the value to owner ID
            options.forEach(option => {
                const name = option.getAttribute('data-name').toLowerCase();
                const email = option.getAttribute('data-email').toLowerCase();
                const displayText = option.text.toLowerCase();
                
                if (e.target.value === option.text.split(' - ')[0] || 
                    e.target.value === option.text) {
                    e.target.value = option.value;
                }
            });
        });

        // Show owner name when ID is pre-filled
        window.addEventListener('DOMContentLoaded', function() {
            const currentValue = ownerInput.value;
            if (currentValue) {
                const options = ownerDatalist.querySelectorAll('option');
                options.forEach(option => {
                    if (option.value === currentValue) {
                        ownerInput.value = option.getAttribute('data-name');
                    }
                });
            }
        });

        // Before form submit, ensure we have owner ID
        document.querySelector('form').addEventListener('submit', function(e) {
            const ownerValue = ownerInput.value;
            const options = ownerDatalist.querySelectorAll('option');
            let found = false;
            
            options.forEach(option => {
                if (option.getAttribute('data-name') === ownerValue || 
                    option.getAttribute('data-email') === ownerValue ||
                    option.text.includes(ownerValue)) {
                    ownerInput.value = option.value;
                    found = true;
                }
            });
            
            if (!found && isNaN(ownerValue)) {
                e.preventDefault();
                alert('Please select a valid owner from the list');
            }
        });
    </script>
</body>
</html>