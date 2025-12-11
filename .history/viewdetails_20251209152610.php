<?php
require_once "database.php";
require_once "Appliance.php";
require_once "Owner.php";
require_once "Claim.php";

$applianceObj = new Appliance();
$ownerObj = new Owner();
$claimObj = new Claim();

if (isset($_GET["id"])) {
    $appliance_id = trim(htmlspecialchars($_GET["id"]));
    $appliance = $applianceObj->getApplianceById($appliance_id);
    
    if (!$appliance) {
        echo "Appliance not found.";
        exit;
    }
    
    $owner = $ownerObj->getOwnerById($appliance["owner_id"]);
    $claims = $claimObj->getClaimsByAppliance($appliance_id);
} else {
    echo "Invalid request.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appliance Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Appliance Details</h1>
            <a href="viewappliance.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <!-- Appliance Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-laptop"></i>
                Appliance Information
            </h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Appliance Name</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["appliance_name"]) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Model Number</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["model_number"]) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Serial Number</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["serial_number"]) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Purchase Date</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["purchase_date"]) ?></div>
                </div>
            </div>
        </div>

        <!-- Warranty Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-shield-alt"></i>
                Warranty Information
            </h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Warranty Period</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["warranty_period"]) ?> year(s)</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Warranty End Date</div>
                    <div class="info-value"><?= htmlspecialchars($appliance["warranty_end_date"]) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge <?= strtolower($appliance["status"]) ?>">
                            <?= htmlspecialchars($appliance["status"]) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Information Section -->
        <div class="section">
            <h2>
                <i class="fas fa-user"></i>
                Owner Information
            </h2>
            <?php if ($owner): ?>
            <div class="owner-card">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Owner Name</div>
                        <div class="info-value"><?= htmlspecialchars($owner["owner_name"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?= htmlspecialchars($owner["email"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?= htmlspecialchars($owner["phone"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?= htmlspecialchars($owner["address"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">City</div>
                        <div class="info-value"><?= htmlspecialchars($owner["city"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">State/Province</div>
                        <div class="info-value"><?= htmlspecialchars($owner["state"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Zip Code</div>
                        <div class="info-value"><?= htmlspecialchars($owner["zip_code"]) ?></div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-user-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>Owner information not found.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Warranty Claims Section -->
        <div class="section">
            <h2>
                <i class="fas fa-file-contract"></i>
                Warranty Claims
            </h2>
            <?php if ($claims && count($claims) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Claim Date</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($claims as $claim): ?>
                    <tr>
                        <td><?= htmlspecialchars($claim["claim_date"]) ?></td>
                        <td>
                            <span class="claim-status <?= strtolower($claim["claim_status"]) ?>">
                                <?= htmlspecialchars($claim["claim_status"]) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(substr($claim["claim_description"], 0, 50)) ?>...</td>
                        <td>
                            <a href="viewclaimdetails.php?id=<?= $claim['id'] ?>" class="view-link">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No warranty claims filed for this appliance.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="editappliance.php?id=<?= $appliance['id'] ?>" class="btn btn-edit">
                <i class="fas fa-edit"></i>
                Edit Appliance
            </a>
            <a href="deleteappliance.php?id=<?= $appliance['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this appliance?');">
                <i class="fas fa-trash"></i>
                Delete Appliance
            </a>
        </div>
    </div>
</body>
</html>