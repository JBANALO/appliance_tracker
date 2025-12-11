<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once "Claim.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: viewclaim.php");
    exit;
}

$claimObj = new Claim();
$claim = $claimObj->getClaimById($id);

if (!$claim) {
    header("Location: viewclaim.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_status = $_POST['status'] ?? '';
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    if ($claimObj->updateClaimStatus($id, $new_status, $admin_notes)) {
        header("Location: viewclaim.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Claim Details</h1>
            <a href="viewclaim.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Claims
            </a>
        </div>

        <!-- Appliance Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-laptop"></i>
                Appliance Information
            </h2>
            <div class="appliance-section">
                <div class="info-row">
                    <div class="info-label">Appliance Name:</div>
                    <div class="info-value"><?= htmlspecialchars($claim['appliance_name']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Model Number:</div>
                    <div class="info-value"><?= htmlspecialchars($claim['model_number']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Serial Number:</div>
                    <div class="info-value"><?= htmlspecialchars($claim['serial_number']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Warranty End Date:</div>
                    <div class="info-value"><?= htmlspecialchars($claim['warranty_end_date']) ?></div>
                </div>
            </div>
        </div>

        <!-- Owner Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-user"></i>
                Owner Information
            </h2>
            <div class="info-row">
                <div class="info-label">Owner Name:</div>
                <div class="info-value"><?= htmlspecialchars($claim['owner_name']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?= htmlspecialchars($claim['email']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value"><?= htmlspecialchars($claim['phone']) ?></div>
            </div>
        </div>

        <!-- Claim Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-file-contract"></i>
                Claim Information
            </h2>
            <div class="info-row">
                <div class="info-label">Claim Date:</div>
                <div class="info-value"><?= htmlspecialchars($claim['claim_date']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Current Status:</div>
                <div class="info-value">
                    <span class="status-badge status-<?= strtolower($claim['status']) ?>">
                        <?= htmlspecialchars($claim['status']) ?>
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Issue Description:</div>
                <div class="info-value">
                    <div class="issue-box">
                        <?= nl2br(htmlspecialchars($claim['issue_description'])) ?>
                    </div>
                </div>
            </div>
            <?php if (!empty($claim['admin_notes'])): ?>
            <div class="info-row">
                <div class="info-label">Admin Notes:</div>
                <div class="info-value">
                    <div class="issue-box">
                        <?= nl2br(htmlspecialchars($claim['admin_notes'])) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Update Claim Status Section -->
        <div class="section">
            <h2>
                <i class="fas fa-edit"></i>
                Update Claim Status
            </h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-check-circle"></i>
                        Status
                    </label>
                    <select name="status" id="status" required>
                        <option value="Pending" <?= $claim['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Approved" <?= $claim['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="admin_notes">
                        <i class="fas fa-sticky-note"></i>
                        Admin Notes
                    </label>
                    <textarea name="admin_notes" id="admin_notes" placeholder="Add any notes about this claim..."><?= htmlspecialchars($claim['admin_notes']) ?></textarea>
                </div>

                <button type="submit">
                    <i class="fas fa-save"></i>
                    Update Claim
                </button>
            </form>
        </div>
    </div>
</body>
</html>