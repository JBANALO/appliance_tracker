<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

initSecureSession();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "database.php";
require_once "claim.php";
require_once "appliance.php";
require_once "Notification.php";
require_once "EmailNotification.php";

$applianceObj = new Appliance();

$claim = [
    "appliance_id" => "", "claim_date" => "", "claim_description" => "",
    "claim_status" => "", "resolution_notes" => ""
];

$errors = [
    "appliance_id" => "", "claim_date" => "", "claim_description" => "",
    "claim_status" => ""
];

if (isset($_GET["appliance_id"])) {
    $claim["appliance_id"] = $_GET["appliance_id"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $claim["appliance_id"] = trim(htmlspecialchars($_POST["appliance_id"] ?? ""));
    $claim["claim_date"] = trim(htmlspecialchars($_POST["claim_date"] ?? ""));
    $claim["claim_description"] = trim(htmlspecialchars($_POST["claim_description"] ?? ""));
    $claim["claim_status"] = trim(htmlspecialchars($_POST["claim_status"] ?? ""));
    $claim["resolution_notes"] = trim(htmlspecialchars($_POST["resolution_notes"] ?? ""));

    if (empty($claim["appliance_id"])) {
        $errors["appliance_id"] = "Please select an appliance";
    }
    if (empty($claim["claim_date"])) {
        $errors["claim_date"] = "Claim date is required";
    }
    if (empty($claim["claim_description"])) {
        $errors["claim_description"] = "Claim description is required";
    }
    if (empty($claim["claim_status"])) {
        $errors["claim_status"] = "Please select a claim status";
    }

    if (!array_filter($errors)) {
        $claimObj = new Claim();
        $claimObj->appliance_id = $claim["appliance_id"];
        $claimObj->claim_date = $claim["claim_date"];
        $claimObj->claim_description = $claim["claim_description"];
        $claimObj->claim_status = $claim["claim_status"];
        $claimObj->resolution_notes = $claim["resolution_notes"];

        if ($claimObj->addClaim()) {
            
            $db = new Database();
            $conn = $db->connect();
            
            $notificationObj = new Notification();
            $emailObj = new EmailNotification();
            
            
            $claim_id = $conn->lastInsertId();
            
            $sql = "SELECT a.appliance_name, o.owner_name, o.email 
                    FROM appliance a 
                    LEFT JOIN owner o ON a.owner_id = o.id 
                    WHERE a.id = :appliance_id";
            $query = $conn->prepare($sql);
            $query->bindParam(':appliance_id', $claimObj->appliance_id);
            $query->execute();
            $info = $query->fetch();
            
         
            $notificationObj->addNotification(
                'claim',
                'New Warranty Claim Filed',
                "A new claim has been filed for {$info['appliance_name']} by {$info['owner_name']}",
                "viewclaim.php"
            );
            
            
            
            header("Location: viewclaim.php?success=claim_added");
            exit;
        } else {
            echo "Failed to add claim.";
        }
    }
}

$appliances = $applianceObj->viewAppliance("", "");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>File Warranty Claim</title>
            margin-top: 5px;
        }
        
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>File Warranty Claim</h1>
        <a href="viewclaim.php">← Back to Claims</a>

        <form action="" method="post">
            <p>Fields with <span class="required">*</span> are required</p>

            <label for="appliance_id">Appliance <span class="required">*</span></label>
            <select name="appliance_id" id="appliance_id" required>
                <option value="">-- Select Appliance --</option>
                <?php if ($appliances): ?>
                    <?php foreach($appliances as $appliance): ?>
                        <option value="<?= $appliance['id'] ?>" <?= $claim['appliance_id'] == $appliance['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($appliance['appliance_name']) ?> - S/N: <?= htmlspecialchars($appliance['serial_number']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if($errors['appliance_id']): ?>
                <div class="error-message"><?= $errors['appliance_id'] ?></div>
            <?php endif; ?>

            <label for="claim_date">Claim Date <span class="required">*</span></label>
            <input type="date" name="claim_date" id="claim_date" value="<?= $claim['claim_date'] ?>" required>
            <?php if($errors['claim_date']): ?>
                <div class="error-message"><?= $errors['claim_date'] ?></div>
            <?php endif; ?>

            <label for="claim_description">Claim Description <span class="required">*</span></label>
            <textarea name="claim_description" id="claim_description" required><?= $claim['claim_description'] ?></textarea>
            <?php if($errors['claim_description']): ?>
                <div class="error-message"><?= $errors['claim_description'] ?></div>
            <?php endif; ?>

            <label for="claim_status">Claim Status <span class="required">*</span></label>
            <select name="claim_status" id="claim_status" required>
                <option value="">-- Select Status --</option>
                <option value="Pending" <?= $claim['claim_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Approved" <?= $claim['claim_status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= $claim['claim_status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="Completed" <?= $claim['claim_status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
            <?php if($errors['claim_status']): ?>
                <div class="error-message"><?= $errors['claim_status'] ?></div>
            <?php endif; ?>

            <label for="resolution_notes">Resolution Notes</label>
            <textarea name="resolution_notes" id="resolution_notes"><?= $claim['resolution_notes'] ?></textarea>

            <button type="submit">Submit Claim</button>
        </form>
    </div>
</body>
</html>