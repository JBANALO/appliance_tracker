<?php
require_once "database.php";
require_once "Owner.php";

$ownerObj = new Owner();

if (isset($_GET["id"])) {
    $owner_id = trim(htmlspecialchars($_GET["id"]));
    $owner = $ownerObj->getOwnerById($owner_id);
    
    if (!$owner) {
        echo "Owner not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}

$appliances = $ownerObj->getOwnerAppliances($owner_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Owner Details</h1>
            <a href="viewowner.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>

        <div class="owner-info-section">
            <div class="owner-info-grid">
                <div class="info-item">
                    <div class="info-label">Full Name</div>
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

            <div class="action-buttons">
                <a href="editowner.php?id=<?= $owner['id'] ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i>
                    Edit Owner
                </a>
                <a href="deleteowner.php?id=<?= $owner['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this owner?');">
                    <i class="fas fa-trash"></i>
                    Delete Owner
                </a>
            </div>
        </div>

        <div class="appliances-section">
            <h2>
                <i class="fas fa-list"></i>
                Associated Appliances
            </h2>
            
            <?php if ($appliances && count($appliances) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Appliance Name</th>
                        <th>Model Number</th>
                        <th>Serial Number</th>
                        <th>Warranty End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                foreach ($appliances as $appliance) {
                    $statusClass = strtolower($appliance["status"]);
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($appliance["appliance_name"]) ?></td>
                        <td><?= htmlspecialchars($appliance["model_number"]) ?></td>
                        <td><?= htmlspecialchars($appliance["serial_number"]) ?></td>
                        <td><?= htmlspecialchars($appliance["warranty_end_date"]) ?></td>
                        <td><span class="status <?= $statusClass ?>"><?= htmlspecialchars($appliance["status"]) ?></span></td>
                        <td>
                            <div class="action-links">
                                <a href="viewdetails.php?id=<?= $appliance['id'] ?>" class="view-btn">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="editappliance.php?id=<?= $appliance['id'] ?>" class="edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-appliances">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No appliances found for this owner.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>