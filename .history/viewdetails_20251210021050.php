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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="viewappliance.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <h1 style="font-size: 28px; color: #667eea; margin: 20px 0 0 0;">
                <i class="fas fa-laptop"></i> Appliance Details
            </h1>
        </div>

       
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <i class="fas fa-laptop"></i> Appliance Information
            </div>
            <div style="padding: 20px;">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-tag"></i> Appliance Name</div>
                        <div class="info-value"><?= htmlspecialchars($appliance["appliance_name"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-barcode"></i> Model Number</div>
                        <div class="info-value"><?= htmlspecialchars($appliance["model_number"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-hashtag"></i> Serial Number</div>
                        <div class="info-value"><?= htmlspecialchars($appliance["serial_number"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar-alt"></i> Purchase Date</div>
                        <div class="info-value"><?= htmlspecialchars($appliance["purchase_date"]) ?></div>
                    </div>
                </div>
            </div>
        </div>

       
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <i class="fas fa-shield-alt"></i> Warranty Information
            </div>
            <div style="padding: 20px;">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-clock"></i> Warranty Period</div>
                        <div class="info-value"><?= htmlspecialchars($appliance["warranty_period"]) ?> year(s)</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar-check"></i> Warranty End Date</div>
                        <div class="info-value"><?= htmlspecialchars($appliance["warranty_end_date"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-info-circle"></i> Status</div>
                        <div class="info-value">
                            <span class="status-<?= strtolower($appliance["status"]) ?>">
                                <?= htmlspecialchars($appliance["status"]) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <i class="fas fa-user"></i> Owner Information
            </div>
            <div style="padding: 20px;">
                <?php if ($owner): ?>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user-circle"></i> Owner Name</div>
                        <div class="info-value"><?= htmlspecialchars($owner["owner_name"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email Address</div>
                        <div class="info-value"><a href="mailto:<?= htmlspecialchars($owner["email"]) ?>" style="color: #667eea;"><?= htmlspecialchars($owner["email"]) ?></a></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> Phone Number</div>
                        <div class="info-value"><?= htmlspecialchars($owner["phone"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> Address</div>
                        <div class="info-value"><?= htmlspecialchars($owner["address"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-city"></i> City</div>
                        <div class="info-value"><?= htmlspecialchars($owner["city"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-flag"></i> Province</div>
                        <div class="info-value"><?= htmlspecialchars($owner["state"]) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-mail-bulk"></i> Zip Code</div>
                        <div class="info-value"><?= htmlspecialchars($owner["zip_code"]) ?></div>
                    </div>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 40px 20px; color: #666;">
                    <i class="fas fa-user-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Owner information not found.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <i class="fas fa-file-contract"></i> Warranty Claims
            </div>
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
                            <span class="status-<?= strtolower($claim["claim_status"]) ?>">
                                <?= htmlspecialchars($claim["claim_status"]) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(substr($claim["claim_description"], 0, 50)) ?>...</td>
                        <td>
                            <a href="viewclaimdetails.php?id=<?= $claim['id'] ?>" class="action-btn view-btn">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; color: #666;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No warranty claims filed for this appliance.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 20px;">
            <a href="editappliance.php?id=<?= $appliance['id'] ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Appliance
            </a>
            <a href="print_warranty.php?id=<?= $appliance['id'] ?>" class="btn btn-secondary" target="_blank">
                <i class="fas fa-print"></i> Print Warranty
            </a>
            <a href="deleteappliance.php?id=<?= $appliance['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this appliance?');">
                <i class="fas fa-trash"></i> Delete
            </a>
        </div>
    </div>
</body>
</html>